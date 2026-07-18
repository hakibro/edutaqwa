<?php

namespace App\Http\Controllers;

use App\Models\KelompokMapel;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\PerPageTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MapelController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Mapel::with(['lembaga', 'kelompokMapel'])->withCount('pengajaranMapels as guru_count');

        if ($user->lembaga_id) {
            $query->where('lembaga_id', $user->lembaga_id);
        } elseif ($user->yayasan_id) {
            $query->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        $mapels = $query->latest()->paginate($this->perPage($request));

        return view('mapel.index', compact('mapels'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);
        $kelompokMapels = KelompokMapel::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))->get();

        return view('mapel.create', compact('lembagas', 'kelompokMapels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'kelompok_mapel_id' => 'nullable|exists:kelompok_mapels,id',
            'nama' => 'required|string|max:255',
            'kode' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['lembaga_id'] = $lembagaId;

        $mapel = Mapel::create($validated);

        LogAktivita::log('create', 'Menambah mapel "' . $mapel->nama . '"', $mapel);

        return redirect()->route('mapel.index')->with('success', 'Mapel berhasil ditambahkan.');
    }

    public function edit(Mapel $mapel): View
    {
        $user = auth()->user();
        $lembagas = $this->getLembagas($user);
        $kelompokMapels = KelompokMapel::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))->get();

        return view('mapel.edit', compact('mapel', 'lembagas', 'kelompokMapels'));
    }

    public function update(Request $request, Mapel $mapel): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'kelompok_mapel_id' => 'nullable|exists:kelompok_mapels,id',
            'nama' => 'required|string|max:255',
            'kode' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['lembaga_id'] = $lembagaId;

        $mapel->update($validated);

        LogAktivita::log('update', 'Mengupdate mapel "' . $mapel->nama . '"', $mapel);

        return redirect()->route('mapel.index')->with('success', 'Mapel berhasil diperbarui.');
    }

    public function destroy(Mapel $mapel): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus mapel "' . $mapel->nama . '"', $mapel);
        $mapel->delete();

        return redirect()->route('mapel.index')->with('success', 'Mapel berhasil dihapus.');
    }

    /**
     * Import mapel dari file XLSX.
     * Kolom: nama, kode, kelompok_mapel (nama), deskripsi
     */
    public function import(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'file' => 'required|file|mimes:xlsx|max:5120',
        ]);

        $lembaga = Lembaga::findOrFail($lembagaId);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        array_shift($rows); // Skip header
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $row) {
            $nama = trim($row[0] ?? '');
            if (empty($nama)) {
                $skipped++;
                continue;
            }

            $exists = Mapel::where('lembaga_id', $lembagaId)->where('nama', $nama)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            // Resolve kelompok_mapel by name
            $kelompokMapelId = null;
            $namaKelompok = trim($row[2] ?? '');
            if (!empty($namaKelompok)) {
                $kelompok = KelompokMapel::firstOrCreate(
                    ['lembaga_id' => $lembagaId, 'nama' => $namaKelompok]
                );
                $kelompokMapelId = $kelompok->id;
            }

            try {
                Mapel::create([
                    'lembaga_id' => $lembagaId,
                    'nama' => $nama,
                    'kode' => trim($row[1] ?? '') ?: null,
                    'kelompok_mapel_id' => $kelompokMapelId,
                    'deskripsi' => trim($row[3] ?? '') ?: null,
                ]);
                $created++;
            } catch (\Exception $e) {
                $errors[] = $nama . ': ' . $e->getMessage();
                $skipped++;
            }
        }

        $msg = "Import selesai. {$created} mapel baru, {$skipped} dilewati.";
        if (!empty($errors)) {
            LogAktivita::log('import', 'Import mapel XLSX — ' . $msg . ' Errors: ' . implode('; ', array_slice($errors, 0, 5)));
        } else {
            LogAktivita::log('import', 'Import mapel XLSX — ' . $msg);
        }

        return redirect()->route('mapel.index')->with('success', $msg);
    }

    /**
     * Download template XLSX untuk import mapel.
     */
    public function template(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = ['nama', 'kode', 'kelompok_mapel', 'deskripsi'];

        return response()->streamDownload(function () use ($headers) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->fromArray([
                ['Matematika', 'MTK', 'Umum', 'Matematika Wajib'],
                ['Bahasa Indonesia', 'BIN', 'Umum', ''],
                ['Fikih', 'FKH', 'Agama', ''],
            ], null, 'A2');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'template-import-mapel.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * Export mapel ke XLSX.
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();
        $query = Mapel::with(['lembaga', 'kelompokMapel'])->withCount('pengajaranMapels as guru_count');

        if ($user->lembaga_id) {
            $query->where('lembaga_id', $user->lembaga_id);
        } elseif ($user->yayasan_id) {
            $query->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        $mapels = $query->latest()->get();

        $filename = 'export-mapel-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($mapels) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = ['No', 'Kode', 'Nama Mapel', 'Kelompok Mapel', 'Lembaga', 'Jumlah Guru', 'Deskripsi'];
            $sheet->fromArray([$headers], null, 'A1');

            $row = 2;
            foreach ($mapels as $i => $m) {
                $sheet->fromArray([
                    [
                        $i + 1,
                        $m->kode,
                        $m->nama,
                        $m->kelompokMapel?->nama ?? '-',
                        $m->lembaga?->nama ?? '-',
                        $m->guru_count,
                        $m->deskripsi,
                    ]
                ], null, "A{$row}");
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    protected function getLembagas($user)
    {
        if ($user->lembaga_id) {
            return Lembaga::where('id', $user->lembaga_id)->get();
        }
        return Lembaga::where('yayasan_id', $user->yayasan_id)->get();
    }
}
