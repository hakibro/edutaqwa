<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\PerizinanSiswa;
use App\Models\RiwayatKelasSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\PerPageTrait;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PerizinanSiswaController extends Controller
{
    use PerPageTrait;

    /**
     * Daftar perizinan — hanya untuk guru dengan permission perizinan_siswa.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $guru = Guru::findOrFail($user->guru_id);

        // Cek permission
        if (!$guru->hasPermission('perizinan_siswa')) {
            abort(403, 'Anda tidak memiliki akses perizinan siswa.');
        }

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $guru->lembaga->yayasan_id)
            ->where('is_active', true)
            ->first();

        $perizinans = PerizinanSiswa::with(['siswa', 'kelas', 'validator'])
            ->where('lembaga_id', $lembagaId)
            ->when($request->filled('kelas_id'), fn($q) => $q->where('kelas_id', $request->kelas_id))
            ->when($request->filled('tanggal'), fn($q) => $q->where('tanggal', $request->tanggal))
            ->when($request->filled('jenis'), fn($q) => $q->where('jenis', $request->jenis))
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->paginate($this->perPage($request));

        $kelasList = Kelas::where('lembaga_id', $lembagaId)->orderBy('nama')->get();

        return view('perizinan.index', compact('perizinans', 'kelasList'));
    }

    /**
     * Form input perizinan.
     */
    public function create(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $guru = Guru::findOrFail($user->guru_id);

        if (!$guru->hasPermission('perizinan_siswa')) {
            abort(403, 'Anda tidak memiliki akses perizinan siswa.');
        }

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $guru->lembaga->yayasan_id)
            ->where('is_active', true)
            ->first();

        $kelasList = Kelas::where('lembaga_id', $lembagaId)->orderBy('nama')->get();

        // Jika ada kelas_id, load siswa dari kelas tersebut
        $siswaList = collect();
        $selectedKelas = null;
        if ($request->filled('kelas_id')) {
            $selectedKelas = Kelas::find($request->kelas_id);
            if ($selectedKelas && $tahunAjaranAktif) {
                $siswaList = Siswa::where('lembaga_id', $lembagaId)
                    ->where('is_active', true)
                    ->whereHas(
                        'riwayatKelasSiswas',
                        fn($q) => $q
                            ->where('kelas_id', $request->kelas_id)
                            ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                            ->whereNull('tanggal_keluar')
                    )
                    ->orderBy('nama')
                    ->get();
            }
        }

        return view('perizinan.create', compact('kelasList', 'siswaList', 'selectedKelas'));
    }

    /**
     * Simpan perizinan.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $guru = Guru::findOrFail($user->guru_id);

        if (!$guru->hasPermission('perizinan_siswa')) {
            abort(403, 'Anda tidak memiliki akses perizinan siswa.');
        }

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal' => 'required|date',
            'jenis' => 'required|in:sakit,izin',
            'keterangan' => 'nullable|string|max:255',
            'lampiran' => 'required|string',
        ], [
            'lampiran.required' => 'Lampiran bukti perizinan wajib diisi (paste gambar).',
        ]);

        // Proses lampiran: base64 image → simpan ke storage
        $lampiranPath = null;
        if (!empty($validated['lampiran'])) {
            $lampiranPath = $this->simpanLampiran($validated['lampiran'], $lembagaId, $validated['siswa_id'], $validated['tanggal']);
        }

        // Validasi siswa benar di kelas tersebut
        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $guru->lembaga->yayasan_id)
            ->where('is_active', true)
            ->first();

        $siswaDiKelas = RiwayatKelasSiswa::where('siswa_id', $validated['siswa_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
            ->whereNull('tanggal_keluar')
            ->exists();

        if (!$siswaDiKelas) {
            return back()->withInput()->with('error', 'Siswa tidak ditemukan di kelas ini.');
        }

        $perizinan = PerizinanSiswa::updateOrCreate(
            [
                'siswa_id' => $validated['siswa_id'],
                'tanggal' => $validated['tanggal'],
            ],
            [
                'lembaga_id' => $lembagaId,
                'kelas_id' => $validated['kelas_id'],
                'validator_id' => $user->id,
                'jenis' => $validated['jenis'],
                'keterangan' => $validated['keterangan'],
                'lampiran' => $lampiranPath,
            ]
        );

        // Auto-apply ke jurnal yang sudah ada
        $perizinan->applyToJurnal();

        // Notifikasi ke wali kelas (jika ada)
        $waliKelas = Guru::whereHas(
            'tugasTambahans',
            fn($q) => $q
                ->where('jenis', 'Wali Kelas')
                ->where('kelas_id', $validated['kelas_id'])
                ->where('is_active', true)
                ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
        )
            ->whereHas('user')
            ->first();

        if ($waliKelas && $waliKelas->user) {
            $siswa = Siswa::find($validated['siswa_id']);
            \App\Http\Controllers\NotifikasiController::kirim(
                $waliKelas->user->id,
                'Siswa ' . ucfirst($validated['jenis']),
                $siswa->nama . ' - ' . ucfirst($validated['jenis']) . ' pada ' . Carbon::parse($validated['tanggal'])->format('d/m/Y'),
                'info',
                route('guru.wali-kelas')
            );
        }

        return redirect()->route('perizinan.index')
            ->with('success', 'Perizinan siswa berhasil disimpan.');
    }

    /**
     * Form edit perizinan.
     */
    public function edit(PerizinanSiswa $perizinan): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $guru = Guru::findOrFail($user->guru_id);

        if (!$guru->hasPermission('perizinan_siswa')) {
            abort(403, 'Anda tidak memiliki akses perizinan siswa.');
        }

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $guru->lembaga->yayasan_id)
            ->where('is_active', true)
            ->first();

        $kelasList = Kelas::where('lembaga_id', $lembagaId)->orderBy('nama')->get();

        $siswaList = Siswa::where('lembaga_id', $lembagaId)
            ->where('is_active', true)
            ->whereHas(
                'riwayatKelasSiswas',
                fn($q) => $q
                    ->where('kelas_id', $perizinan->kelas_id)
                    ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                    ->whereNull('tanggal_keluar')
            )
            ->orderBy('nama')
            ->get();

        $selectedKelas = $perizinan->kelas;

        return view('perizinan.edit', compact('perizinan', 'kelasList', 'siswaList', 'selectedKelas'));
    }

    /**
     * Update perizinan.
     */
    public function update(Request $request, PerizinanSiswa $perizinan): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $guru = Guru::findOrFail($user->guru_id);

        if (!$guru->hasPermission('perizinan_siswa')) {
            abort(403, 'Anda tidak memiliki akses perizinan siswa.');
        }

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal' => 'required|date',
            'jenis' => 'required|in:sakit,izin',
            'keterangan' => 'nullable|string|max:255',
            'lampiran' => 'nullable|string',
        ]);

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $guru->lembaga->yayasan_id)
            ->where('is_active', true)
            ->first();

        $siswaDiKelas = RiwayatKelasSiswa::where('siswa_id', $validated['siswa_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
            ->whereNull('tanggal_keluar')
            ->exists();

        if (!$siswaDiKelas) {
            return back()->withInput()->with('error', 'Siswa tidak ditemukan di kelas ini.');
        }

        // Lampiran: jika ada base64 baru, simpan; jika tidak, pertahankan lama
        if (!empty($validated['lampiran']) && str_starts_with($validated['lampiran'], 'data:image')) {
            if ($perizinan->lampiran) {
                Storage::disk('public')->delete($perizinan->lampiran);
            }
            $validated['lampiran'] = $this->simpanLampiran($validated['lampiran'], $lembagaId, $validated['siswa_id'], $validated['tanggal']);
        } else {
            $validated['lampiran'] = $perizinan->lampiran;
        }

        $perizinan->update([
            'kelas_id' => $validated['kelas_id'],
            'siswa_id' => $validated['siswa_id'],
            'tanggal' => $validated['tanggal'],
            'jenis' => $validated['jenis'],
            'keterangan' => $validated['keterangan'],
            'lampiran' => $validated['lampiran'],
            'validator_id' => $user->id,
            'is_applied' => false,
            'applied_at' => null,
        ]);

        $perizinan->applyToJurnal();

        return redirect()->route('perizinan.index')
            ->with('success', 'Perizinan siswa berhasil diperbarui.');
    }

    /**
     * Hapus perizinan.
     */
    public function destroy(PerizinanSiswa $perizinan): RedirectResponse
    {
        $user = auth()->user();
        $guru = Guru::findOrFail($user->guru_id);

        if (!$guru->hasPermission('perizinan_siswa')) {
            abort(403, 'Anda tidak memiliki akses perizinan siswa.');
        }

        // Balikkan status di detail_jurnal_siswas jadi alpha
        \App\Models\DetailJurnalSiswa::where('siswa_id', $perizinan->siswa_id)
            ->whereHas('jurnalMengajar', fn($q) => $q->where('tanggal', $perizinan->tanggal))
            ->update([
                'status' => 'alpha',
                'keterangan' => 'Perizinan dihapus',
            ]);

        $perizinan->delete();

        return redirect()->route('perizinan.index')
            ->with('success', 'Perizinan siswa berhasil dihapus.');
    }

    /**
     * API: get siswa by kelas (untuk AJAX).
     */
    public function getSiswaByKelas(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        $guru = Guru::findOrFail($user->guru_id);

        if (!$guru->hasPermission('perizinan_siswa')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate(['kelas_id' => 'required|exists:kelas,id']);

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $guru->lembaga->yayasan_id)
            ->where('is_active', true)
            ->first();

        $siswaList = Siswa::where('lembaga_id', $user->lembaga_id)
            ->where('is_active', true)
            ->whereHas(
                'riwayatKelasSiswas',
                fn($q) => $q
                    ->where('kelas_id', $request->kelas_id)
                    ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                    ->whereNull('tanggal_keluar')
            )
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis', 'nisn']);

        return response()->json($siswaList);
    }

    /**
     * Simpan base64 image ke storage, return path.
     */
    private function simpanLampiran(string $base64, int $lembagaId, int $siswaId, string $tanggal): string
    {
        // Decode base64: bisa "data:image/png;base64,..." atau raw base64
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            $ext = $matches[1];
            $base64 = substr($base64, strpos($base64, ',') + 1);
        } else {
            $ext = 'png';
        }

        $imageData = base64_decode($base64);
        if ($imageData === false) {
            throw new \InvalidArgumentException('Data lampiran tidak valid.');
        }

        $filename = sprintf(
            'perizinan/%d/%d_%s_%s.%s',
            $lembagaId,
            $siswaId,
            $tanggal,
            date('His'),
            $ext
        );

        Storage::disk('public')->put($filename, $imageData);

        return $filename;
    }
}
