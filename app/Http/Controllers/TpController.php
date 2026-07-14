<?php

namespace App\Http\Controllers;

use App\Models\Cp;
use App\Models\LogAktivita;
use App\Models\Tp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TpController extends Controller
{
    /**
     * Hanya menampilkan TP milik CP guru yang login.
     * Kurikulum & Kepala Lembaga: read-only.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Tp::with(['cp.mapel', 'cp.guru'])->withCount('atps');

        if ($user->isGuru()) {
            $query->whereHas('cp', fn($q) => $q->where('guru_id', $user->guru_id));
        } else {
            if ($user->lembaga_id) {
                $query->whereHas('cp.mapel', fn($q) => $q->where('lembaga_id', $user->lembaga_id));
            } elseif ($user->yayasan_id) {
                $query->whereHas('cp.mapel.lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
            }
        }

        if ($request->filled('cp_id')) {
            $query->where('cp_id', $request->cp_id);
        }

        $tps = $query->latest()->paginate(10);

        $cps = Cp::with('mapel')
            ->when($user->isGuru(), fn($q) => $q->where('guru_id', $user->guru_id))
            ->get();

        return view('tp.index', compact('tps', 'cps'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $cps = Cp::with('mapel')
            ->when($user->isGuru(), fn($q) => $q->where('guru_id', $user->guru_id))
            ->get();

        return view('tp.create', compact('cps'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cp_id' => 'required|exists:cps,id',
            'kode' => 'nullable|string|max:50',
            'deskripsi' => 'required|string',
        ]);

        $tp = Tp::create($validated);

        LogAktivita::log('create', 'Menambah TP "' . $tp->kode . '"', $tp);

        return redirect()->route('tp.index')->with('success', 'TP berhasil ditambahkan.');
    }

    public function show(Tp $tp): View
    {
        $tp->load(['cp.mapel', 'atps']);
        return view('tp.show', compact('tp'));
    }

    public function edit(Tp $tp): View
    {
        $user = auth()->user();
        $cps = Cp::with('mapel')
            ->when($user->isGuru(), fn($q) => $q->where('guru_id', $user->guru_id))
            ->get();

        return view('tp.edit', compact('tp', 'cps'));
    }

    public function update(Request $request, Tp $tp): RedirectResponse
    {
        $validated = $request->validate([
            'cp_id' => 'required|exists:cps,id',
            'kode' => 'nullable|string|max:50',
            'deskripsi' => 'required|string',
        ]);

        $tp->update($validated);

        LogAktivita::log('update', 'Mengupdate TP "' . $tp->kode . '"', $tp);

        return redirect()->route('tp.index')->with('success', 'TP berhasil diperbarui.');
    }

    public function destroy(Tp $tp): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus TP "' . $tp->kode . '"', $tp);
        $tp->delete();

        return redirect()->route('tp.index')->with('success', 'TP berhasil dihapus.');
    }
}
