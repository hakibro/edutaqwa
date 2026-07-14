<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JurusanController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $query = Jurusan::with('lembaga')->withCount('kelas');

        if ($user->lembaga_id) {
            $query->where('lembaga_id', $user->lembaga_id);
        } elseif ($user->yayasan_id) {
            $query->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        $jurusans = $query->latest()->paginate(10);

        return view('jurusan.index', compact('jurusans'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);

        return view('jurusan.create', compact('lembagas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nama' => 'required|string|max:100',
            'kode' => 'nullable|string|max:50',
        ]);

        $validated['lembaga_id'] = $lembagaId;

        $jurusan = Jurusan::create($validated);

        LogAktivita::log('create', 'Menambah jurusan "' . $jurusan->nama . '"', $jurusan);

        return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Jurusan $jurusan): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);

        return view('jurusan.edit', compact('jurusan', 'lembagas'));
    }

    public function update(Request $request, Jurusan $jurusan): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nama' => 'required|string|max:100',
            'kode' => 'nullable|string|max:50',
        ]);

        $validated['lembaga_id'] = $lembagaId;

        $jurusan->update($validated);

        LogAktivita::log('update', 'Mengupdate jurusan "' . $jurusan->nama . '"', $jurusan);

        return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus jurusan "' . $jurusan->nama . '"', $jurusan);
        $jurusan->delete();
        return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil dihapus.');
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
