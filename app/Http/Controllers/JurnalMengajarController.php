<?php

namespace App\Http\Controllers;

use App\Models\DetailJurnalSiswa;
use App\Models\Jadwal;
use App\Models\JurnalMengajar;
use App\Models\LogAktivita;
use App\Models\RiwayatKelasSiswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class JurnalMengajarController extends Controller
{
    /**
     * Daftar jurnal guru yang login.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $guruId = $user->guru_id;

        $jurnals = JurnalMengajar::with(['jadwal.mapel', 'jadwal.kelas', 'kelas', 'detailSiswas'])
            ->where('guru_id', $guruId)
            ->when($request->filled('tanggal'), fn($q) => $q->where('tanggal', $request->tanggal))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate(20);

        $today = Carbon::today();
        $jadwalHariIni = Jadwal::with(['mapel', 'kelas'])
            ->where('lembaga_id', $lembagaId)
            ->where('guru_id', $guruId)
            ->where('hari', $today->locale('id')->dayName)
            ->orderBy('jam_ke')
            ->get();

        return view('jurnal-mengajar.index', compact('jurnals', 'jadwalHariIni'));
    }

    /**
     * Wizard: pilih jadwal → selfie → presensi siswa → materi.
     */
    public function create(Request $request): View
    {
        $jadwalId = $request->get('jadwal_id');
        $jadwal = Jadwal::with(['mapel', 'kelas'])->findOrFail($jadwalId);

        $user = auth()->user();
        if ($jadwal->guru_id != $user->guru_id) {
            abort(403);
        }

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $user->yayasan_id)
            ->where('is_active', true)
            ->first();

        $siswas = [];
        if ($tahunAjaranAktif) {
            $siswas = RiwayatKelasSiswa::with('siswa')
                ->where('kelas_id', $jadwal->kelas_id)
                ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                ->whereNull('tanggal_keluar')
                ->get()
                ->pluck('siswa')
                ->filter();
        }

        $lastPertemuan = JurnalMengajar::where('jadwal_id', $jadwal->id)->max('pertemuan_ke') ?? 0;
        $pertemuanKe = $lastPertemuan + 1;

        $existingToday = JurnalMengajar::where('jadwal_id', $jadwal->id)
            ->where('tanggal', Carbon::today()->toDateString())
            ->exists();

        // Cek apakah ada jam selanjutnya yg berurutan (guru+kelas+mapel+hari sama)
        $nextJadwals = Jadwal::where('guru_id', $jadwal->guru_id)
            ->where('kelas_id', $jadwal->kelas_id)
            ->where('mapel_id', $jadwal->mapel_id)
            ->where('hari', $jadwal->hari)
            ->where('jam_ke', '>', $jadwal->jam_ke)
            ->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
            ->orderBy('jam_ke')
            ->get();

        return view('jurnal-mengajar.create', compact('jadwal', 'siswas', 'pertemuanKe', 'existingToday', 'nextJadwals'));
    }

    /**
     * Simpan jurnal lengkap (selfie + presensi + materi).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $guruId = $user->guru_id;

        $validated = $request->validate([
            'jadwal_id' => 'required|exists:jadwals,id',
            'foto_base64' => 'required|string',
            'latitude' => 'required|string|max:50',
            'longitude' => 'required|string|max:50',
            'materi' => 'nullable|string|max:500',
            'siswa' => 'required|array',
            'siswa.*.id' => 'required|exists:siswas,id',
            'siswa.*.status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'siswa.*.keterangan' => 'nullable|string|max:255',
            'next_jadwal_ids' => 'nullable|array',
            'next_jadwal_ids.*' => 'exists:jadwals,id',
        ]);

        $jadwal = Jadwal::with(['mapel', 'kelas'])->findOrFail($validated['jadwal_id']);

        if ($jadwal->guru_id != $guruId) {
            return back()->with('error', 'Jadwal tidak sesuai.');
        }

        $today = Carbon::today();

        // Cek duplikat
        $exists = JurnalMengajar::where('jadwal_id', $jadwal->id)
            ->where('tanggal', $today->toDateString())
            ->exists();
        if ($exists) {
            return back()->with('error', 'Jurnal untuk jadwal ini hari ini sudah ada.');
        }

        $lastPertemuan = JurnalMengajar::where('jadwal_id', $jadwal->id)->max('pertemuan_ke') ?? 0;
        $pertemuanKe = $lastPertemuan + 1;

        // Simpan foto dari base64
        $base64 = $request->foto_base64;
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $base64 = substr($base64, strpos($base64, ',') + 1);
            $type = strtolower($type[1]);
            if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                return back()->with('error', 'Format foto tidak didukung.');
            }
            $base64 = base64_decode($base64);
            $filename = 'agenda/' . $lembagaId . '/' . $jadwal->id . '/' . uniqid() . '.' . $type;
            Storage::disk('public')->put($filename, $base64);
            $path = $filename;
        } else {
            return back()->with('error', 'Data foto tidak valid.');
        }

        $jurnal = JurnalMengajar::create([
            'jadwal_id' => $jadwal->id,
            'guru_id' => $guruId,
            'kelas_id' => $jadwal->kelas_id,
            'pertemuan_ke' => $pertemuanKe,
            'tanggal' => $today->toDateString(),
            'jam_mulai' => Carbon::now()->format('H:i'),
            'foto_path' => $path,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'materi' => $validated['materi'] ?? null,
            'metadata' => json_encode([
                'mapel' => $jadwal->mapel->nama,
                'kelas' => $jadwal->kelas->nama,
                'jam_ke' => $jadwal->jam_ke,
                'hari' => $jadwal->hari,
            ]),
        ]);

        // Batch insert detail siswa
        $siswaData = [];
        foreach ($validated['siswa'] as $s) {
            $siswaData[] = [
                'jurnal_mengajar_id' => $jurnal->id,
                'siswa_id' => $s['id'],
                'status' => $s['status'],
                'keterangan' => $s['keterangan'] ?? null,
            ];
        }
        DetailJurnalSiswa::insert($siswaData);

        LogAktivita::log('create', 'Jurnal mengajar ' . $jadwal->mapel->nama . ' ' . $jadwal->kelas->nama . ' pertemuan ke-' . $pertemuanKe);

        // Duplikasi jurnal ke jam selanjutnya jika checkbox dicentang
        $nextJadwalIds = $validated['next_jadwal_ids'] ?? [];
        foreach ($nextJadwalIds as $nextJadwalId) {
            $nextJadwal = Jadwal::with(['mapel', 'kelas'])->find($nextJadwalId);
            if (!$nextJadwal || $nextJadwal->guru_id != $guruId) {
                continue;
            }

            // Skip jika jadwal selanjutnya sudah ada jurnal hari ini
            if (
                JurnalMengajar::where('jadwal_id', $nextJadwal->id)
                    ->where('tanggal', $today->toDateString())
                    ->exists()
            ) {
                continue;
            }

            // Copy foto ke folder jadwal berikutnya
            $nextFilename = 'agenda/' . $lembagaId . '/' . $nextJadwal->id . '/' . uniqid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
            Storage::disk('public')->copy($path, $nextFilename);

            $nextLastPertemuan = JurnalMengajar::where('jadwal_id', $nextJadwal->id)->max('pertemuan_ke') ?? 0;
            $nextPertemuanKe = $nextLastPertemuan + 1;

            $nextJurnal = JurnalMengajar::create([
                'jadwal_id' => $nextJadwal->id,
                'guru_id' => $guruId,
                'kelas_id' => $nextJadwal->kelas_id,
                'pertemuan_ke' => $nextPertemuanKe,
                'tanggal' => $today->toDateString(),
                'jam_mulai' => Carbon::now()->format('H:i'),
                'foto_path' => $nextFilename,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'materi' => $validated['materi'] ?? null,
                'metadata' => json_encode([
                    'mapel' => $nextJadwal->mapel->nama,
                    'kelas' => $nextJadwal->kelas->nama,
                    'jam_ke' => $nextJadwal->jam_ke,
                    'hari' => $nextJadwal->hari,
                ]),
            ]);

            $nextSiswaData = [];
            foreach ($validated['siswa'] as $s) {
                $nextSiswaData[] = [
                    'jurnal_mengajar_id' => $nextJurnal->id,
                    'siswa_id' => $s['id'],
                    'status' => $s['status'],
                    'keterangan' => $s['keterangan'] ?? null,
                ];
            }
            DetailJurnalSiswa::insert($nextSiswaData);

            LogAktivita::log('create', 'Jurnal mengajar (auto) ' . $nextJadwal->mapel->nama . ' ' . $nextJadwal->kelas->nama . ' jam ke-' . $nextJadwal->jam_ke);
        }

        $totalJurnal = 1 + count($nextJadwalIds);

        return redirect()->route('jurnal-mengajar.show', $jurnal->id)
            ->with('success', 'Jurnal mengajar pertemuan ke-' . $pertemuanKe . ' berhasil disimpan' . ($totalJurnal > 1 ? ' untuk ' . $totalJurnal . ' jam pelajaran.' : '.'));
    }

    /**
     * Detail jurnal.
     */
    public function show(JurnalMengajar $jurnal): View
    {
        $jurnal->load(['jadwal.mapel', 'jadwal.kelas', 'kelas', 'guru', 'verifikator', 'detailSiswas.siswa']);

        $user = auth()->user();
        if ($user->isGuru() && $jurnal->jadwal->guru_id != $user->guru_id) {
            abort(403);
        }

        return view('jurnal-mengajar.show', compact('jurnal'));
    }

    /**
     * Monitoring jurnal (Kurikulum / Kepala Lembaga / Admin Lembaga).
     */
    public function monitoring(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $query = JurnalMengajar::with(['jadwal.mapel', 'jadwal.kelas', 'guru', 'kelas', 'detailSiswas'])
            ->whereHas('jadwal', fn($q) => $q->where('lembaga_id', $lembagaId));

        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }
        if ($request->filled('verified')) {
            $query->where('is_verified', $request->verified === '1');
        }

        $jurnals = $query->orderByDesc('tanggal')->orderByDesc('created_at')->paginate(20);

        $gurus = \App\Models\Guru::where('lembaga_id', $lembagaId)
            ->where('is_approved', true)
            ->orderBy('nama')
            ->get();

        return view('jurnal-mengajar.monitoring', compact('jurnals', 'gurus'));
    }

    /**
     * Verifikasi jurnal.
     */
    public function verify(JurnalMengajar $jurnal): RedirectResponse
    {
        $jurnal->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        LogAktivita::log('verify', 'Verifikasi jurnal mengajar ID ' . $jurnal->id);

        return back()->with('success', 'Jurnal berhasil diverifikasi.');
    }

    /**
     * Halaman edit jurnal (materi + presensi siswa).
     * Foto selfie & lokasi tidak bisa diubah.
     */
    public function edit(JurnalMengajar $jurnal): View
    {
        $jurnal->load(['jadwal.mapel', 'jadwal.kelas', 'kelas', 'detailSiswas.siswa']);

        $user = auth()->user();
        if ($jurnal->jadwal->guru_id != $user->guru_id) {
            abort(403);
        }

        // Siswa di kelas ini (aktif)
        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $user->yayasan_id)
            ->where('is_active', true)
            ->first();
        $siswas = [];
        if ($tahunAjaranAktif) {
            $siswas = RiwayatKelasSiswa::with('siswa')
                ->where('kelas_id', $jurnal->kelas_id)
                ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                ->whereNull('tanggal_keluar')
                ->get()
                ->pluck('siswa')
                ->filter();
        }

        // Map existing presensi by siswa_id
        $presensiMap = $jurnal->detailSiswas->keyBy('siswa_id');

        return view('jurnal-mengajar.edit', compact('jurnal', 'siswas', 'presensiMap'));
    }

    /**
     * Update jurnal (materi + presensi siswa).
     */
    public function update(Request $request, JurnalMengajar $jurnal): RedirectResponse
    {
        $user = auth()->user();
        if ($jurnal->jadwal->guru_id != $user->guru_id) {
            abort(403);
        }

        $validated = $request->validate([
            'materi' => 'nullable|string|max:500',
            'siswa' => 'required|array',
            'siswa.*.id' => 'required|exists:siswas,id',
            'siswa.*.status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'siswa.*.keterangan' => 'nullable|string|max:255',
        ]);

        // Update materi & jam_selesai
        $jurnal->update([
            'materi' => $validated['materi'] ?? null,
            'jam_selesai' => $jurnal->jam_selesai ?? Carbon::now()->format('H:i'),
        ]);

        // Sync presensi siswa: delete existing, insert new
        $jurnal->detailSiswas()->delete();
        $siswaData = [];
        foreach ($validated['siswa'] as $s) {
            $siswaData[] = [
                'jurnal_mengajar_id' => $jurnal->id,
                'siswa_id' => $s['id'],
                'status' => $s['status'],
                'keterangan' => $s['keterangan'] ?? null,
            ];
        }
        DetailJurnalSiswa::insert($siswaData);

        LogAktivita::log('update', 'Update jurnal mengajar ID ' . $jurnal->id);

        return redirect()->route('jurnal-mengajar.show', $jurnal->id)
            ->with('success', 'Jurnal mengajar berhasil diperbarui.');
    }

    /**
     * Hapus jurnal (guru sendiri).
     */
    public function destroy(JurnalMengajar $jurnal): RedirectResponse
    {
        if ($jurnal->foto_path) {
            Storage::disk('public')->delete($jurnal->foto_path);
        }

        $jurnal->delete();
        LogAktivita::log('delete', 'Menghapus jurnal mengajar ID ' . $jurnal->id);

        return redirect()->route('jurnal-mengajar.index')->with('success', 'Jurnal berhasil dihapus.');
    }
}
