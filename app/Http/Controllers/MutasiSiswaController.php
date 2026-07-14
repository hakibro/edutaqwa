<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Models\RiwayatKelasSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MutasiSiswaController extends Controller
{
    /**
     * Form pindah masuk.
     */
    public function createMasuk(): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);
        $kelasList = Kelas::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))->get();
        $tahunAjarans = TahunAjaran::when($user->yayasan_id, fn($q) => $q->where('yayasan_id', $user->yayasan_id))->get();

        return view('siswa.mutasi-masuk', compact('lembagas', 'kelasList', 'tahunAjarans'));
    }

    /**
     * Proses pindah masuk: daftarkan siswa baru & assign ke kelas.
     */
    public function storeMasuk(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nis' => 'required|string|max:50',
            'nisn' => 'nullable|string|max:20',
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'kelas_id' => 'required|exists:kelas,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'asal_sekolah' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $request->validate([
            'nis' => 'unique:siswas,nis,NULL,id,lembaga_id,' . $lembagaId,
        ]);

        $validated['lembaga_id'] = $lembagaId;
        $validated['status'] = 'aktif';
        $validated['is_active'] = true;

        $siswa = Siswa::create($validated);

        // Assign ke kelas
        RiwayatKelasSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $validated['kelas_id'],
            'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
            'tanggal_masuk' => now()->toDateString(),
        ]);

        $keterangan = $validated['keterangan'] ?? '';
        if ($validated['asal_sekolah']) {
            $keterangan = 'Dari: ' . $validated['asal_sekolah'] . ($keterangan ? ' — ' . $keterangan : '');
        }

        LogAktivita::log('create', 'Mutasi masuk siswa "' . $siswa->nama . '" (NIS: ' . $siswa->nis . ') ' . $keterangan, $siswa);

        return redirect()->route('siswa.index')->with('success', 'Siswa pindah masuk berhasil didaftarkan.');
    }

    /**
     * Form pindah keluar / alumni.
     */
    public function editStatus(Siswa $siswa): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);

        return view('siswa.mutasi-keluar', compact('siswa', 'lembagas'));
    }

    /**
     * Proses pindah keluar / alumni.
     */
    public function updateStatus(Request $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:aktif,alumni,pindah,keluar,dropout',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $oldStatus = $siswa->status;
        $siswa->update(['status' => $validated['status'], 'is_active' => $validated['status'] === 'aktif']);

        // Jika pindah/keluar, tutup riwayat kelas aktif
        if (in_array($validated['status'], ['pindah', 'keluar', 'dropout', 'alumni'])) {
            RiwayatKelasSiswa::where('siswa_id', $siswa->id)
                ->whereNull('tanggal_keluar')
                ->update(['tanggal_keluar' => now()->toDateString()]);
        }

        $label = match ($validated['status']) {
            'alumni' => 'alumni',
            'pindah' => 'pindah keluar',
            'keluar' => 'keluar',
            'dropout' => 'drop out',
            default => 'aktif kembali',
        };

        $msg = $validated['keterangan'] ? " — {$validated['keterangan']}" : '';
        LogAktivita::log('update', "Siswa \"{$siswa->nama}\" {$label} ({$oldStatus} → {$validated['status']}){$msg}", $siswa);

        return redirect()->route('siswa.index')->with('success', "Status siswa berhasil diubah menjadi {$label}.");
    }

    /**
     * Daftar alumni.
     */
    public function alumni(): View
    {
        $user = auth()->user();
        $query = Siswa::with('lembaga')->where('status', 'alumni');

        if ($user->lembaga_id) {
            $query->where('lembaga_id', $user->lembaga_id);
        } elseif ($user->yayasan_id) {
            $query->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        $siswas = $query->latest()->paginate(10);

        return view('siswa.alumni', compact('siswas'));
    }

    protected function getLembagas($user)
    {
        if ($user->lembaga_id) {
            return Lembaga::where('id', $user->lembaga_id)->get();
        }
        if ($user->yayasan_id) {
            return Lembaga::where('yayasan_id', $user->yayasan_id)->get();
        }
        return Lembaga::where('is_active', true)->get();
    }
}
