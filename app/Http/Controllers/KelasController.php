<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $query = Kelas::with(['lembaga', 'jurusan'])->withCount('riwayatKelasSiswas as siswa_count');

        if ($user->lembaga_id) {
            $query->where('lembaga_id', $user->lembaga_id);
        } elseif ($user->yayasan_id) {
            $query->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        $kelas = $query->latest()->paginate(10);

        return view('kelas.index', compact('kelas'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);
        $jurusans = Jurusan::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))->get();

        return view('kelas.create', compact('lembagas', 'jurusans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'nama' => 'required|string|max:100',
            'tingkat' => 'required|string|max:20',
        ]);

        $validated['lembaga_id'] = $lembagaId;

        $kelas = Kelas::create($validated);

        LogAktivita::log('create', 'Menambah kelas "' . $kelas->nama . '"', $kelas);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);
        $jurusans = Jurusan::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))->get();

        return view('kelas.edit', compact('kelas', 'lembagas', 'jurusans'));
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'nama' => 'required|string|max:100',
            'tingkat' => 'required|string|max:20',
        ]);

        $validated['lembaga_id'] = $lembagaId;

        $kelas->update($validated);

        LogAktivita::log('update', 'Mengupdate kelas "' . $kelas->nama . '"', $kelas);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus kelas "' . $kelas->nama . '"', $kelas);
        $kelas->delete();
        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
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
