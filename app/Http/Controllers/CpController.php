<?php

namespace App\Http\Controllers;

use App\Models\Cp;
use App\Models\Guru;
use App\Models\LogAktivita;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CpController extends Controller
{
    /**
     * Hanya menampilkan CP milik guru yang login (guru).
     * Kurikulum & Kepala Lembaga: read-only semua CP.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        if ($user->isGuru()) {
            $guru = Guru::where('id', $user->guru_id)->first();
            $query = Cp::with(['mapel', 'guru'])->withCount('tps');
            if ($guru) {
                $query->where('guru_id', $guru->id);
            } else {
                $query->where('guru_id', -1); // tidak ada data
            }
        } else {
            $query = Cp::with(['mapel', 'guru'])->withCount('tps');
            if ($user->lembaga_id) {
                $query->whereHas('mapel', fn($q) => $q->where('lembaga_id', $user->lembaga_id));
            } elseif ($user->yayasan_id) {
                $query->whereHas('mapel.lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
            }
        }

        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        $cps = $query->latest()->paginate(10);

        $mapels = Mapel::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))
            ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)))
            ->get();

        return view('cp.index', compact('cps', 'mapels'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        // Guru hanya bisa memilih mapel yang diampu
        if ($user->isGuru() && $guru) {
            $mapelIds = $guru->pengajaranMapels()->pluck('mapel_id');
            $mapels = Mapel::whereIn('id', $mapelIds)->get();
        } else {
            $mapels = Mapel::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))
                ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)))
                ->get();
        }

        return view('cp.create', compact('mapels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'fase' => 'required|string|max:20',
            'kode' => 'nullable|string|max:50',
            'deskripsi' => 'required|string',
        ]);

        $validated['guru_id'] = $guru ? $guru->id : $request->guru_id;

        $cp = Cp::create($validated);

        LogAktivita::log('create', 'Menambah CP "' . $cp->kode . '"', $cp);

        return redirect()->route('cp.index')->with('success', 'CP berhasil ditambahkan.');
    }

    public function show(Cp $cp): View
    {
        $cp->load(['mapel', 'guru', 'tps.atps']);
        return view('cp.show', compact('cp'));
    }

    public function edit(Cp $cp): View
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        if ($user->isGuru() && $guru) {
            $mapelIds = $guru->pengajaranMapels()->pluck('mapel_id');
            $mapels = Mapel::whereIn('id', $mapelIds)->get();
        } else {
            $mapels = Mapel::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))
                ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)))
                ->get();
        }

        return view('cp.edit', compact('cp', 'mapels'));
    }

    public function update(Request $request, Cp $cp): RedirectResponse
    {
        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'fase' => 'required|string|max:20',
            'kode' => 'nullable|string|max:50',
            'deskripsi' => 'required|string',
        ]);

        $cp->update($validated);

        LogAktivita::log('update', 'Mengupdate CP "' . $cp->kode . '"', $cp);

        return redirect()->route('cp.index')->with('success', 'CP berhasil diperbarui.');
    }

    public function destroy(Cp $cp): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus CP "' . $cp->kode . '"', $cp);
        $cp->delete();

        return redirect()->route('cp.index')->with('success', 'CP berhasil dihapus.');
    }
}
