<?php

namespace App\Http\Controllers;

use App\Models\LogAktivita;
use App\Models\TahunAjaran;
use App\Models\Yayasan;
use App\Services\PerPageTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TahunAjaranController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = TahunAjaran::with('yayasan');

        if ($user->isAdminYayasan()) {
            $query->where('yayasan_id', $user->yayasan_id);
        }

        $tahunAjarans = $query->latest()->paginate($this->perPage($request));
        $yayasans = Yayasan::where('is_active', true)->get();

        return view('tahun-ajaran.index', compact('tahunAjarans', 'yayasans'));
    }

    public function create(): View
    {
        $yayasans = Yayasan::where('is_active', true)->get();
        return view('tahun-ajaran.create', compact('yayasans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $yayasanId = $user->isAdminYayasan() ? $user->yayasan_id : $request->yayasan_id;

        $validated = $request->validate([
            'yayasan_id' => $user->isAdminYayasan() ? 'nullable' : 'required|exists:yayasans,id',
            'nama' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'boolean',
        ]);

        $request->validate([
            'nama' => 'unique:tahun_ajarans,nama,NULL,id,yayasan_id,' . $yayasanId,
        ]);

        // If setting active, deactivate others in same yayasan
        if ($request->boolean('is_active')) {
            TahunAjaran::where('yayasan_id', $yayasanId)->update(['is_active' => false]);
        }

        $validated['yayasan_id'] = $yayasanId;
        $validated['is_active'] = $request->boolean('is_active');

        $ta = TahunAjaran::create($validated);

        LogAktivita::log('create', 'Menambah tahun ajaran "' . $ta->nama . '"', $ta);

        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(TahunAjaran $tahunAjaran): View
    {
        $yayasans = Yayasan::where('is_active', true)->get();
        return view('tahun-ajaran.edit', compact('tahunAjaran', 'yayasans'));
    }

    public function update(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'boolean',
        ]);

        $request->validate([
            'nama' => 'unique:tahun_ajarans,nama,' . $tahunAjaran->id . ',id,yayasan_id,' . $tahunAjaran->yayasan_id,
        ]);

        // If setting active, deactivate others in same yayasan
        if ($request->boolean('is_active') && !$tahunAjaran->is_active) {
            TahunAjaran::where('yayasan_id', $tahunAjaran->yayasan_id)
                ->where('id', '!=', $tahunAjaran->id)
                ->update(['is_active' => false]);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $tahunAjaran->update($validated);

        LogAktivita::log('update', 'Mengupdate tahun ajaran "' . $tahunAjaran->nama . ' ' . $tahunAjaran->semester . '"', $tahunAjaran);

        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus tahun ajaran "' . $tahunAjaran->nama . ' ' . $tahunAjaran->semester . '"', $tahunAjaran);
        $tahunAjaran->delete();
        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
