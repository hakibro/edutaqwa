<?php

namespace App\Http\Controllers;

use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiswaController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $query = Siswa::with('lembaga');

        if ($user->lembaga_id) {
            $query->where('lembaga_id', $user->lembaga_id);
        } elseif ($user->yayasan_id) {
            $query->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        $siswas = $query->latest()->paginate(10);

        return view('siswa.index', compact('siswas'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);

        return view('siswa.create', compact('lembagas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nis' => 'required|string|max:50',
            'nisn' => 'nullable|string|max:20',
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'agama' => 'nullable|string|max:50',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'telp_orang_tua' => 'nullable|string|max:50',
            'status' => 'nullable|in:aktif,alumni,pindah,keluar,dropout',
            'is_active' => 'boolean',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Cek unique nis per lembaga
        $request->validate([
            'nis' => 'unique:siswas,nis,NULL,id,lembaga_id,' . $lembagaId,
        ]);

        $validated['lembaga_id'] = $lembagaId;
        $validated['is_active'] = $request->boolean('is_active');

        // Upload foto
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('siswa/foto', 'public');
        }

        $siswa = Siswa::create($validated);

        LogAktivita::log('create', 'Menambah siswa "' . $siswa->nama . '" (NIS: ' . $siswa->nis . ')', $siswa);

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);

        return view('siswa.edit', compact('siswa', 'lembagas'));
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nis' => 'required|string|max:50',
            'nisn' => 'nullable|string|max:20',
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'agama' => 'nullable|string|max:50',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'telp_orang_tua' => 'nullable|string|max:50',
            'status' => 'nullable|in:aktif,alumni,pindah,keluar,dropout',
            'is_active' => 'boolean',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $request->validate([
            'nis' => 'unique:siswas,nis,' . $siswa->id . ',id,lembaga_id,' . $lembagaId,
        ]);

        $validated['lembaga_id'] = $lembagaId;
        $validated['is_active'] = $request->boolean('is_active');

        // Upload foto
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($siswa->foto && \Storage::disk('public')->exists($siswa->foto)) {
                \Storage::disk('public')->delete($siswa->foto);
            }
            $validated['foto'] = $request->file('foto')->store('siswa/foto', 'public');
        }

        $siswa->update($validated);

        LogAktivita::log('update', 'Mengupdate siswa "' . $siswa->nama . '"', $siswa);

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus siswa "' . $siswa->nama . '"', $siswa);
        $siswa->delete();
        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil dihapus.');
    }

    protected function getLembagas($user)
    {
        if ($user->lembaga_id) {
            return Lembaga::where('id', $user->lembaga_id)->get();
        }
        if ($user->yayasan_id) {
            return Lembaga::where('yayasan_id', $user->yayasan_id)->get();
        }
        return Lembaga::where('is_active', true)->get();
    }
}
