<?php

namespace App\Http\Controllers;

use App\Models\Ekskul;
use App\Models\Guru;
use App\Models\LogAktivita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;

class EkskulController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $ekskuls = Ekskul::with(['pembina', 'anggotaEkskuls'])
            ->where('lembaga_id', $lembagaId)
            ->when($request->filled('search'), fn($q) => $q->where('nama', 'like', '%' . $request->search . '%'))
            ->orderBy('nama')
            ->paginate($this->perPage($request));

        return view('kesiswaan.ekskul.index', compact('ekskuls'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $gurus = Guru::where('lembaga_id', $lembagaId)->where('is_active', true)->orderBy('nama')->get();

        return view('kesiswaan.ekskul.create', compact('gurus'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'pembina_id' => 'nullable|exists:gurus,id',
        ]);

        $validated['lembaga_id'] = $user->lembaga_id;
        $ekskul = Ekskul::create($validated);

        LogAktivita::log('create', 'Ekskul "' . $ekskul->nama . '" ditambahkan', $ekskul);

        return redirect()->route('kesiswaan.ekskul.index')->with('success', 'Ekskul ditambahkan.');
    }

    public function edit(Ekskul $ekskul): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $gurus = Guru::where('lembaga_id', $lembagaId)->where('is_active', true)->orderBy('nama')->get();

        return view('kesiswaan.ekskul.edit', compact('ekskul', 'gurus'));
    }

    public function update(Request $request, Ekskul $ekskul): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'pembina_id' => 'nullable|exists:gurus,id',
        ]);

        $ekskul->update($validated);

        LogAktivita::log('update', 'Ekskul "' . $ekskul->nama . '" diperbarui', $ekskul);

        return redirect()->route('kesiswaan.ekskul.index')->with('success', 'Ekskul diperbarui.');
    }

    public function destroy(Ekskul $ekskul): RedirectResponse
    {
        $nama = $ekskul->nama;
        $ekskul->delete();

        LogAktivita::log('delete', 'Ekskul "' . $nama . '" dihapus');

        return redirect()->route('kesiswaan.ekskul.index')->with('success', 'Ekskul dihapus.');
    }
}
