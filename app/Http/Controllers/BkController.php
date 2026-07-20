<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BkController extends Controller
{
    /**
     * Dashboard BK — lihat pelanggaran siswa & pembinaan.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $guru = Guru::whereHas('user', fn($q) => $q->where('id', $user->id))->firstOrFail();

        if (!$guru->isBK()) {
            abort(403, 'Anda tidak memiliki akses sebagai BK.');
        }

        $lembagaId = $guru->lembaga_id;
        $tahunAjaranId = $request->input('tahun_ajaran_id', TahunAjaran::where('is_active', true)->first()?->id);
        $kelasId = $request->input('kelas_id');

        // Semua siswa di lembaga, filter per kelas jika dipilih
        $siswas = Siswa::where('lembaga_id', $lembagaId)
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->withCount(['pelanggarans as total_pelanggaran' => fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId)])
            ->withSum(['pelanggarans as total_poin' => fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId)], 'poin')
            ->orderBy('nama')
            ->get();

        $kelasList = Kelas::where('lembaga_id', $lembagaId)->orderBy('nama')->get();
        $tahunAjarans = TahunAjaran::where('is_active', true)->get();

        // Statistik pelanggaran per kategori
        $kategoriStats = \App\Models\KategoriPelanggaran::withCount(['pelanggarans' => fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId)])
            ->get()
            ->map(fn($k) => ['nama' => $k->nama, 'total' => $k->pelanggarans_count]);

        return view('bk.index', compact('siswas', 'kelasList', 'tahunAjarans', 'tahunAjaranId', 'kelasId', 'kategoriStats'));
    }
}
