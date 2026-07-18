<?php

namespace App\Http\Controllers;

use App\Models\JenisPtk;
use App\Models\Lembaga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;

class JenisPtkController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $jenisPtks = JenisPtk::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))
            ->with('lembaga')
            ->latest()
            ->paginate($this->perPage($request));

        return view('jenis-ptk.index', compact('jenisPtks'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagas = $user->lembaga_id
            ? Lembaga::where('id', $user->lembaga_id)->get()
            : Lembaga::where('is_active', true)->get();

        return view('jenis-ptk.create', compact('lembagas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nama' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['lembaga_id'] = $lembagaId;
        $validated['is_active'] = $request->boolean('is_active', true);

        JenisPtk::create($validated);

        return redirect()->route('jenis-ptk.index')->with('success', 'Jenis PTK berhasil ditambahkan.');
    }

    public function edit(JenisPtk $jenisPtk): View
    {
        $this->authorizeLembagaAccess($jenisPtk->lembaga);
        return view('jenis-ptk.edit', compact('jenisPtk'));
    }

    public function update(Request $request, JenisPtk $jenisPtk): RedirectResponse
    {
        $this->authorizeLembagaAccess($jenisPtk->lembaga);

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $jenisPtk->update($validated);

        return redirect()->route('jenis-ptk.index')->with('success', 'Jenis PTK berhasil diperbarui.');
    }

    public function destroy(JenisPtk $jenisPtk): RedirectResponse
    {
        $this->authorizeLembagaAccess($jenisPtk->lembaga);
        $jenisPtk->delete();

        return redirect()->route('jenis-ptk.index')->with('success', 'Jenis PTK berhasil dihapus.');
    }

    protected function authorizeLembagaAccess(Lembaga $lembaga): void
    {
        $user = auth()->user();
        if ($user->lembaga_id && $user->lembaga_id !== $lembaga->id) {
            abort(403);
        }
        if ($user->yayasan_id && $user->yayasan_id !== $lembaga->yayasan_id) {
            abort(403);
        }
    }
}
