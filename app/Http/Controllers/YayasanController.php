<?php

namespace App\Http\Controllers;

use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Models\User;
use App\Models\Yayasan;
use App\Services\PerPageTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class YayasanController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View
    {
        $yayasans = Yayasan::withCount('lembagas')->latest()->paginate($this->perPage($request));
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

    public function importLembaga(Yayasan $yayasan): RedirectResponse
    {
        $url = 'https://apiakademik.daruttaqwa.or.id/api/lembaga';

        try {
            $response = Http::timeout(30)->get($url);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghubungi API Akademik: ' . $e->getMessage());
        }

        if (!$response->successful()) {
            return back()->with('error', 'API Akademik merespon dengan status ' . $response->status());
        }

        $data = $response->json();
        $allLembaga = $data['data'] ?? [];

        $mapTingkat = [
            'PAUD' => 'PAUD',
            'RA' => 'RA',
            'MI' => 'MI',
            'MTS' => 'MTS',
            'MA' => 'MA',
            'SD' => 'SD',
            'SMP' => 'SMP',
            'SMA' => 'SMA',
            'SMK' => 'SMK',
        ];

        $imported = 0;
        $skipped = 0;

        foreach ($allLembaga as $item) {
            $kodeSisda = $item['idunit'] ?? null;
            $kode = $item['kode'] ?? null;
            $nama = $item['nama'] ?? null;

            if (!$kodeSisda || !$kode || !$nama)
                continue;

            // Cek apakah sudah ada di yayasan ini (by kode_sisda)
            $exists = Lembaga::where('yayasan_id', $yayasan->id)
                ->where('kode_sisda', $kodeSisda)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $tingkat = isset($mapTingkat[$kode]) ? $mapTingkat[$kode] : 'MADIN';
            $unitFormal = isset($mapTingkat[$kode]) ? $kode : null;

            Lembaga::create([
                'yayasan_id' => $yayasan->id,
                'nama' => $nama,
                'kode' => $kode,
                'kode_sisda' => $kodeSisda,
                'tingkat' => $tingkat,
                'unit_formal' => $unitFormal,
                'is_active' => true,
                'sisda_mode' => true,
            ]);

            $imported++;
        }

        LogAktivita::log('import', "Import {$imported} lembaga dari API Akademik ke yayasan \"{$yayasan->nama}\" ({$skipped} dilewati)", $yayasan);

        $msg = "Berhasil import {$imported} lembaga dari API Akademik.";
        if ($skipped > 0)
            $msg .= " {$skipped} sudah ada dan dilewati.";

        return redirect()->route('yayasan.edit', $yayasan)->with('success', $msg);
    }
}
