<?php

namespace App\Http\Controllers;

use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LembagaController extends Controller
{

    public function index(): View
    {
        $user = auth()->user();
        $query = Lembaga::with('yayasan')->withCount('gurus', 'siswas');

        if ($user->isAdminYayasan()) {
            $query->where('yayasan_id', $user->yayasan_id);
        }

        $lembagas = $query->latest()->paginate(10);
        $yayasans = Yayasan::where('is_active', true)->get();

        return view('lembaga.index', compact('lembagas', 'yayasans'));
    }

    public function create(): View
    {
        $yayasans = Yayasan::where('is_active', true)->get();
        return view('lembaga.create', compact('yayasans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $yayasanId = $user->isAdminYayasan() ? $user->yayasan_id : $request->yayasan_id;

        $validated = $request->validate([
            'yayasan_id' => $user->isAdminYayasan() ? 'nullable' : 'required|exists:yayasans,id',
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50',
            'npsn' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tingkat' => 'required|string|max:50',
            'unit_formal' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            // User admin lembaga
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        $request->validate([
            'kode' => 'unique:lembagas,kode,NULL,id,yayasan_id,' . $yayasanId,
        ]);

        $validated['yayasan_id'] = $yayasanId;
        $validated['is_active'] = $request->boolean('is_active');

        $lembaga = Lembaga::create($validated);

        // Auto-create admin user for lembaga
        User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => bcrypt($validated['admin_password']),
            'role' => 'admin_lembaga',
            'lembaga_id' => $lembaga->id,
            'yayasan_id' => $lembaga->yayasan_id,
            'is_active' => true,
        ]);

        LogAktivita::log('create', 'Menambah lembaga "' . $lembaga->nama . '"', $lembaga);

        return redirect()->route('lembaga.index')->with('success', 'Lembaga berhasil ditambahkan.');
    }

    public function edit(Lembaga $lembaga): View
    {
        $yayasans = Yayasan::where('is_active', true)->get();
        return view('lembaga.edit', compact('lembaga', 'yayasans'));
    }

    public function update(Request $request, Lembaga $lembaga): RedirectResponse
    {
        $user = auth()->user();
        $yayasanId = $user->isAdminYayasan() ? $user->yayasan_id : $request->yayasan_id;

        $validated = $request->validate([
            'yayasan_id' => $user->isAdminYayasan() ? 'nullable' : 'required|exists:yayasans,id',
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50',
            'npsn' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tingkat' => 'required|string|max:50',
            'unit_formal' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $request->validate([
            'kode' => 'unique:lembagas,kode,' . $lembaga->id . ',id,yayasan_id,' . $yayasanId,
        ]);

        $validated['yayasan_id'] = $yayasanId;
        $validated['is_active'] = $request->boolean('is_active');

        $lembaga->update($validated);

        LogAktivita::log('update', 'Mengupdate lembaga "' . $lembaga->nama . '"', $lembaga);

        return redirect()->route('lembaga.index')->with('success', 'Lembaga berhasil diperbarui.');
    }

    public function destroy(Lembaga $lembaga): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus lembaga "' . $lembaga->nama . '"', $lembaga);
        $lembaga->delete();
        return redirect()->route('lembaga.index')->with('success', 'Lembaga berhasil dihapus.');
    }
}
