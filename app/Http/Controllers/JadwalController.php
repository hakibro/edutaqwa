<?php

namespace App\Http\Controllers;

use App\Models\AkademikSetting;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class JadwalController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $query = Jadwal::with(['kelas', 'mapel', 'guru', 'tahunAjaran']);

        if ($user->lembaga_id) {
            $query->where('lembaga_id', $user->lembaga_id);
        } elseif ($user->yayasan_id) {
            $query->whereHas('kelas.lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $jadwals = $query->orderBy('hari')->orderBy('jam_ke')->paginate(20);

        // Resolve jam_ke labels from timetable settings (kbm only, 1-based KBM number)
        $timetableLabels = [];
        if ($lembagaId) {
            foreach ($hariList as $h) {
                $kbmItems = AkademikSetting::getKbmItems($lembagaId, $h);
                foreach ($kbmItems as $n => $item) {
                    $timetableLabels[$h][$n] = $item['label'] . ' (' . $item['jam_mulai'] . '-' . $item['jam_selesai'] . ')';
                }
            }
        }

        $kelasList = Kelas::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))
            ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)))
            ->get();

        // Grid view per kelas
        $gridKelasId = $request->filled('grid_kelas_id') ? $request->grid_kelas_id : null;
        $gridView = null;
        if ($gridKelasId) {
            $jadwalKelas = Jadwal::with(['mapel', 'guru'])
                ->where('kelas_id', $gridKelasId)
                ->where('lembaga_id', $user->lembaga_id)
                ->get()
                ->groupBy('hari');

            $gridView = [];
            foreach ($hariList as $hari) {
                $gridView[$hari] = $jadwalKelas->get($hari, collect())->sortBy('jam_ke');
            }
        }

        return view('jadwal.index', compact('jadwals', 'kelasList', 'hariList', 'gridView', 'gridKelasId', 'timetableLabels'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $yayasanId = $user->yayasan_id ?? Lembaga::find($user->lembaga_id)?->yayasan_id;

        $kelasList = Kelas::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))->get();
        $mapels = Mapel::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))->get();
        $gurus = Guru::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))->where('is_approved', true)->get();
        $tahunAjarans = TahunAjaran::where('yayasan_id', $yayasanId)->get();
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        // Resolve jam_ke labels from timetable (kbm only, 1-based KBM number)
        $timetableLabels = [];
        if ($lembagaId) {
            foreach ($hariList as $h) {
                $kbmItems = AkademikSetting::getKbmItems($lembagaId, $h);
                foreach ($kbmItems as $n => $item) {
                    $timetableLabels[$h][$n] = $item['label'] . ' (' . $item['jam_mulai'] . '-' . $item['jam_selesai'] . ')';
                }
            }
        }

        return view('jadwal.create', compact('kelasList', 'mapels', 'gurus', 'tahunAjarans', 'hariList', 'timetableLabels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapels,id',
            'guru_id' => 'required|exists:gurus,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_ke' => 'required|integer|min:1|max:20',
        ]);

        $validated['lembaga_id'] = $lembagaId;

        // Validasi jam_ke adalah slot KBM
        $kbmItems = AkademikSetting::getKbmItems($lembagaId, $validated['hari']);
        if (!isset($kbmItems[(int) $validated['jam_ke']])) {
            return back()->withInput()->withErrors(['jam_ke' => 'Jam ke-' . $validated['jam_ke'] . ' bukan slot KBM yang valid untuk ' . $validated['hari'] . '.']);
        }

        // Cek bentrok
        $bentrok = Jadwal::cekBentrok(
            $validated['guru_id'],
            $validated['hari'],
            (int) $validated['jam_ke']
        );

        if ($bentrok) {
            return back()->withInput()->withErrors(['guru_id' => $bentrok]);
        }

        Jadwal::create($validated);

        LogAktivita::log('create', 'Menambah jadwal ' . $validated['hari'] . ' Jam ' . $validated['jam_ke']);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Jadwal $jadwal): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $yayasanId = $user->yayasan_id ?? Lembaga::find($user->lembaga_id)?->yayasan_id;

        $kelasList = Kelas::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))->get();
        $mapels = Mapel::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))->get();
        $gurus = Guru::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))->where('is_approved', true)->get();
        $tahunAjarans = TahunAjaran::where('yayasan_id', $yayasanId)->get();
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        // Resolve jam_ke labels from timetable (kbm only, 1-based KBM number)
        $timetableLabels = [];
        if ($lembagaId) {
            foreach ($hariList as $h) {
                $kbmItems = AkademikSetting::getKbmItems($lembagaId, $h);
                foreach ($kbmItems as $n => $item) {
                    $timetableLabels[$h][$n] = $item['label'] . ' (' . $item['jam_mulai'] . '-' . $item['jam_selesai'] . ')';
                }
            }
        }

        return view('jadwal.edit', compact('jadwal', 'kelasList', 'mapels', 'gurus', 'tahunAjarans', 'hariList', 'timetableLabels'));
    }

    public function update(Request $request, Jadwal $jadwal): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapels,id',
            'guru_id' => 'required|exists:gurus,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_ke' => 'required|integer|min:1|max:20',
        ]);

        // Validasi jam_ke adalah slot KBM
        $kbmItems = AkademikSetting::getKbmItems(auth()->user()->lembaga_id, $validated['hari']);
        if (!isset($kbmItems[(int) $validated['jam_ke']])) {
            return back()->withInput()->withErrors(['jam_ke' => 'Jam ke-' . $validated['jam_ke'] . ' bukan slot KBM yang valid untuk ' . $validated['hari'] . '.']);
        }

        // Cek bentrok kecuali jadwal ini sendiri
        $bentrok = Jadwal::cekBentrok(
            $validated['guru_id'],
            $validated['hari'],
            (int) $validated['jam_ke'],
            $jadwal->id
        );

        if ($bentrok) {
            return back()->withInput()->withErrors(['guru_id' => $bentrok]);
        }

        $jadwal->update($validated);

        LogAktivita::log('update', 'Mengupdate jadwal');

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus jadwal');
        $jadwal->delete();

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    // === IMPORT ===

    public function showImportForm(): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $yayasanId = $user->yayasan_id ?? Lembaga::find($user->lembaga_id)?->yayasan_id;

        $tahunAjarans = TahunAjaran::where('yayasan_id', $yayasanId)->get();

        return view('jadwal.import', compact('tahunAjarans'));
    }

    public function import(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
        ]);

        $tahunAjaranId = $request->tahun_ajaran_id;
        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        // Skip header row (baris 0)
        $headers = array_map('strtolower', $rows[0] ?? []);
        $imported = 0;
        $skipped = 0;
        $errors = [];

        $kelasMap = Kelas::where('lembaga_id', $lembagaId)->pluck('id', 'nama')->toArray();
        $mapelMap = Mapel::where('lembaga_id', $lembagaId)->pluck('id', 'nama')->toArray();
        $guruMap = Guru::where('lembaga_id', $lembagaId)->where('is_approved', true)->pluck('id', 'nama')->toArray();
        $hariValid = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        foreach (array_slice($rows, 1) as $i => $row) {
            $rowNum = $i + 2; // baris excel (1-based, skip header)

            $kelasNama = trim((string) ($row[0] ?? ''));
            $mapelNama = trim((string) ($row[1] ?? ''));
            $guruNama = trim((string) ($row[2] ?? ''));
            $hari = trim((string) ($row[3] ?? ''));
            $jamKe = trim((string) ($row[4] ?? ''));

            if (empty($kelasNama) || empty($mapelNama) || empty($guruNama) || empty($hari) || $jamKe === '') {
                $skipped++;
                $errors[] = "Baris $rowNum: data tidak lengkap, dilewati.";
                continue;
            }

            $kelasId = $kelasMap[$kelasNama] ?? null;
            if (!$kelasId) {
                $skipped++;
                $errors[] = "Baris $rowNum: kelas '$kelasNama' tidak ditemukan.";
                continue;
            }

            $mapelId = $mapelMap[$mapelNama] ?? null;
            if (!$mapelId) {
                $skipped++;
                $errors[] = "Baris $rowNum: mapel '$mapelNama' tidak ditemukan.";
                continue;
            }

            $guruId = $guruMap[$guruNama] ?? null;
            if (!$guruId) {
                $skipped++;
                $errors[] = "Baris $rowNum: guru '$guruNama' tidak ditemukan atau belum approved.";
                continue;
            }

            $hariLower = strtolower($hari);
            if (!in_array($hariLower, $hariValid)) {
                $skipped++;
                $errors[] = "Baris $rowNum: hari '$hari' tidak valid.";
                continue;
            }
            $hariTitle = ucfirst($hariLower);

            // Validasi jam_ke integer
            if (!ctype_digit($jamKe) || (int) $jamKe < 1) {
                $skipped++;
                $errors[] = "Baris $rowNum: jam_ke harus angka >= 1.";
                continue;
            }
            $jamKe = (int) $jamKe;

            // Validasi jam_ke adalah slot KBM
            $kbmItems = AkademikSetting::getKbmItems($lembagaId, $hariTitle);
            if (!isset($kbmItems[$jamKe])) {
                $skipped++;
                $maxKbm = count($kbmItems);
                $errors[] = "Baris $rowNum: jam_ke $jamKe tidak valid untuk $hariTitle (slot KBM tersedia: 1-$maxKbm).";
                continue;
            }

            // Cek bentrok
            $bentrok = Jadwal::cekBentrok($guruId, $hariTitle, $jamKe);
            if ($bentrok) {
                $skipped++;
                $errors[] = "Baris $rowNum: bentrok — $bentrok";
                continue;
            }

            // Cek duplikasi identik
            $exists = Jadwal::where('kelas_id', $kelasId)
                ->where('mapel_id', $mapelId)
                ->where('guru_id', $guruId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->where('hari', $hariTitle)
                ->where('jam_ke', $jamKe)
                ->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            Jadwal::create([
                'lembaga_id' => $lembagaId,
                'kelas_id' => $kelasId,
                'mapel_id' => $mapelId,
                'guru_id' => $guruId,
                'tahun_ajaran_id' => $tahunAjaranId,
                'hari' => $hariTitle,
                'jam_ke' => $jamKe,
            ]);

            $imported++;
        }

        LogAktivita::log('import', "Import jadwal: $imported berhasil, $skipped dilewati.");

        $message = "Import selesai. $imported berhasil, $skipped dilewati.";
        if (!empty($errors)) {
            session()->flash('import_errors', $errors);
        }

        return redirect()->route('jadwal.index')->with('success', $message);
    }

    public function template()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'kelas');
        $sheet->setCellValue('B1', 'mapel');
        $sheet->setCellValue('C1', 'guru');
        $sheet->setCellValue('D1', 'hari');
        $sheet->setCellValue('E1', 'jam_ke');

        $sheet->setCellValue('A2', 'XII RPL');
        $sheet->setCellValue('B2', 'Matematika Wajib');
        $sheet->setCellValue('C2', 'Ahmad Fauzi');
        $sheet->setCellValue('D2', 'Senin');
        $sheet->setCellValue('E2', '1');

        $sheet->setCellValue('A3', 'XII RPL');
        $sheet->setCellValue('B3', 'Bahasa Inggris');
        $sheet->setCellValue('C3', 'Siti Aminah');
        $sheet->setCellValue('D3', 'Senin');
        $sheet->setCellValue('E3', '2');

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'template-import-jadwal.xlsx';
        $tempPath = storage_path('app/' . $filename);
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend();
    }
}
