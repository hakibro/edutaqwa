<?php

namespace App\Http\Controllers;

use App\Models\KalenderAkademik;
use App\Models\LogAktivita;
use App\Models\Yayasan;
use App\Services\PerPageTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KalenderAkademikController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View
    {
        $user = auth()->user();
        $yayasanId = $user->isAdminYayasan() ? $user->yayasan_id : $request->get('yayasan_id');

        $yayasans = Yayasan::where('is_active', true)->get();

        $kalenders = KalenderAkademik::with('yayasan')
            ->when($yayasanId, fn($q) => $q->where('yayasan_id', $yayasanId))
            ->orderBy('tanggal')
            ->paginate($this->perPage($request));

        return view('kalender-akademik.index', compact('kalenders', 'yayasans', 'yayasanId'));
    }

    public function create(Request $request): View
    {
        $user = auth()->user();
        $yayasanId = $user->isAdminYayasan() ? $user->yayasan_id : $request->get('yayasan_id');
        $yayasans = Yayasan::where('is_active', true)->get();

        return view('kalender-akademik.create', compact('yayasans', 'yayasanId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $yayasanId = $user->isAdminYayasan() ? $user->yayasan_id : $request->yayasan_id;

        $validated = $request->validate([
            'yayasan_id' => $user->isAdminYayasan() ? 'nullable' : 'required|exists:yayasans,id',
            'tanggal' => 'required|date',
            'label' => 'required|string|max:255',
            'jenis' => 'required|in:efektif,libur,ujian,lainnya',
            'keterangan' => 'nullable|string',
        ]);

        $request->validate([
            'tanggal' => 'unique:kalender_akademiks,tanggal,NULL,id,yayasan_id,' . $yayasanId,
        ]);

        $validated['yayasan_id'] = $yayasanId;

        $k = KalenderAkademik::create($validated);

        LogAktivita::log('create', 'Menambah kalender akademik "' . $k->label . '" (' . $k->tanggal->format('d/m/Y') . ')', $k);

        return redirect()->route('kalender-akademik.index', ['yayasan_id' => $yayasanId])
            ->with('success', 'Kalender akademik berhasil ditambahkan.');
    }

    public function edit(KalenderAkademik $kalenderAkademik): View
    {
        $yayasans = Yayasan::where('is_active', true)->get();
        return view('kalender-akademik.edit', compact('kalenderAkademik', 'yayasans'));
    }

    public function update(Request $request, KalenderAkademik $kalenderAkademik): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'label' => 'required|string|max:255',
            'jenis' => 'required|in:efektif,libur,ujian,lainnya',
            'keterangan' => 'nullable|string',
        ]);

        $request->validate([
            'tanggal' => 'unique:kalender_akademiks,tanggal,' . $kalenderAkademik->id . ',id,yayasan_id,' . $kalenderAkademik->yayasan_id,
        ]);

        $kalenderAkademik->update($validated);

        LogAktivita::log('update', 'Mengupdate kalender akademik "' . $kalenderAkademik->label . '"', $kalenderAkademik);

        return redirect()->route('kalender-akademik.index', ['yayasan_id' => $kalenderAkademik->yayasan_id])
            ->with('success', 'Kalender akademik berhasil diperbarui.');
    }

    public function destroy(KalenderAkademik $kalenderAkademik): RedirectResponse
    {
        $yayasanId = $kalenderAkademik->yayasan_id;
        LogAktivita::log('delete', 'Menghapus kalender akademik "' . $kalenderAkademik->label . '"', $kalenderAkademik);
        $kalenderAkademik->delete();

        return redirect()->route('kalender-akademik.index', ['yayasan_id' => $yayasanId])
            ->with('success', 'Kalender akademik berhasil dihapus.');
    }
}
