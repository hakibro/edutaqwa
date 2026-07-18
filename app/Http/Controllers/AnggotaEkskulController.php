<?php

namespace App\Http\Controllers;

use App\Models\AnggotaEkskul;
use App\Models\Ekskul;
use App\Models\LogAktivita;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;

class AnggotaEkskulController extends Controller
{
    use PerPageTrait;

    public function index(Ekskul $ekskul, Request $request): View
    {
        $anggota = AnggotaEkskul::with(['siswa', 'tahunAjaran'])
            ->where('ekskul_id', $ekskul->id)
            ->when($request->filled('search'), fn($q) => $q->whereHas('siswa', fn($sq) => $sq->where('nama', 'like', '%' . $request->search . '%')->orWhere('nis', 'like', '%' . $request->search . '%')))
            ->orderByDesc('id')
            ->paginate($this->perPage($request));

        return view('kesiswaan.anggota-ekskul.index', compact('ekskul', 'anggota'));
    }

    public function create(Ekskul $ekskul): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        // Get siswa already in this ekskul
        $existingIds = AnggotaEkskul::where('ekskul_id', $ekskul->id)->pluck('siswa_id');

        $siswas = Siswa::where('lembaga_id', $lembagaId)
            ->where('is_active', true)
            ->whereNotIn('id', $existingIds)
            ->orderBy('nama')
            ->get();

        $tahunAjarans = TahunAjaran::when($user->yayasan_id, fn($q) => $q->where('yayasan_id', $user->yayasan_id))->orderByDesc('tahun_mulai')->get();

        return view('kesiswaan.anggota-ekskul.create', compact('ekskul', 'siswas', 'tahunAjarans'));
    }

    public function store(Request $request, Ekskul $ekskul): RedirectResponse
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
        ]);

        // Check duplicate
        $exists = AnggotaEkskul::where('ekskul_id', $ekskul->id)
            ->where('siswa_id', $validated['siswa_id'])
            ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Siswa sudah terdaftar di ekskul ini untuk tahun ajaran yang dipilih.')->withInput();
        }

        $validated['ekskul_id'] = $ekskul->id;
        $anggota = AnggotaEkskul::create($validated);

        LogAktivita::log('create', 'Anggota ekskul "' . $ekskul->nama . '": ' . $anggota->siswa->nama, $anggota);

        return redirect()->route('kesiswaan.anggota-ekskul.index', $ekskul)->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function destroy(Ekskul $ekskul, AnggotaEkskul $anggota): RedirectResponse
    {
        $nama = $anggota->siswa->nama ?? 'unknown';
        $anggota->delete();

        LogAktivita::log('delete', 'Anggota ekskul "' . $ekskul->nama . '": ' . $nama . ' dikeluarkan');

        return redirect()->route('kesiswaan.anggota-ekskul.index', $ekskul)->with('success', 'Anggota berhasil dikeluarkan.');
    }
}
