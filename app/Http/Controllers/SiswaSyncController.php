<?php

namespace App\Http\Controllers;

use App\Models\Lembaga;
use App\Models\TahunAjaran;
use App\Services\SisdaImportService;
use Illuminate\Http\Request;

class SiswaSyncController extends Controller
{
    protected SisdaImportService $sisdaService;

    public function __construct(SisdaImportService $sisdaService)
    {
        $this->sisdaService = $sisdaService;
    }

    /**
     * Halaman sync siswa — admin lembaga / admin yayasan.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $lembagas = collect();
        $tahunAjarans = collect();

        if ($user->isAdminYayasan() || $user->isSuperAdmin()) {
            $lembagas = Lembaga::whereNotNull('unit_formal')
                ->where('unit_formal', '!=', '')
                ->get();
            $tahunAjarans = TahunAjaran::where('yayasan_id', $user->yayasan_id)->get();
        } elseif ($user->lembaga_id) {
            $lembagas = Lembaga::where('id', $user->lembaga_id)
                ->whereNotNull('unit_formal')
                ->where('unit_formal', '!=', '')
                ->get();
            $lembaga = $lembagas->first();
            if ($lembaga) {
                $tahunAjarans = TahunAjaran::where('yayasan_id', $lembaga->yayasan_id)->get();
            }
        }

        return view('master-data.sync-siswa', compact('lembagas', 'tahunAjarans'));
    }

    /**
     * Jalankan sync untuk lembaga tertentu.
     */
    public function sync(Request $request)
    {
        $request->validate(['lembaga_id' => 'required|exists:lembagas,id']);

        $lembaga = Lembaga::findOrFail($request->lembaga_id);

        $user = $request->user();
        if (!$user->isAdminYayasan() && !$user->isSuperAdmin() && $user->lembaga_id !== $lembaga->id) {
            abort(403);
        }

        $result = $this->sisdaService->syncForLembaga($lembaga);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Sync kenaikan kelas untuk tahun ajaran baru.
     */
    public function kenaikanKelas(Request $request)
    {
        $request->validate([
            'lembaga_id' => 'required|exists:lembagas,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
        ]);

        $lembaga = Lembaga::findOrFail($request->lembaga_id);
        $tahunAjaran = TahunAjaran::findOrFail($request->tahun_ajaran_id);

        $user = $request->user();
        if (!$user->isAdminYayasan() && !$user->isSuperAdmin() && $user->lembaga_id !== $lembaga->id) {
            abort(403);
        }

        $result = $this->sisdaService->syncKenaikanKelas($lembaga, $tahunAjaran);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
