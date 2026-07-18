<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\KategoriPelanggaran;
use App\Models\Kelas;
use App\Models\LogAktivita;
use App\Models\Pelanggaran;
use App\Models\RiwayatKelasSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;

class PelanggaranController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $pelanggarans = Pelanggaran::with(['siswa', 'kategoriPelanggaran', 'guru'])
            ->whereHas('siswa', fn($q) => $q->where('lembaga_id', $lembagaId))
            ->when($request->filled('search'), fn($q) => $q->whereHas('siswa', fn($sq) => $sq->where('nama', 'like', '%' . $request->search . '%')->orWhere('nis', 'like', '%' . $request->search . '%')))
            ->when($request->filled('kategori_id'), fn($q) => $q->where('kategori_pelanggaran_id', $request->kategori_id))
            ->when($request->filled('tanggal'), fn($q) => $q->where('tanggal', $request->tanggal))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate($this->perPage($request));

        $kategoris = KategoriPelanggaran::where('lembaga_id', $lembagaId)->orderBy('nama')->get();
        $siswas = Siswa::where('lembaga_id', $lembagaId)->where('is_active', true)->orderBy('nama')->get();

        return view('kesiswaan.pelanggaran.index', compact('pelanggarans', 'kategoris', 'siswas'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $kategoris = KategoriPelanggaran::where('lembaga_id', $lembagaId)->orderBy('nama')->get();
        $siswas = Siswa::where('lembaga_id', $lembagaId)->where('is_active', true)->orderBy('nama')->get();
        $gurus = Guru::where('lembaga_id', $lembagaId)->where('is_active', true)->orderBy('nama')->get();

        return view('kesiswaan.pelanggaran.create', compact('kategoris', 'siswas', 'gurus'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'kategori_pelanggaran_id' => 'required|exists:kategoris_pelanggarans,id',
            'guru_id' => 'required|exists:gurus,id',
            'deskripsi' => 'required|string|max:1000',
            'tanggal' => 'required|date',
            'tindakan' => 'nullable|string|max:1000',
        ]);

        $pelanggaran = Pelanggaran::create($validated);

        LogAktivita::log('create', 'Pelanggaran dicatat: ' . $pelanggaran->siswa->nama . ' — ' . $pelanggaran->kategoriPelanggaran->nama, $pelanggaran);

        return redirect()->route('kesiswaan.pelanggaran.index')->with('success', 'Pelanggaran berhasil dicatat.');
    }

    public function edit(Pelanggaran $pelanggaran): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $kategoris = KategoriPelanggaran::where('lembaga_id', $lembagaId)->orderBy('nama')->get();
        $siswas = Siswa::where('lembaga_id', $lembagaId)->where('is_active', true)->orderBy('nama')->get();
        $gurus = Guru::where('lembaga_id', $lembagaId)->where('is_active', true)->orderBy('nama')->get();

        return view('kesiswaan.pelanggaran.edit', compact('pelanggaran', 'kategoris', 'siswas', 'gurus'));
    }

    public function update(Request $request, Pelanggaran $pelanggaran): RedirectResponse
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'kategori_pelanggaran_id' => 'required|exists:kategoris_pelanggarans,id',
            'guru_id' => 'required|exists:gurus,id',
            'deskripsi' => 'required|string|max:1000',
            'tanggal' => 'required|date',
            'tindakan' => 'nullable|string|max:1000',
        ]);

        $pelanggaran->update($validated);

        LogAktivita::log('update', 'Pelanggaran diperbarui: ' . $pelanggaran->siswa->nama, $pelanggaran);

        return redirect()->route('kesiswaan.pelanggaran.index')->with('success', 'Pelanggaran diperbarui.');
    }

    public function destroy(Pelanggaran $pelanggaran): RedirectResponse
    {
        $nama = $pelanggaran->siswa->nama ?? 'unknown';
        $pelanggaran->delete();

        LogAktivita::log('delete', 'Pelanggaran dihapus: ' . $nama);

        return redirect()->route('kesiswaan.pelanggaran.index')->with('success', 'Pelanggaran dihapus.');
    }
}
