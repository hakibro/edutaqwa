<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPtk;
use App\Models\Atp;
use App\Models\DetailJurnalSiswa;
use App\Models\Jadwal;
use App\Models\JurnalMengajar;
use App\Models\LogAktivita;
use App\Models\RiwayatKelasSiswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class JurnalMengajarController extends Controller
{
    use PerPageTrait;

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
            ->where('is_draft', false)
            ->when($request->filled('tanggal'), fn($q) => $q->where('tanggal', $request->tanggal))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate($this->perPage($request));

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

        $lastPertemuan = JurnalMengajar::where('jadwal_id', $jadwal->id)
            ->where('is_draft', false)
            ->max('pertemuan_ke') ?? 0;
        $pertemuanKe = $lastPertemuan + 1;

        $existingToday = JurnalMengajar::where('jadwal_id', $jadwal->id)
            ->where('tanggal', Carbon::today()->toDateString())
            ->where('is_draft', false)
            ->exists();

        // Cek draft tersimpan (per-step save)
        $draft = JurnalMengajar::where('jadwal_id', $jadwal->id)
            ->where('guru_id', $user->guru_id)
            ->where('tanggal', Carbon::today()->toDateString())
            ->where('is_draft', true)
            ->first();

        // Presensi dari draft (jika ada)
        $existingPresensi = [];
        if ($draft) {
            $existingPresensi = DetailJurnalSiswa::where('jurnal_mengajar_id', $draft->id)
                ->get()
                ->keyBy('siswa_id');
        }

        // Cek apakah ada jam selanjutnya yg berurutan (guru+kelas+mapel+hari sama)
        $nextJadwals = Jadwal::where('guru_id', $jadwal->guru_id)
            ->where('kelas_id', $jadwal->kelas_id)
            ->where('mapel_id', $jadwal->mapel_id)
            ->where('hari', $jadwal->hari)
            ->where('jam_ke', '>', $jadwal->jam_ke)
            ->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
            ->orderBy('jam_ke')
            ->get();

        // Ambil ATP milik guru ini untuk mapel tersebut — opsional, tampilkan info CP + TP
        $atps = Atp::with(['tp.cp'])
            ->whereHas('tp.cp', function ($q) use ($jadwal, $user) {
                $q->where('mapel_id', $jadwal->mapel_id)
                    ->where('guru_id', $user->guru_id);
            })
            ->orderBy('minggu_ke')
            ->get();

        return view('jurnal-mengajar.create', compact('jadwal', 'siswas', 'pertemuanKe', 'existingToday', 'nextJadwals', 'draft', 'existingPresensi', 'atps'));
    }

    /**
     * Simpan jurnal lengkap (selfie + presensi + materi).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $guruId = $user->guru_id;

        $draftId = $request->input('draft_id');

        $rules = [
            'jadwal_id' => 'required|exists:jadwals,id',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'materi' => 'nullable|string|max:500',
            'atp_id' => 'nullable|exists:atps,id',
            'siswa' => 'required|array',
            'siswa.*.id' => 'required|exists:siswas,id',
            'siswa.*.status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'siswa.*.keterangan' => 'nullable|string|max:255',
            'next_jadwal_ids' => 'nullable|array',
            'next_jadwal_ids.*' => 'exists:jadwals,id',
        ];

        // Jika ada draft_id, foto_base64 optional (pakai foto_path dari draft)
        if (!$draftId) {
            $rules['foto_base64'] = 'required|string';
        }

        $validated = $request->validate($rules);

        $jadwal = Jadwal::with(['mapel', 'kelas'])->findOrFail($validated['jadwal_id']);

        if ($jadwal->guru_id != $guruId) {
            return back()->with('error', 'Jadwal tidak sesuai.');
        }

        $today = Carbon::today();

        // Jika ada draft, gunakan draft itu
        if ($draftId) {
            $jurnal = JurnalMengajar::where('id', $draftId)
                ->where('guru_id', $guruId)
                ->where('is_draft', true)
                ->first();
            if (!$jurnal) {
                return back()->with('error', 'Draft tidak ditemukan.');
            }

            // Hitung pertemuan_ke saat finalisasi
            $lastPertemuan = JurnalMengajar::where('jadwal_id', $jadwal->id)
                ->where('is_draft', false)
                ->where('id', '!=', $draftId)
                ->max('pertemuan_ke') ?? 0;
            $jurnal->pertemuan_ke = $lastPertemuan + 1;
            $jurnal->kelas_id = $jadwal->kelas_id;

            // Jika foto_base64 dikirim ulang, ganti foto
            if ($request->filled('foto_base64')) {
                // Hapus foto lama
                if ($jurnal->foto_path) {
                    Storage::disk('public')->delete($jurnal->foto_path);
                }
                $path = $this->saveFoto($request->foto_base64, $lembagaId, $jadwal->id);
                $jurnal->foto_path = $path;
            }

            $jurnal->latitude = $validated['latitude'] ?? null;
            $jurnal->longitude = $validated['longitude'] ?? null;
            $jurnal->materi = $validated['materi'] ?? null;
            $jurnal->atp_id = $validated['atp_id'] ?? null;
            $jurnal->jam_mulai = Carbon::now()->format('H:i');
            $jurnal->is_draft = false;
            $jurnal->draft_step = 0;
            $jurnal->metadata = json_encode([
                'mapel' => $jadwal->mapel->nama,
                'kelas' => $jadwal->kelas->nama,
                'jam_ke' => $jadwal->jam_ke,
                'hari' => $jadwal->hari,
            ]);
            $jurnal->save();

            // Hapus detail siswa lama (draft), ganti dgn yg baru
            DetailJurnalSiswa::where('jurnal_mengajar_id', $jurnal->id)->delete();

            // $path untuk next_jadwal copy
            $path = $jurnal->foto_path;
        } else {
            // Cek duplikat (non-draft only)
            $exists = JurnalMengajar::where('jadwal_id', $jadwal->id)
                ->where('tanggal', $today->toDateString())
                ->where('is_draft', false)
                ->exists();
            if ($exists) {
                return back()->with('error', 'Jurnal untuk jadwal ini hari ini sudah ada.');
            }

            $lastPertemuan = JurnalMengajar::where('jadwal_id', $jadwal->id)
                ->where('is_draft', false)
                ->max('pertemuan_ke') ?? 0;
            $pertemuanKe = $lastPertemuan + 1;

            // Simpan foto dari base64
            $path = $this->saveFoto($request->foto_base64, $lembagaId, $jadwal->id);

            $jurnal = JurnalMengajar::create([
                'jadwal_id' => $jadwal->id,
                'guru_id' => $guruId,
                'kelas_id' => $jadwal->kelas_id,
                'atp_id' => $validated['atp_id'] ?? null,
                'pertemuan_ke' => $pertemuanKe,
                'tanggal' => $today->toDateString(),
                'jam_mulai' => Carbon::now()->format('H:i'),
                'foto_path' => $path,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'materi' => $validated['materi'] ?? null,
                'is_draft' => false,
                'draft_step' => 0,
                'metadata' => json_encode([
                    'mapel' => $jadwal->mapel->nama,
                    'kelas' => $jadwal->kelas->nama,
                    'jam_ke' => $jadwal->jam_ke,
                    'hari' => $jadwal->hari,
                ]),
            ]);
        }

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

        $pertemuanKe = $jurnal->pertemuan_ke;

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
        $jurnal->load(['jadwal.mapel', 'jadwal.kelas', 'kelas', 'guru', 'verifikator', 'detailSiswas.siswa', 'atp.tp.cp']);

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
            ->where('is_draft', false)
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

        $jurnals = $query->orderByDesc('tanggal')->orderByDesc('created_at')->paginate($this->perPage($request));

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

        // Ambil ATP milik guru ini untuk mapel tersebut
        $atps = Atp::with(['tp.cp'])
            ->whereHas('tp.cp', function ($q) use ($jurnal, $user) {
                $q->where('mapel_id', $jurnal->jadwal->mapel_id)
                    ->where('guru_id', $user->guru_id);
            })
            ->orderBy('minggu_ke')
            ->get();

        return view('jurnal-mengajar.edit', compact('jurnal', 'siswas', 'presensiMap', 'atps'));
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
            'atp_id' => 'nullable|exists:atps,id',
            'siswa' => 'required|array',
            'siswa.*.id' => 'required|exists:siswas,id',
            'siswa.*.status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'siswa.*.keterangan' => 'nullable|string|max:255',
        ]);

        // Update materi & jam_selesai
        $jurnal->update([
            'materi' => $validated['materi'] ?? null,
            'atp_id' => $validated['atp_id'] ?? null,
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
     * Simpan foto base64 ke storage.
     */
    private function saveFoto(string $fotoBase64, int $lembagaId, int $jadwalId): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $fotoBase64, $type)) {
            $base64 = substr($fotoBase64, strpos($fotoBase64, ',') + 1);
            $type = strtolower($type[1]);
            if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                throw new \InvalidArgumentException('Format foto tidak didukung.');
            }
            $base64 = base64_decode($base64);
            $filename = 'agenda/' . $lembagaId . '/' . $jadwalId . '/' . uniqid() . '.' . $type;
            Storage::disk('public')->put($filename, $base64);
            return $filename;
        }
        throw new \InvalidArgumentException('Data foto tidak valid.');
    }

    /**
     * Simpan draft per-step (AJAX).
     */
    public function saveDraft(Request $request): JsonResponse
    {
        $user = auth()->user();
        $guruId = $user->guru_id;
        $lembagaId = $user->lembaga_id;

        $step = intval($request->input('step', 1));
        $jadwalId = $request->input('jadwal_id');
        $draftId = $request->input('draft_id');

        if (!$jadwalId) {
            return response()->json(['ok' => false, 'message' => 'jadwal_id wajib.'], 422);
        }

        $jadwal = Jadwal::find($jadwalId);
        if (!$jadwal || $jadwal->guru_id != $guruId) {
            return response()->json(['ok' => false, 'message' => 'Jadwal tidak valid.'], 403);
        }

        $today = Carbon::today()->toDateString();

        // Cari atau buat draft
        $draft = null;
        if ($draftId) {
            $draft = JurnalMengajar::where('id', $draftId)
                ->where('guru_id', $guruId)
                ->where('is_draft', true)
                ->first();
        }
        if (!$draft) {
            $draft = JurnalMengajar::where('jadwal_id', $jadwalId)
                ->where('guru_id', $guruId)
                ->where('tanggal', $today)
                ->where('is_draft', true)
                ->first();
        }

        $createData = [
            'jadwal_id' => $jadwalId,
            'guru_id' => $guruId,
            'kelas_id' => $jadwal->kelas_id,
            'tanggal' => $today,
            'is_draft' => true,
            'draft_step' => $step,
            'pertemuan_ke' => 0,
        ];

        if ($step >= 1) {
            if ($request->filled('foto_base64')) {
                try {
                    $path = $this->saveFoto($request->foto_base64, $lembagaId, $jadwal->id);
                    $createData['foto_path'] = $path;
                } catch (\InvalidArgumentException $e) {
                    return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
                }
            } elseif ($draft && $draft->foto_path) {
                $createData['foto_path'] = $draft->foto_path;
            }
            $createData['latitude'] = $request->input('latitude', $draft->latitude ?? null);
            $createData['longitude'] = $request->input('longitude', $draft->longitude ?? null);
            $createData['jam_mulai'] = $draft->jam_mulai ?? Carbon::now()->format('H:i');
        }

        if ($step >= 2 && $request->has('siswa')) {
            $createData['draft_step'] = 2;
        }

        if ($step >= 3) {
            $createData['materi'] = $request->input('materi', $draft->materi ?? null);
            $createData['atp_id'] = $request->input('atp_id', $draft->atp_id ?? null);
            $createData['draft_step'] = 3;
        }

        if ($draft) {
            // Hapus foto lama jika diganti
            if ($request->filled('foto_base64') && $draft->foto_path && isset($path) && $path !== $draft->foto_path) {
                Storage::disk('public')->delete($draft->foto_path);
            }
            $draft->update($createData);
        } else {
            $draft = JurnalMengajar::create($createData);
        }

        // Simpan presensi siswa (step 2)
        if ($step >= 2 && $request->has('siswa')) {
            DetailJurnalSiswa::where('jurnal_mengajar_id', $draft->id)->delete();
            $siswaData = [];
            foreach ($request->input('siswa', []) as $s) {
                if (!isset($s['id']))
                    continue;
                $siswaData[] = [
                    'jurnal_mengajar_id' => $draft->id,
                    'siswa_id' => $s['id'],
                    'status' => $s['status'] ?? 'hadir',
                    'keterangan' => $s['keterangan'] ?? null,
                ];
            }
            if (!empty($siswaData)) {
                DetailJurnalSiswa::insert($siswaData);
            }
        }

        return response()->json([
            'ok' => true,
            'draft_id' => $draft->id,
            'step' => $step,
            'message' => 'Draft tersimpan.',
        ]);
    }

    /**
     * Hapus draft (jika user batal).
     */
    public function destroyDraft(JurnalMengajar $jurnal): JsonResponse
    {
        $user = auth()->user();
        if ($jurnal->jadwal->guru_id != $user->guru_id || !$jurnal->is_draft) {
            return response()->json(['ok' => false, 'message' => 'Tidak dapat menghapus draft ini.'], 403);
        }

        if ($jurnal->foto_path) {
            Storage::disk('public')->delete($jurnal->foto_path);
        }

        $jurnal->delete();

        return response()->json(['ok' => true, 'message' => 'Draft dihapus.']);
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
