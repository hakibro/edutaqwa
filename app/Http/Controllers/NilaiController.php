<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JenisNilai;
use App\Models\Kelas;
use App\Models\LogAktivita;
use App\Models\Mapel;
use App\Models\Nilai;
use App\Models\PengajaranMapel;
use App\Models\RiwayatKelasSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Tp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NilaiController extends Controller
{
    // ─── Jenis Nilai (Kurikulum) ─────────────────────────────

    public function jenisNilaiIndex(): View
    {
        $user = auth()->user();
        $jenisNilais = JenisNilai::where('lembaga_id', $user->lembaga_id)->get();

        return view('nilai.jenis-nilai.index', compact('jenisNilais'));
    }

    public function jenisNilaiStore(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'bobot' => 'required|numeric|min:0|max:100',
        ]);

        JenisNilai::create([
            'lembaga_id' => $user->lembaga_id,
            'nama' => $validated['nama'],
            'bobot' => $validated['bobot'],
        ]);

        LogAktivita::log('create', 'Menambah jenis nilai "' . $validated['nama'] . '" (bobot: ' . $validated['bobot'] . ')');

        return redirect()->route('nilai.jenis-nilai.index')->with('success', 'Jenis nilai berhasil ditambahkan.');
    }

    public function jenisNilaiUpdate(Request $request, JenisNilai $jenisNilai): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'bobot' => 'required|numeric|min:0|max:100',
        ]);

        $jenisNilai->update($validated);

        LogAktivita::log('update', 'Mengubah jenis nilai "' . $validated['nama'] . '"');

        return redirect()->route('nilai.jenis-nilai.index')->with('success', 'Jenis nilai berhasil diubah.');
    }

    public function jenisNilaiDestroy(JenisNilai $jenisNilai): RedirectResponse
    {
        $nama = $jenisNilai->nama;
        $jenisNilai->delete();

        LogAktivita::log('delete', 'Menghapus jenis nilai "' . $nama . '"');

        return redirect()->route('nilai.jenis-nilai.index')->with('success', 'Jenis nilai berhasil dihapus.');
    }

    // ─── Input Nilai (Guru) ──────────────────────────────────

    /**
     * Daftar mapel yang diampu guru → pilih kelas → input nilai.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        if (!$guru) {
            abort(403, 'Akun guru tidak ditemukan.');
        }

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $user->yayasan_id)
            ->where('is_active', true)
            ->first();

        // Mapel yang diampu guru
        $pengajaran = PengajaranMapel::with(['mapel', 'kelas'])
            ->where('guru_id', $guru->id)
            ->when($tahunAjaranAktif, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktif->id))
            ->get();

        // Jenis nilai yang tersedia
        $jenisNilais = JenisNilai::where('lembaga_id', $user->lembaga_id)->get();

        // Filter existing nilai
        $query = Nilai::with(['siswa', 'mapel', 'kelas', 'jenisNilai', 'tp'])
            ->where('guru_id', $guru->id);

        if ($tahunAjaranAktif) {
            $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('jenis_nilai_id')) {
            $query->where('jenis_nilai_id', $request->jenis_nilai_id);
        }

        $nilais = $query->latest()->paginate(25);

        // Ambil semua kelas dari pengajaran (buat filter dropdown)
        $kelasIds = $pengajaran->pluck('kelas_id')->filter()->unique();
        $kelass = Kelas::whereIn('id', $kelasIds)->get();
        $mapels = $pengajaran->pluck('mapel')->unique('id');

        return view('nilai.index', compact('pengajaran', 'nilais', 'jenisNilais', 'mapels', 'kelass', 'tahunAjaranAktif'));
    }

    /**
     * Form input nilai: pilih mapel & kelas → tampilkan daftar siswa + TP.
     */
    public function create(Request $request): View
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        if (!$guru) {
            abort(403, 'Akun guru tidak ditemukan.');
        }

        $mapelId = $request->get('mapel_id');
        $kelasId = $request->get('kelas_id');
        $jenisNilaiId = $request->get('jenis_nilai_id');

        $mapel = Mapel::findOrFail($mapelId);
        $kelas = Kelas::findOrFail($kelasId);
        $jenisNilai = JenisNilai::findOrFail($jenisNilaiId);

        // Verifikasi guru mengajar mapel ini di kelas ini
        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $user->yayasan_id)
            ->where('is_active', true)
            ->first();

        $isPengajar = PengajaranMapel::where('guru_id', $guru->id)
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->when($tahunAjaranAktif, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktif->id))
            ->exists();

        if (!$isPengajar) {
            abort(403, 'Anda tidak mengajar mapel ini di kelas tersebut.');
        }

        // Daftar siswa di kelas ini
        $siswas = [];
        if ($tahunAjaranAktif) {
            $siswas = RiwayatKelasSiswa::with('siswa')
                ->where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                ->whereNull('tanggal_keluar')
                ->get()
                ->pluck('siswa')
                ->filter();
        }

        // TP yang tersedia (untuk jenis Harian)
        $tps = collect();
        $isHarian = strtolower($jenisNilai->nama) === 'harian';
        if ($isHarian) {
            $tps = Tp::whereHas('cp', fn($q) => $q->where('mapel_id', $mapelId)->where('guru_id', $guru->id))
                ->with('cp')
                ->get();
        }

        // Nilai yang sudah ada untuk kombinasi ini
        $existingNilai = Nilai::where('guru_id', $guru->id)
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->where('jenis_nilai_id', $jenisNilaiId)
            ->when($tahunAjaranAktif, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktif->id))
            ->get()
            ->keyBy(function ($n) {
                return $n->siswa_id . ($n->tp_id ? '_' . $n->tp_id : '');
            });

        return view('nilai.create', compact(
            'mapel',
            'kelas',
            'jenisNilai',
            'siswas',
            'tps',
            'existingNilai',
            'isHarian',
            'tahunAjaranAktif'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_nilai_id' => 'required|exists:jenis_nilais,id',
            'tp_id' => 'nullable|exists:tps,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $user->yayasan_id)
            ->where('is_active', true)
            ->first();

        if (!$tahunAjaranAktif) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $insertData = [];
        foreach ($validated['nilai'] as $siswaId => $nilai) {
            if ($nilai === null || $nilai === '') {
                continue;
            }

            // Update or create per siswa+tp
            $key = [
                'siswa_id' => $siswaId,
                'mapel_id' => $validated['mapel_id'],
                'guru_id' => $guru->id,
                'kelas_id' => $validated['kelas_id'],
                'tahun_ajaran_id' => $tahunAjaranAktif->id,
                'jenis_nilai_id' => $validated['jenis_nilai_id'],
                'tp_id' => $validated['tp_id'] ?? null,
            ];

            Nilai::updateOrCreate($key, [
                'nilai' => $nilai,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        }

        $jenisNilai = JenisNilai::find($validated['jenis_nilai_id']);
        LogAktivita::log('create', 'Input nilai ' . $jenisNilai->nama . ' mapel ' . Mapel::find($validated['mapel_id'])->nama);

        return redirect()->route('nilai.index', [
            'mapel_id' => $validated['mapel_id'],
            'kelas_id' => $validated['kelas_id'],
            'jenis_nilai_id' => $validated['jenis_nilai_id'],
        ])->with('success', 'Nilai berhasil disimpan.');
    }

    /**
     * Form edit: tampilkan kembali semua siswa + nilai existing.
     */
    public function edit(Request $request): View
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        $mapelId = $request->get('mapel_id');
        $kelasId = $request->get('kelas_id');
        $jenisNilaiId = $request->get('jenis_nilai_id');

        $mapel = Mapel::findOrFail($mapelId);
        $kelas = Kelas::findOrFail($kelasId);
        $jenisNilai = JenisNilai::findOrFail($jenisNilaiId);

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $user->yayasan_id)
            ->where('is_active', true)
            ->first();

        $siswas = [];
        if ($tahunAjaranAktif) {
            $siswas = RiwayatKelasSiswa::with('siswa')
                ->where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                ->whereNull('tanggal_keluar')
                ->get()
                ->pluck('siswa')
                ->filter();
        }

        $isHarian = strtolower($jenisNilai->nama) === 'harian';
        $tps = collect();
        if ($isHarian) {
            $tps = Tp::whereHas('cp', fn($q) => $q->where('mapel_id', $mapelId)->where('guru_id', $guru->id))
                ->with('cp')
                ->get();
        }

        // Untuk non-harian: tampilkan 1 kolom nilai per siswa
        $existingNilai = Nilai::where('guru_id', $guru->id)
            ->where('mapel_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->where('jenis_nilai_id', $jenisNilaiId)
            ->when($tahunAjaranAktif, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktif->id))
            ->get()
            ->keyBy(function ($n) {
                return $n->siswa_id . ($n->tp_id ? '_' . $n->tp_id : '');
            });

        return view('nilai.edit', compact(
            'mapel',
            'kelas',
            'jenisNilai',
            'siswas',
            'tps',
            'existingNilai',
            'isHarian',
            'tahunAjaranAktif'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_nilai_id' => 'required|exists:jenis_nilais,id',
            'tp_id' => 'nullable|exists:tps,id',
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $user->yayasan_id)
            ->where('is_active', true)
            ->first();

        // Update nilai yang ada, hapus yang dikosongkan
        foreach ($validated['nilai'] as $siswaId => $nilai) {
            $key = [
                'siswa_id' => $siswaId,
                'mapel_id' => $validated['mapel_id'],
                'guru_id' => $guru->id,
                'kelas_id' => $validated['kelas_id'],
                'tahun_ajaran_id' => $tahunAjaranAktif->id,
                'jenis_nilai_id' => $validated['jenis_nilai_id'],
                'tp_id' => $validated['tp_id'] ?? null,
            ];

            if ($nilai === null || $nilai === '') {
                Nilai::where($key)->delete();
            } else {
                Nilai::updateOrCreate($key, [
                    'nilai' => $nilai,
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);
            }
        }

        return redirect()->route('nilai.index', [
            'mapel_id' => $validated['mapel_id'],
            'kelas_id' => $validated['kelas_id'],
            'jenis_nilai_id' => $validated['jenis_nilai_id'],
        ])->with('success', 'Nilai berhasil diperbarui.');
    }

    /**
     * Finalisasi semua nilai untuk kombinasi mapel+kelas+jenis_nilai.
     */
    public function finalize(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_nilai_id' => 'required|exists:jenis_nilais,id',
        ]);

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $user->yayasan_id)
            ->where('is_active', true)
            ->first();

        Nilai::where('guru_id', $guru->id)
            ->where('mapel_id', $validated['mapel_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('jenis_nilai_id', $validated['jenis_nilai_id'])
            ->when($tahunAjaranAktif, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktif->id))
            ->update(['is_finalized' => true]);

        $jenisNilai = JenisNilai::find($validated['jenis_nilai_id']);
        LogAktivita::log('finalize', 'Finalisasi nilai ' . $jenisNilai->nama . ' mapel ' . Mapel::find($validated['mapel_id'])->nama);

        return back()->with('success', 'Nilai ' . $jenisNilai->nama . ' berhasil difinalisasi (dikunci).');
    }

    /**
     * Rekap nilai (Kurikulum / Kepala Lembaga).
     */
    public function rekap(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $user->yayasan_id)
            ->where('is_active', true)
            ->first();

        $query = Nilai::with(['siswa', 'mapel', 'kelas', 'jenisNilai', 'guru', 'tp'])
            ->whereHas('kelas', fn($q) => $q->where('lembaga_id', $lembagaId));

        if ($tahunAjaranAktif) {
            $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('jenis_nilai_id')) {
            $query->where('jenis_nilai_id', $request->jenis_nilai_id);
        }

        $nilais = $query->orderBy('mapel_id')->orderBy('kelas_id')->orderBy('siswa_id')->paginate(50);

        $mapels = Mapel::where('lembaga_id', $lembagaId)->get();
        $kelass = Kelas::where('lembaga_id', $lembagaId)->get();
        $jenisNilais = JenisNilai::where('lembaga_id', $lembagaId)->get();

        return view('nilai.rekap', compact('nilais', 'mapels', 'kelass', 'jenisNilais', 'tahunAjaranAktif'));
    }
}
