<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;

class KelasController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        $query = Kelas::with(['lembaga', 'jurusan'])->withCount('riwayatKelasSiswas as siswa_count');

        if ($user->lembaga_id) {
            $query->where('lembaga_id', $user->lembaga_id);
        } elseif ($user->yayasan_id) {
            $query->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        // Filter tingkat
        if ($tingkat = $request->input('tingkat')) {
            $query->where('tingkat', $tingkat);
        }

        // Filter jurusan
        if ($jurusanId = $request->input('jurusan_id')) {
            $query->where('jurusan_id', $jurusanId);
        }

        $perPage = $this->perPage($request, 10);
        $kelas = $query->latest()->paginate($perPage)->appends($request->except('page'));

        // Get filter options
        $tingkats = Kelas::select('tingkat')
            ->whereNotNull('tingkat')
            ->whereIn('lembaga_id', function ($q) use ($user) {
                if ($user->lembaga_id) {
                    $q->select('id')->from('lembagas')->where('id', $user->lembaga_id);
                } elseif ($user->yayasan_id) {
                    $q->select('id')->from('lembagas')->where('yayasan_id', $user->yayasan_id);
                } else {
                    $q->select('id')->from('lembagas')->where('is_active', true);
                }
            })
            ->distinct()->orderBy('tingkat')->pluck('tingkat');
        $jurusans = collect();
        if ($user->lembaga_id) {
            $jurusans = Jurusan::where('lembaga_id', $user->lembaga_id)->orderBy('nama')->get();
        } elseif ($user->yayasan_id) {
            $jurusans = Jurusan::whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id))->orderBy('nama')->get();
        } else {
            $jurusans = Jurusan::orderBy('nama')->get();
        }

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('kelas._table', compact('kelas'))->render();
            return response()->json(['html' => $html, 'pagination' => $kelas->links()->toHtml()]);
        }

        return view('kelas.index', compact('kelas', 'tingkats', 'jurusans'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);
        $jurusans = Jurusan::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))->get();

        return view('kelas.create', compact('lembagas', 'jurusans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'nama' => 'required|string|max:100',
            'tingkat' => 'required|string|max:20',
        ]);

        $validated['lembaga_id'] = $lembagaId;

        $kelas = Kelas::create($validated);

        LogAktivita::log('create', 'Menambah kelas "' . $kelas->nama . '"', $kelas);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);
        $jurusans = Jurusan::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))->get();

        return view('kelas.edit', compact('kelas', 'lembagas', 'jurusans'));
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'nama' => 'required|string|max:100',
            'tingkat' => 'required|string|max:20',
        ]);

        $validated['lembaga_id'] = $lembagaId;

        $kelas->update($validated);

        LogAktivita::log('update', 'Mengupdate kelas "' . $kelas->nama . '"', $kelas);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus kelas "' . $kelas->nama . '"', $kelas);
        $kelas->delete();
        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
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
