<?php

namespace App\Http\Controllers;

use App\Models\DetailPresensi;
use App\Models\Jadwal;
use App\Models\LogAktivita;
use App\Models\Presensi;
use App\Models\RiwayatKelasSiswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PresensiController extends Controller
{
    /**
     * Daftar jadwal hari ini (Guru) + riwayat presensi.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $guruId = $user->guru_id;

        $today = Carbon::today();

        // Jadwal hari ini
        $jadwalHariIni = Jadwal::with(['mapel', 'kelas'])
            ->where('lembaga_id', $lembagaId)
            ->where('guru_id', $guruId)
            ->where('hari', $today->locale('id')->dayName)
            ->orderBy('jam_ke')
            ->get();

        // Riwayat presensi
        $presensis = Presensi::with(['jadwal.mapel', 'jadwal.kelas', 'detailPresensis'])
            ->whereHas('jadwal', fn($q) => $q->where('guru_id', $guruId))
            ->when($request->filled('tanggal'), fn($q) => $q->where('tanggal', $request->tanggal))
            ->when($request->filled('jadwal_id'), fn($q) => $q->where('jadwal_id', $request->jadwal_id))
            ->orderByDesc('tanggal')
            ->orderByDesc('pertemuan_ke')
            ->paginate(20);

        return view('presensi.index', compact('jadwalHariIni', 'presensis'));
    }

    /**
     * Form input presensi: pilih jadwal → tampilkan siswa.
     */
    public function create(Request $request): View
    {
        $jadwalId = $request->get('jadwal_id');
        $jadwal = Jadwal::with(['mapel', 'kelas'])->findOrFail($jadwalId);

        $user = auth()->user();
        if ($jadwal->guru_id != $user->guru_id) {
            abort(403);
        }

        // Ambil siswa aktif di kelas ini (tahun ajaran aktif)
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

        // Pertemuan ke-
        $lastPertemuan = Presensi::where('jadwal_id', $jadwal->id)->max('pertemuan_ke') ?? 0;
        $pertemuanKe = $lastPertemuan + 1;

        // Cek apakah sudah ada presensi hari ini untuk jadwal ini
        $existingToday = Presensi::where('jadwal_id', $jadwal->id)
            ->where('tanggal', Carbon::today()->toDateString())
            ->exists();

        return view('presensi.create', compact('jadwal', 'siswas', 'pertemuanKe', 'existingToday'));
    }

    /**
     * Simpan presensi.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $validated = $request->validate([
            'jadwal_id' => 'required|exists:jadwals,id',
            'materi' => 'nullable|string|max:255',
            'siswa' => 'required|array',
            'siswa.*.id' => 'required|exists:siswas,id',
            'siswa.*.status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'siswa.*.keterangan' => 'nullable|string|max:255',
        ]);

        $jadwal = Jadwal::findOrFail($validated['jadwal_id']);

        if ($jadwal->guru_id != $user->guru_id) {
            return back()->with('error', 'Jadwal tidak sesuai.');
        }

        $today = Carbon::today();

        // Cek duplikat
        $exists = Presensi::where('jadwal_id', $jadwal->id)
            ->where('tanggal', $today->toDateString())
            ->exists();

        if ($exists) {
            return back()->with('error', 'Presensi untuk jadwal ini hari ini sudah ada.');
        }

        $lastPertemuan = Presensi::where('jadwal_id', $jadwal->id)->max('pertemuan_ke') ?? 0;
        $pertemuanKe = $lastPertemuan + 1;

        $presensi = Presensi::create([
            'jadwal_id' => $jadwal->id,
            'pertemuan_ke' => $pertemuanKe,
            'tanggal' => $today->toDateString(),
            'jam_mulai' => Carbon::now()->format('H:i'),
            'materi' => $validated['materi'] ?? null,
        ]);

        $siswaData = [];
        foreach ($validated['siswa'] as $s) {
            $siswaData[] = [
                'presensi_id' => $presensi->id,
                'siswa_id' => $s['id'],
                'status' => $s['status'],
                'keterangan' => $s['keterangan'] ?? null,
            ];
        }

        DetailPresensi::insert($siswaData);

        LogAktivita::log('create', 'Presensi ' . $jadwal->mapel->nama . ' ' . $jadwal->kelas->nama . ' pertemuan ke-' . $pertemuanKe);

        return redirect()->route('presensi.show', $presensi->id)
            ->with('success', 'Presensi pertemuan ke-' . $pertemuanKe . ' berhasil disimpan.');
    }

    /**
     * Detail presensi.
     */
    public function show(Presensi $presensi): View
    {
        $presensi->load(['jadwal.mapel', 'jadwal.kelas', 'detailPresensis.siswa']);

        $user = auth()->user();
        // Guru hanya bisa lihat punya sendiri
        if ($user->isGuru() && $presensi->jadwal->guru_id != $user->guru_id) {
            abort(403);
        }

        return view('presensi.show', compact('presensi'));
    }

    /**
     * Edit presensi.
     */
    public function edit(Presensi $presensi): View
    {
        $presensi->load(['jadwal.mapel', 'jadwal.kelas', 'detailPresensis.siswa']);

        $user = auth()->user();
        if ($presensi->jadwal->guru_id != $user->guru_id) {
            abort(403);
        }

        return view('presensi.edit', compact('presensi'));
    }

    /**
     * Update presensi.
     */
    public function update(Request $request, Presensi $presensi): RedirectResponse
    {
        $user = auth()->user();
        if ($presensi->jadwal->guru_id != $user->guru_id) {
            abort(403);
        }

        $validated = $request->validate([
            'materi' => 'nullable|string|max:255',
            'siswa' => 'required|array',
            'siswa.*.id' => 'required|exists:detail_presensis,id',
            'siswa.*.status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'siswa.*.keterangan' => 'nullable|string|max:255',
        ]);

        $presensi->update([
            'materi' => $validated['materi'] ?? null,
        ]);

        foreach ($validated['siswa'] as $s) {
            DetailPresensi::where('id', $s['id'])
                ->update([
                    'status' => $s['status'],
                    'keterangan' => $s['keterangan'] ?? null,
                ]);
        }

        LogAktivita::log('update', 'Update presensi ID ' . $presensi->id);

        return redirect()->route('presensi.show', $presensi->id)
            ->with('success', 'Presensi berhasil diperbarui.');
    }

    /**
     * Rekap presensi — untuk Kurikulum / Kepala Lembaga / Admin Lembaga.
     */
    public function rekap(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));
        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');

        $query = Presensi::with(['jadwal.mapel', 'jadwal.kelas', 'jadwal.guru', 'detailPresensis'])
            ->whereHas('jadwal', fn($q) => $q->where('lembaga_id', $lembagaId))
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulan]);

        if ($kelasId) {
            $query->whereHas('jadwal', fn($q) => $q->where('kelas_id', $kelasId));
        }
        if ($mapelId) {
            $query->whereHas('jadwal', fn($q) => $q->where('mapel_id', $mapelId));
        }

        $presensis = $query->orderByDesc('tanggal')->paginate(20);

        $kelas = \App\Models\Kelas::where('lembaga_id', $lembagaId)->orderBy('nama')->get();
        $mapels = \App\Models\Mapel::where('lembaga_id', $lembagaId)->orderBy('nama')->get();

        // Summary statistik
        $summary = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'terlambat' => 0];
        $totalSiswa = 0;
        foreach ($presensis as $p) {
            foreach ($p->detailPresensis as $d) {
                $summary[$d->status] = ($summary[$d->status] ?? 0) + 1;
                $totalSiswa++;
            }
        }

        return view('presensi.rekap', compact('presensis', 'kelas', 'mapels', 'bulan', 'kelasId', 'mapelId', 'summary', 'totalSiswa'));
    }
}
