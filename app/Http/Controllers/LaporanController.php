<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPtk;
use App\Models\AgendaMengajar;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\Mapel;
use App\Models\Nilai;
use App\Models\Pelanggaran;
use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    use PerPageTrait;

    protected function lembagaId()
    {
        return auth()->user()->lembaga_id;
    }

    // === INDEX ===
    public function index()
    {
        return view('laporan.index');
    }

    // === LAPORAN AKADEMIK (Rekap Nilai) ===
    public function akademik(Request $request)
    {
        $lembagaId = $this->lembagaId();
        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');

        $kelasList = Kelas::where('lembaga_id', $lembagaId)->get();
        $mapelList = Mapel::where('lembaga_id', $lembagaId)->get();

        $nilais = collect();
        if ($kelasId && $mapelId) {
            $nilais = Nilai::with(['siswa', 'jenisNilai'])
                ->where('kelas_id', $kelasId)
                ->where('mapel_id', $mapelId)
                ->where('is_finalized', true)
                ->get()
                ->groupBy('siswa_id');
        }

        return view('laporan.akademik', compact('kelasList', 'mapelList', 'nilais', 'kelasId', 'mapelId'));
    }

    public function exportAkademik(Request $request)
    {
        $lembagaId = $this->lembagaId();
        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');

        $nilais = Nilai::with(['siswa', 'jenisNilai', 'mapel'])
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->where('is_finalized', true)
            ->get()
            ->groupBy('siswa_id');

        $kelas = Kelas::find($kelasId);
        $mapel = Mapel::find($mapelId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Nilai');

        $sheet->setCellValue('A1', 'Rekap Nilai');
        $sheet->setCellValue('A2', 'Kelas: ' . ($kelas->nama ?? '-'));
        $sheet->setCellValue('A3', 'Mapel: ' . ($mapel->nama ?? '-'));

        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'Nama Siswa');
        $sheet->setCellValue('C5', 'Jenis Nilai');
        $sheet->setCellValue('D5', 'Nilai');

        $row = 6;
        $no = 1;
        foreach ($nilais as $siswaId => $nilaiSiswa) {
            $siswa = $nilaiSiswa->first()->siswa;
            foreach ($nilaiSiswa as $nilai) {
                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $siswa->nama ?? '-');
                $sheet->setCellValue('C' . $row, $nilai->jenisNilai->nama ?? '-');
                $sheet->setCellValue('D' . $row, $nilai->nilai);
                $row++;
            }
            $no++;
        }

        return $this->downloadXlsx($spreadsheet, 'rekap-nilai-' . date('Y-m-d') . '.xlsx');
    }

    // === LAPORAN KESISWAAN ===
    public function kesiswaan(Request $request)
    {
        $lembagaId = $this->lembagaId();
        $kelasId = $request->get('kelas_id');

        $kelasList = Kelas::where('lembaga_id', $lembagaId)->get();

        // Rekap siswa per kelas
        $siswaPerKelas = Kelas::where('lembaga_id', $lembagaId)
            ->withCount(['riwayatKelasSiswas' => fn($q) => $q->whereNull('tanggal_keluar')])
            ->get();

        // Pelanggaran
        $pelanggarans = Pelanggaran::with(['siswa', 'kategoriPelanggaran'])
            ->whereHas('siswa', fn($q) => $q->where('lembaga_id', $lembagaId))
            ->when($kelasId, function ($q) use ($kelasId) {
                $q->whereHas('siswa.riwayatKelasSiswas', fn($q2) => $q2
                    ->where('kelas_id', $kelasId)->whereNull('tanggal_keluar'));
            })
            ->latest()
            ->paginate($this->perPage($request), ['*'], 'pelanggaran_page');

        return view('laporan.kesiswaan', compact('kelasList', 'siswaPerKelas', 'pelanggarans', 'kelasId'));
    }

    public function exportKesiswaan(Request $request)
    {
        $lembagaId = $this->lembagaId();
        $kelasId = $request->get('kelas_id');

        $pelanggarans = Pelanggaran::with(['siswa', 'kategoriPelanggaran'])
            ->whereHas('siswa', fn($q) => $q->where('lembaga_id', $lembagaId))
            ->when($kelasId, function ($q) use ($kelasId) {
                $q->whereHas('siswa.riwayatKelasSiswas', fn($q2) => $q2
                    ->where('kelas_id', $kelasId)->whereNull('tanggal_keluar'));
            })
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pelanggaran Siswa');

        $sheet->setCellValue('A1', 'Rekap Pelanggaran Siswa');
        $sheet->setCellValue('A3', 'No');
        $sheet->setCellValue('B3', 'Nama Siswa');
        $sheet->setCellValue('C3', 'Kategori');
        $sheet->setCellValue('D3', 'Poin');
        $sheet->setCellValue('E3', 'Deskripsi');
        $sheet->setCellValue('F3', 'Tanggal');
        $sheet->setCellValue('G3', 'Tindakan');

        $row = 4;
        foreach ($pelanggarans as $i => $p) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $p->siswa->nama ?? '-');
            $sheet->setCellValue('C' . $row, $p->kategoriPelanggaran->nama ?? '-');
            $sheet->setCellValue('D' . $row, $p->kategoriPelanggaran->poin ?? 0);
            $sheet->setCellValue('E' . $row, $p->deskripsi);
            $sheet->setCellValue('F' . $row, $p->tanggal?->format('Y-m-d'));
            $sheet->setCellValue('G' . $row, $p->tindakan ?? '-');
            $row++;
        }

        return $this->downloadXlsx($spreadsheet, 'pelanggaran-siswa-' . date('Y-m-d') . '.xlsx');
    }

    // === LAPORAN PRESENSI ===
    public function presensi(Request $request)
    {
        $lembagaId = $this->lembagaId();
        $kelasId = $request->get('kelas_id');
        $bulan = $request->get('bulan', date('Y-m'));

        $kelasList = Kelas::where('lembaga_id', $lembagaId)->get();

        $presensis = collect();
        $statistik = [];
        if ($kelasId) {
            $presensis = Presensi::with(['jadwal.mapel', 'detailPresensis.siswa'])
                ->whereHas('jadwal', fn($q) => $q->where('lembaga_id', $lembagaId)->where('kelas_id', $kelasId))
                ->whereMonth('tanggal', substr($bulan, 5, 2))
                ->whereYear('tanggal', substr($bulan, 0, 4))
                ->latest()
                ->get();

            // Statistik presensi per siswa
            $siswaIds = Siswa::where('lembaga_id', $lembagaId)
                ->whereHas('riwayatKelasSiswas', fn($q) => $q
                    ->where('kelas_id', $kelasId)->whereNull('tanggal_keluar'))
                ->pluck('id');

            $statistik = \App\Models\DetailPresensi::whereIn('siswa_id', $siswaIds)
                ->whereHas('presensi', fn($q) => $q
                    ->whereMonth('tanggal', substr($bulan, 5, 2))
                    ->whereYear('tanggal', substr($bulan, 0, 4)))
                ->selectRaw('siswa_id, status, count(*) as jumlah')
                ->groupBy('siswa_id', 'status')
                ->get()
                ->groupBy('siswa_id');
        }

        return view('laporan.presensi', compact('kelasList', 'presensis', 'statistik', 'kelasId', 'bulan'));
    }

    public function exportPresensi(Request $request)
    {
        $lembagaId = $this->lembagaId();
        $kelasId = $request->get('kelas_id');
        $bulan = $request->get('bulan', date('Y-m'));

        $presensis = Presensi::with(['jadwal.mapel', 'detailPresensis.siswa'])
            ->whereHas('jadwal', fn($q) => $q->where('lembaga_id', $lembagaId)->where('kelas_id', $kelasId))
            ->whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->latest()
            ->get();

        $kelas = Kelas::find($kelasId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Presensi');

        $sheet->setCellValue('A1', 'Rekap Presensi - ' . $bulan);
        $sheet->setCellValue('A2', 'Kelas: ' . ($kelas->nama ?? '-'));
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Tanggal');
        $sheet->setCellValue('C4', 'Mapel');
        $sheet->setCellValue('D4', 'Nama Siswa');
        $sheet->setCellValue('E4', 'Status');

        $row = 5;
        $no = 1;
        foreach ($presensis as $p) {
            foreach ($p->detailPresensis as $d) {
                $sheet->setCellValue('A' . $row, $no);
                $sheet->setCellValue('B' . $row, $p->tanggal->format('Y-m-d'));
                $sheet->setCellValue('C' . $row, $p->jadwal->mapel->nama ?? '-');
                $sheet->setCellValue('D' . $row, $d->siswa->nama ?? '-');
                $sheet->setCellValue('E' . $row, $d->status);
                $row++;
            }
            $no++;
        }

        return $this->downloadXlsx($spreadsheet, 'rekap-presensi-' . $bulan . '.xlsx');
    }

    // === LAPORAN ABSENSI PTK ===
    public function absensiPtk(Request $request)
    {
        $lembagaId = $this->lembagaId();
        $bulan = $request->get('bulan', date('Y-m'));

        $absensis = AbsensiPtk::with('guru')
            ->where('lembaga_id', $lembagaId)
            ->whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->latest()
            ->paginate($this->perPage($request));

        return view('laporan.absensi-ptk', compact('absensis', 'bulan'));
    }

    public function exportAbsensiPtk(Request $request)
    {
        $lembagaId = $this->lembagaId();
        $bulan = $request->get('bulan', date('Y-m'));

        $absensis = AbsensiPtk::with('guru')
            ->where('lembaga_id', $lembagaId)
            ->whereMonth('tanggal', substr($bulan, 5, 2))
            ->whereYear('tanggal', substr($bulan, 0, 4))
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Absensi PTK');

        $sheet->setCellValue('A1', 'Rekap Absensi PTK - ' . $bulan);
        $sheet->setCellValue('A3', 'No');
        $sheet->setCellValue('B3', 'Nama Guru');
        $sheet->setCellValue('C3', 'Tanggal');
        $sheet->setCellValue('D3', 'Check-in');
        $sheet->setCellValue('E3', 'Check-out');
        $sheet->setCellValue('F3', 'Status');
        $sheet->setCellValue('G3', 'Keterlambatan (menit)');

        $row = 4;
        foreach ($absensis as $i => $a) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $a->guru->nama ?? '-');
            $sheet->setCellValue('C' . $row, $a->tanggal->format('Y-m-d'));
            $sheet->setCellValue('D' . $row, $a->check_in?->format('H:i'));
            $sheet->setCellValue('E' . $row, $a->check_out?->format('H:i'));
            $sheet->setCellValue('F' . $row, $a->status);
            $sheet->setCellValue('G' . $row, $a->keterlambatan_menit);
            $row++;
        }

        return $this->downloadXlsx($spreadsheet, 'absensi-ptk-' . $bulan . '.xlsx');
    }

    // === LAPORAN AGENDA MENGAJAR ===
    public function agendaMengajar(Request $request)
    {
        $lembagaId = $this->lembagaId();
        $guruId = $request->get('guru_id');

        $guruList = Guru::where('lembaga_id', $lembagaId)->get();

        $agendas = AgendaMengajar::with(['guru', 'jadwal.mapel', 'jadwal.kelas'])
            ->whereHas('guru', fn($q) => $q->where('lembaga_id', $lembagaId))
            ->when($guruId, fn($q) => $q->where('guru_id', $guruId))
            ->latest()
            ->paginate($this->perPage($request));

        return view('laporan.agenda-mengajar', compact('guruList', 'agendas', 'guruId'));
    }

    public function exportAgendaMengajar(Request $request)
    {
        $lembagaId = $this->lembagaId();
        $guruId = $request->get('guru_id');

        $agendas = AgendaMengajar::with(['guru', 'jadwal.mapel', 'jadwal.kelas'])
            ->whereHas('guru', fn($q) => $q->where('lembaga_id', $lembagaId))
            ->when($guruId, fn($q) => $q->where('guru_id', $guruId))
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Agenda Mengajar');

        $sheet->setCellValue('A1', 'Laporan Agenda Mengajar');
        $sheet->setCellValue('A3', 'No');
        $sheet->setCellValue('B3', 'Guru');
        $sheet->setCellValue('C3', 'Mapel');
        $sheet->setCellValue('D3', 'Kelas');
        $sheet->setCellValue('E3', 'Tanggal');
        $sheet->setCellValue('F3', 'Deskripsi');
        $sheet->setCellValue('G3', 'Status Verifikasi');

        $row = 4;
        foreach ($agendas as $i => $a) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $a->guru->nama ?? '-');
            $sheet->setCellValue('C' . $row, $a->jadwal->mapel->nama ?? '-');
            $sheet->setCellValue('D' . $row, $a->jadwal->kelas->nama ?? '-');
            $sheet->setCellValue('E' . $row, $a->created_at->format('Y-m-d H:i'));
            $sheet->setCellValue('F' . $row, $a->deskripsi ?? '');
            $sheet->setCellValue('G' . $row, $a->is_verified ? 'Verified' : 'Pending');
            $row++;
        }

        return $this->downloadXlsx($spreadsheet, 'agenda-mengajar-' . date('Y-m-d') . '.xlsx');
    }

    // === HELPER ===
    protected function downloadXlsx(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
