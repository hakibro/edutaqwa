<?php

namespace App\Http\Controllers;

use App\Models\KategoriPelanggaran;
use App\Models\LogAktivita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;

class KategoriPelanggaranController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $kategoris = KategoriPelanggaran::where('lembaga_id', $lembagaId)
            ->when($request->filled('search'), fn($q) => $q->where('nama', 'like', '%' . $request->search . '%'))
            ->orderBy('nama')
            ->paginate($this->perPage($request));

        return view('kesiswaan.kategori-pelanggaran.index', compact('kategoris'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'poin' => 'required|integer|min:1|max:100',
        ]);

        $validated['lembaga_id'] = $user->lembaga_id;
        $kategori = KategoriPelanggaran::create($validated);

        LogAktivita::log('create', 'Kategori pelanggaran "' . $kategori->nama . '" (' . $kategori->poin . ' poin)', $kategori);

        return redirect()->route('kesiswaan.kategori-pelanggaran.index')->with('success', 'Kategori pelanggaran ditambahkan.');
    }

    public function update(Request $request, KategoriPelanggaran $kategoriPelanggaran): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'poin' => 'required|integer|min:1|max:100',
        ]);

        $kategoriPelanggaran->update($validated);

        LogAktivita::log('update', 'Kategori pelanggaran "' . $kategoriPelanggaran->nama . '" (' . $kategoriPelanggaran->poin . ' poin)', $kategoriPelanggaran);

        return redirect()->route('kesiswaan.kategori-pelanggaran.index')->with('success', 'Kategori pelanggaran diperbarui.');
    }

    public function destroy(KategoriPelanggaran $kategoriPelanggaran): RedirectResponse
    {
        $nama = $kategoriPelanggaran->nama;
        $kategoriPelanggaran->delete();

        LogAktivita::log('delete', 'Kategori pelanggaran "' . $nama . '" dihapus');

        return redirect()->route('kesiswaan.kategori-pelanggaran.index')->with('success', 'Kategori pelanggaran dihapus.');
    }
}
