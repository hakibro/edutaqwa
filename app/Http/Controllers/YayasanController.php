<?php

namespace App\Http\Controllers;

use App\Models\LogAktivita;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class YayasanController extends Controller
{

    public function index(): View
    {
        $yayasans = Yayasan::withCount('lembagas')->latest()->paginate(10);
        return view('yayasan.index', compact('yayasans'));
    }

    public function create(): View
    {
        return view('yayasan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:yayasans,kode',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            // User admin yayasan
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        $yayasan = Yayasan::create($validated);

        // Auto-create admin yayasan user
        User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => bcrypt($validated['admin_password']),
            'role' => 'admin_yayasan',
            'yayasan_id' => $yayasan->id,
            'is_active' => true,
        ]);

        LogAktivita::log('create', 'Super admin menambah yayasan "' . $yayasan->nama . '"', $yayasan);

        return redirect()->route('yayasan.index')->with('success', 'Yayasan berhasil ditambahkan.');
    }

    public function edit(Yayasan $yayasan): View
    {
        return view('yayasan.edit', compact('yayasan'));
    }

    public function update(Request $request, Yayasan $yayasan): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:yayasans,kode,' . $yayasan->id,
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $yayasan->update($validated);

        LogAktivita::log('update', 'Super admin mengupdate yayasan "' . $yayasan->nama . '"', $yayasan);

        return redirect()->route('yayasan.index')->with('success', 'Yayasan berhasil diperbarui.');
    }

    public function destroy(Yayasan $yayasan): RedirectResponse
    {
        LogAktivita::log('delete', 'Super admin menghapus yayasan "' . $yayasan->nama . '"', $yayasan);
        $yayasan->delete();
        return redirect()->route('yayasan.index')->with('success', 'Yayasan berhasil dihapus.');
    }
}
