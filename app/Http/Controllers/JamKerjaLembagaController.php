<?php

namespace App\Http\Controllers;

use App\Models\JamKerjaLembaga;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JamKerjaLembagaController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $lembaga = Lembaga::findOrFail($lembagaId);
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        $jamKerja = JamKerjaLembaga::where('lembaga_id', $lembagaId)
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->get()
            ->keyBy('hari');

        return view('jam-kerja.index', compact('jamKerja', 'hariList', 'lembaga'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $validated = $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i|after:jam_masuk',
            'toleransi_keterlambatan' => 'nullable|integer|min:0|max:120',
        ], [
            'jam_pulang.after' => 'Jam pulang harus setelah jam masuk.',
        ]);

        $validated['lembaga_id'] = $lembagaId;
        $validated['toleransi_keterlambatan'] = $validated['toleransi_keterlambatan'] ?? 15;

        // Check if already exists for this hari
        $exists = JamKerjaLembaga::where('lembaga_id', $lembagaId)
            ->where('hari', $validated['hari'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['hari' => 'Jam kerja untuk ' . $validated['hari'] . ' sudah ada. Silakan edit.']);
        }

        JamKerjaLembaga::create($validated);
        LogAktivita::log('create', 'Menambah jam kerja ' . $validated['hari']);

        return redirect()->route('jam-kerja.index')->with('success', 'Jam kerja berhasil ditambahkan.');
    }

    public function edit(JamKerjaLembaga $jamKerja): View
    {
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        return view('jam-kerja.edit', compact('jamKerja', 'hariList'));
    }

    public function update(Request $request, JamKerjaLembaga $jamKerja): RedirectResponse
    {
        $validated = $request->validate([
            'jam_masuk' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i|after:jam_masuk',
            'toleransi_keterlambatan' => 'nullable|integer|min:0|max:120',
            'is_active' => 'boolean',
        ], [
            'jam_pulang.after' => 'Jam pulang harus setelah jam masuk.',
        ]);

        $validated['toleransi_keterlambatan'] = $validated['toleransi_keterlambatan'] ?? $jamKerja->toleransi_keterlambatan;
        $validated['is_active'] = $request->boolean('is_active');

        $jamKerja->update($validated);
        LogAktivita::log('update', 'Mengubah jam kerja ' . $jamKerja->hari);

        return redirect()->route('jam-kerja.index')->with('success', 'Jam kerja berhasil diperbarui.');
    }

    public function destroy(JamKerjaLembaga $jamKerja): RedirectResponse
    {
        $hari = $jamKerja->hari;
        $jamKerja->delete();
        LogAktivita::log('delete', 'Menghapus jam kerja ' . $hari);

        return redirect()->route('jam-kerja.index')->with('success', 'Jam kerja ' . $hari . ' berhasil dihapus.');
    }

    /**
     * Update setting absensi lembaga (lokasi, radius, toggle selfie).
     */
    public function updateAbsenSettings(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembaga = Lembaga::findOrFail($user->lembaga_id);

        $validated = $request->validate([
            'lokasi_absen' => 'nullable|string|max:255',
            'latitude_absen' => 'nullable|numeric|between:-90,90',
            'longitude_absen' => 'nullable|numeric|between:-180,180',
            'radius_absen_meter' => 'nullable|integer|min:0|max:5000',
            'wajib_selfie' => 'boolean',
        ]);

        $validated['wajib_selfie'] = $request->boolean('wajib_selfie');
        $validated['radius_absen_meter'] = $validated['radius_absen_meter'] ?? 100;

        $lembaga->update($validated);
        LogAktivita::log('update', 'Mengupdate setting absensi lembaga');

        return redirect()->route('jam-kerja.index')->with('success', 'Setting absensi berhasil diperbarui.');
    }
}
