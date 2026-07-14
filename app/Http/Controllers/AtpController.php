<?php

namespace App\Http\Controllers;

use App\Models\Atp;
use App\Models\LogAktivita;
use App\Models\Tp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AtpController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Atp::with(['tp.cp.mapel', 'tp.cp.guru']);

        if ($user->isGuru()) {
            $query->whereHas('tp.cp', fn($q) => $q->where('guru_id', $user->guru_id));
        } else {
            if ($user->lembaga_id) {
                $query->whereHas('tp.cp.mapel', fn($q) => $q->where('lembaga_id', $user->lembaga_id));
            } elseif ($user->yayasan_id) {
                $query->whereHas('tp.cp.mapel.lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
            }
        }

        if ($request->filled('tp_id')) {
            $query->where('tp_id', $request->tp_id);
        }

        $atps = $query->orderBy('minggu_ke')->paginate(20);

        $tps = Tp::with('cp.mapel')
            ->when($user->isGuru(), fn($q) => $q->whereHas('cp', fn($qc) => $qc->where('guru_id', $user->guru_id)))
            ->get();

        return view('atp.index', compact('atps', 'tps'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $tps = Tp::with('cp.mapel')
            ->when($user->isGuru(), fn($q) => $q->whereHas('cp', fn($qc) => $qc->where('guru_id', $user->guru_id)))
            ->get();

        return view('atp.create', compact('tps'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tp_id' => 'required|exists:tps,id',
            'minggu_ke' => 'required|integer|min:1',
            'materi' => 'required|string',
        ]);

        Atp::create($validated);

        LogAktivita::log('create', 'Menambah ATP minggu ke-' . $validated['minggu_ke']);

        return redirect()->route('atp.index')->with('success', 'ATP berhasil ditambahkan.');
    }

    public function edit(Atp $atp): View
    {
        $user = auth()->user();
        $tps = Tp::with('cp.mapel')
            ->when($user->isGuru(), fn($q) => $q->whereHas('cp', fn($qc) => $qc->where('guru_id', $user->guru_id)))
            ->get();

        return view('atp.edit', compact('atp', 'tps'));
    }

    public function update(Request $request, Atp $atp): RedirectResponse
    {
        $validated = $request->validate([
            'tp_id' => 'required|exists:tps,id',
            'minggu_ke' => 'required|integer|min:1',
            'materi' => 'required|string',
        ]);

        $atp->update($validated);

        LogAktivita::log('update', 'Mengupdate ATP minggu ke-' . $validated['minggu_ke']);

        return redirect()->route('atp.index')->with('success', 'ATP berhasil diperbarui.');
    }

    public function destroy(Atp $atp): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus ATP minggu ke-' . $atp->minggu_ke);
        $atp->delete();

        return redirect()->route('atp.index')->with('success', 'ATP berhasil dihapus.');
    }
}
