<?php

namespace App\Http\Controllers;

use App\Models\KelompokMapel;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;

class KelompokMapelController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = KelompokMapel::with('lembaga')->withCount('mapels');

        if ($user->lembaga_id) {
            $query->where('lembaga_id', $user->lembaga_id);
        } elseif ($user->yayasan_id) {
            $query->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        $kelompokMapels = $query->latest()->paginate($this->perPage($request));

        return view('kelompok-mapel.index', compact('kelompokMapels'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);

        return view('kelompok-mapel.create', compact('lembagas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nama' => 'required|string|max:255',
        ]);

        $validated['lembaga_id'] = $lembagaId;

        $kelompokMapel = KelompokMapel::create($validated);

        LogAktivita::log('create', 'Menambah kelompok mapel "' . $kelompokMapel->nama . '"', $kelompokMapel);

        return redirect()->route('kelompok-mapel.index')->with('success', 'Kelompok mapel berhasil ditambahkan.');
    }

    public function edit(KelompokMapel $kelompokMapel): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);

        return view('kelompok-mapel.edit', compact('kelompokMapel', 'lembagas'));
    }

    public function update(Request $request, KelompokMapel $kelompokMapel): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nama' => 'required|string|max:255',
        ]);

        $validated['lembaga_id'] = $lembagaId;

        $kelompokMapel->update($validated);

        LogAktivita::log('update', 'Mengupdate kelompok mapel "' . $kelompokMapel->nama . '"', $kelompokMapel);

        return redirect()->route('kelompok-mapel.index')->with('success', 'Kelompok mapel berhasil diperbarui.');
    }

    public function destroy(KelompokMapel $kelompokMapel): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus kelompok mapel "' . $kelompokMapel->nama . '"', $kelompokMapel);
        $kelompokMapel->delete();

        return redirect()->route('kelompok-mapel.index')->with('success', 'Kelompok mapel berhasil dihapus.');
    }

    protected function getLembagas($user)
    {
        if ($user->lembaga_id) {
            return Lembaga::where('id', $user->lembaga_id)->get();
        }
        return Lembaga::where('yayasan_id', $user->yayasan_id)->get();
    }
}
