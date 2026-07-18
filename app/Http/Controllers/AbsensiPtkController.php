<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPtk;
use App\Models\Guru;
use App\Models\JamKerjaLembaga;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Services\PerPageTrait;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsensiPtkController extends Controller
{
    use PerPageTrait;

    /**
     * Riwayat absensi guru yang login.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $lembaga = Lembaga::findOrFail($lembagaId);
        $guru = Guru::where('lembaga_id', $lembagaId)
            ->where('id', $user->guru_id)
            ->firstOrFail();

        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));

        $absensis = AbsensiPtk::where('guru_id', $guru->id)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
            ->orderByDesc('tanggal')
            ->get();

        // Today's absensi
        $today = Carbon::today();
        $absensiHariIni = AbsensiPtk::where('guru_id', $guru->id)
            ->where('tanggal', $today->toDateString())
            ->first();

        $jamKerjaHariIni = JamKerjaLembaga::where('lembaga_id', $lembagaId)
            ->where('hari', $today->locale('id')->dayName)
            ->where('is_active', true)
            ->first();

        $canCheckIn = false;
        $canCheckOut = false;

        if ($jamKerjaHariIni) {
            if (!$absensiHariIni) {
                $canCheckIn = true;
            } elseif (!$absensiHariIni->check_out) {
                $canCheckOut = true;
            }
        }

        return view('absensi-ptk.index', compact(
            'absensis',
            'absensiHariIni',
            'jamKerjaHariIni',
            'canCheckIn',
            'canCheckOut',
            'guru',
            'lembaga',
            'bulan'
        ));
    }

    /**
     * Laporan absensi (Admin Lembaga / Kepala Lembaga).
     */
    public function laporan(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));
        $guruId = $request->get('guru_id');

        $query = AbsensiPtk::with('guru')
            ->where('lembaga_id', $lembagaId)
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan]);

        if ($guruId) {
            $query->where('guru_id', $guruId);
        }

        $absensis = $query->orderBy('tanggal')->orderBy('guru_id')->paginate($this->perPage($request));

        $gurus = Guru::where('lembaga_id', $lembagaId)
            ->where('is_approved', true)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        // Summary per guru
        $summary = [];
        if (!$guruId) {
            $summaryQuery = AbsensiPtk::where('lembaga_id', $lembagaId)
                ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan])
                ->selectRaw('guru_id, status, COUNT(*) as jumlah')
                ->groupBy('guru_id', 'status')
                ->get();

            foreach ($summaryQuery as $row) {
                $summary[$row->guru_id][$row->status] = $row->jumlah;
            }
        }

        return view('absensi-ptk.laporan', compact('absensis', 'gurus', 'bulan', 'guruId', 'summary'));
    }

    /**
     * Check-in guru.
     */
    public function checkIn(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $lembaga = Lembaga::findOrFail($lembagaId);
        $guru = Guru::where('lembaga_id', $lembagaId)
            ->where('id', $user->guru_id)
            ->firstOrFail();

        $today = Carbon::today();
        $hari = $today->locale('id')->dayName;

        $jamKerja = JamKerjaLembaga::where('lembaga_id', $lembagaId)
            ->where('hari', $hari)
            ->where('is_active', true)
            ->first();

        if (!$jamKerja) {
            return back()->with('error', 'Tidak ada jam kerja untuk hari ini.');
        }

        // Cek duplikat
        $exists = AbsensiPtk::where('guru_id', $guru->id)
            ->where('tanggal', $today->toDateString())
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah check-in hari ini.');
        }

        // Validasi lokasi GPS jika lembaga mengatur radius
        $lokasi = $request->input('lokasi');
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ($lembaga->latitude_absen && $lembaga->longitude_absen && $lat && $lng) {
            $jarak = $this->hitungJarak(
                (float) $lembaga->latitude_absen,
                (float) $lembaga->longitude_absen,
                (float) $lat,
                (float) $lng
            );
            if ($jarak > $lembaga->radius_absen_meter) {
                return back()->with('error', "Anda berada di luar radius absen ({$jarak}m dari titik absen, maks {$lembaga->radius_absen_meter}m).");
            }
        }

        // Validasi selfie jika wajib
        if ($lembaga->wajib_selfie && !$request->hasFile('foto') && !$request->filled('foto')) {
            return back()->with('error', 'Wajib upload foto selfie untuk check-in.');
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('absensi-ptk/checkin', 'public');
        } elseif ($request->filled('foto')) {
            $fotoPath = $this->simpanBase64Foto($request->input('foto'), 'absensi-ptk/checkin');
        }

        $now = Carbon::now();
        $jamMasukStr = substr($jamKerja->jam_masuk, 0, 5);
        $jamMasuk = Carbon::createFromFormat('H:i', $jamMasukStr);
        $batasTepat = $jamMasuk->copy()->addMinutes($jamKerja->toleransi_keterlambatan);

        $terlambatMenit = 0;
        $status = 'tepat_waktu';

        if ($now->gt($batasTepat)) {
            $terlambatMenit = (int) abs($now->floatDiffInMinutes($jamMasuk));
            $status = 'terlambat';
        }

        AbsensiPtk::create([
            'guru_id' => $guru->id,
            'lembaga_id' => $lembagaId,
            'tanggal' => $today->toDateString(),
            'check_in' => $now,
            'jam_masuk_set' => $jamKerja->jam_masuk,
            'jam_pulang_set' => $jamKerja->jam_pulang,
            'status' => $status,
            'keterlambatan_menit' => $terlambatMenit,
            'lokasi_check_in' => $lokasi,
            'foto_check_in' => $fotoPath,
        ]);

        LogAktivita::log('checkin', 'Check-in absensi PTK');

        $msg = $status === 'tepat_waktu' ? 'Check-in berhasil. Tepat waktu!' : "Check-in berhasil. Terlambat {$terlambatMenit} menit.";
        return redirect()->route('absensi-ptk.index')->with('success', $msg);
    }

    /**
     * Check-out guru.
     */
    public function checkOut(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $lembaga = Lembaga::findOrFail($lembagaId);
        $guru = Guru::where('lembaga_id', $lembagaId)
            ->where('id', $user->guru_id)
            ->firstOrFail();

        $today = Carbon::today();

        $absensi = AbsensiPtk::where('guru_id', $guru->id)
            ->where('tanggal', $today->toDateString())
            ->first();

        if (!$absensi) {
            return back()->with('error', 'Anda belum check-in hari ini.');
        }

        if ($absensi->check_out) {
            return back()->with('error', 'Anda sudah check-out hari ini.');
        }

        // Validasi lokasi GPS jika lembaga mengatur radius
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        if ($lembaga->latitude_absen && $lembaga->longitude_absen && $lat && $lng) {
            $jarak = $this->hitungJarak(
                (float) $lembaga->latitude_absen,
                (float) $lembaga->longitude_absen,
                (float) $lat,
                (float) $lng
            );
            if ($jarak > $lembaga->radius_absen_meter) {
                return back()->with('error', "Anda berada di luar radius absen ({$jarak}m dari titik absen, maks {$lembaga->radius_absen_meter}m).");
            }
        }

        $fotoPath = $absensi->foto_check_out;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('absensi-ptk/checkout', 'public');
        } elseif ($request->filled('foto')) {
            $fotoPath = $this->simpanBase64Foto($request->input('foto'), 'absensi-ptk/checkout');
        }

        $now = Carbon::now();
        $jamPulangStr = substr($absensi->jam_pulang_set, 0, 5);
        $jamPulang = Carbon::createFromFormat('H:i', $jamPulangStr);

        $update = [
            'check_out' => $now,
            'lokasi_check_out' => $request->input('lokasi'),
            'foto_check_out' => $fotoPath,
        ];

        // Deteksi pulang awal
        if ($now->lt($jamPulang)) {
            $update['status'] = 'pulang_awal';
        }

        $absensi->update($update);

        LogAktivita::log('checkout', 'Check-out absensi PTK');

        $msg = $absensi->status === 'pulang_awal' ? 'Check-out berhasil. (Pulang awal)' : 'Check-out berhasil.';
        return redirect()->route('absensi-ptk.index')->with('success', $msg);
    }

    /**
     * Simpan base64 foto ke storage, resize ke 400x400 max.
     */
    private function simpanBase64Foto(string $base64, string $path): string
    {
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $data = base64_decode($base64);

        $img = imagecreatefromstring($data);
        if (!$img) {
            // fallback: simpan asli
            $filename = uniqid('selfie_') . '.jpg';
            $relativePath = $path . '/' . $filename;
            \Illuminate\Support\Facades\Storage::disk('public')->put($relativePath, $data);
            return $relativePath;
        }

        $ow = imagesx($img);
        $oh = imagesy($img);
        $max = 400;
        if ($ow <= $max && $oh <= $max) {
            // sudah kecil
            $filename = uniqid('selfie_') . '.jpg';
            $relativePath = $path . '/' . $filename;
            \Illuminate\Support\Facades\Storage::disk('public')->put($relativePath, $data);
            imagedestroy($img);
            return $relativePath;
        }

        $ratio = min($max / $ow, $max / $oh);
        $nw = (int) round($ow * $ratio);
        $nh = (int) round($oh * $ratio);

        $thumb = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $nw, $nh, $ow, $oh);

        ob_start();
        imagejpeg($thumb, null, 75);
        $jpeg = ob_get_clean();

        imagedestroy($img);
        imagedestroy($thumb);

        $filename = uniqid('selfie_') . '.jpg';
        $relativePath = $path . '/' . $filename;
        \Illuminate\Support\Facades\Storage::disk('public')->put($relativePath, $jpeg);

        return $relativePath;
    }

    /**
     * Hitung jarak dua titik koordinat dengan rumus Haversine (meter).
     */
    private function hitungJarak(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }
}
