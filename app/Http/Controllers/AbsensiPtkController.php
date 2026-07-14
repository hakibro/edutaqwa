<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPtk;
use App\Models\Guru;
use App\Models\JamKerjaLembaga;
use App\Models\LogAktivita;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsensiPtkController extends Controller
{
    /**
     * Riwayat absensi guru yang login.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
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

        $absensis = $query->orderBy('tanggal')->orderBy('guru_id')->paginate(30);

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

        $now = Carbon::now();
        $jamMasukStr = substr($jamKerja->jam_masuk, 0, 5); // 'H:i' only
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
            'lokasi_check_in' => $request->input('lokasi'),
            'foto_check_in' => $request->input('foto'),
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

        $now = Carbon::now();
        $jamPulangStr = substr($absensi->jam_pulang_set, 0, 5);
        $jamPulang = Carbon::createFromFormat('H:i', $jamPulangStr);

        $update = [
            'check_out' => $now,
            'lokasi_check_out' => $request->input('lokasi'),
            'foto_check_out' => $request->input('foto'),
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
}
