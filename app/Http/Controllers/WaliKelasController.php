<?php

namespace App\Http\Controllers;

use App\Models\DetailJurnalSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaliKelasController extends Controller
{
    /**
     * Dashboard Wali Kelas — lihat perkembangan siswa di kelas walinya.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $guru = Guru::whereHas('user', fn($q) => $q->where('id', $user->id))->firstOrFail();

        // Cari tugas tambahan Wali Kelas aktif
        $tahunAjaranId = $request->input('tahun_ajaran_id', TahunAjaran::where('is_active', true)->first()?->id);
        $waliKelas = $guru->waliKelasAktif($tahunAjaranId);

        if (!$waliKelas || !$waliKelas->kelas) {
            abort(403, 'Anda tidak memiliki akses sebagai Wali Kelas.');
        }

        $kelas = $waliKelas->kelas;
        $siswas = $kelas->siswas()->orderBy('nama')->get();
        $siswaIds = $siswas->pluck('id');

        // Ringkasan presensi per siswa (bulan berjalan) via DetailJurnalSiswa
        $bulan = now()->month;
        $tahun = now()->year;
        $presensiRaw = DetailJurnalSiswa::whereIn('siswa_id', $siswaIds)
            ->whereHas('jurnalMengajar', function ($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun);
            })
            ->get();

        $presensiSummary = [];
        foreach ($siswaIds as $sid) {
            $presensiSummary[$sid] = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
        }
        foreach ($presensiRaw as $p) {
            $sid = $p->siswa_id;
            $status = $p->status;
            if (isset($presensiSummary[$sid][$status])) {
                $presensiSummary[$sid][$status]++;
            }
        }

        $tahunAjarans = TahunAjaran::where('is_active', true)->get();

        return view('wali-kelas.index', compact('kelas', 'siswas', 'presensiSummary', 'tahunAjarans', 'tahunAjaranId'));
    }
}
