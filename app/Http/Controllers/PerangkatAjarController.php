<?php

namespace App\Http\Controllers;

use App\Models\Atp;
use App\Models\Cp;
use App\Models\Guru;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Models\Mapel;
use App\Models\ModulAjar;
use App\Models\Tp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\PerPageTrait;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerangkatAjarController extends Controller
{
    use PerPageTrait;

    // ============================================================
    // INDEX — Halaman utama Perangkat Ajar
    // ============================================================

    public function index(Request $request): View
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        // Scope helpers
        $cpScope = function ($query) use ($user, $guru) {
            if ($user->isGuru() && $guru) {
                $mapelIds = $guru->pengajaranMapels()->pluck('mapel_id');
                $query->whereIn('mapel_id', $mapelIds);
            } elseif ($user->lembaga_id) {
                $query->whereHas('mapel', fn($q) => $q->where('lembaga_id', $user->lembaga_id));
            } elseif ($user->yayasan_id) {
                $query->whereHas('mapel.lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
            }
        };

        $tpScope = function ($query) use ($user, $guru) {
            if ($user->isGuru() && $guru) {
                $mapelIds = $guru->pengajaranMapels()->pluck('mapel_id');
                $query->whereHas('cp.mapel', fn($q) => $q->whereIn('mapel_id', $mapelIds));
            } elseif ($user->lembaga_id) {
                $query->whereHas('cp.mapel', fn($q) => $q->where('lembaga_id', $user->lembaga_id));
            } elseif ($user->yayasan_id) {
                $query->whereHas('cp.mapel.lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
            }
        };

        $atpScope = function ($query) use ($user, $guru) {
            if ($user->isGuru() && $guru) {
                $mapelIds = $guru->pengajaranMapels()->pluck('mapel_id');
                $query->whereHas('tp.cp.mapel', fn($q) => $q->whereIn('mapel_id', $mapelIds));
            } elseif ($user->lembaga_id) {
                $query->whereHas('tp.cp.mapel', fn($q) => $q->where('lembaga_id', $user->lembaga_id));
            } elseif ($user->yayasan_id) {
                $query->whereHas('tp.cp.mapel.lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
            }
        };

        $modulScope = function ($query) use ($user, $guru) {
            if ($user->isGuru() && $guru) {
                // Guru bisa lihat modul ajar dari semua guru di mapel yang sama
                $mapelIds = $guru->pengajaranMapels()->pluck('mapel_id');
                $query->whereIn('mapel_id', $mapelIds);
            } elseif ($user->lembaga_id) {
                $query->where('lembaga_id', $user->lembaga_id);
            } elseif ($user->yayasan_id) {
                $query->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
            }
        };

        $mapelId = $request->mapel_id;
        $guruId = $request->guru_id;

        // CP
        $cpQuery = Cp::with(['mapel', 'guru'])->withCount('tps');
        $cpScope($cpQuery);
        if ($mapelId)
            $cpQuery->where('mapel_id', $mapelId);
        if ($guruId)
            $cpQuery->where('guru_id', $guruId);
        $cps = $cpQuery->latest()->paginate($this->perPage($request), ['*'], 'cp_page');

        // TP
        $tpQuery = Tp::with(['cp.mapel', 'cp.guru'])->withCount('atps');
        $tpScope($tpQuery);
        if ($mapelId)
            $tpQuery->whereHas('cp', fn($q) => $q->where('mapel_id', $mapelId));
        if ($guruId)
            $tpQuery->whereHas('cp', fn($q) => $q->where('guru_id', $guruId));
        $tps = $tpQuery->latest()->paginate($this->perPage($request), ['*'], 'tp_page');

        // ATP
        $atpQuery = Atp::with(['tp.cp.mapel', 'tp.cp.guru']);
        $atpScope($atpQuery);
        if ($mapelId)
            $atpQuery->whereHas('tp.cp', fn($q) => $q->where('mapel_id', $mapelId));
        if ($guruId)
            $atpQuery->whereHas('tp.cp', fn($q) => $q->where('guru_id', $guruId));
        $atps = $atpQuery->orderBy('minggu_ke')->paginate($this->perPage($request), ['*'], 'atp_page');

        // Modul Ajar
        $modulQuery = ModulAjar::with(['mapel', 'guru']);
        $modulScope($modulQuery);
        if ($mapelId)
            $modulQuery->where('mapel_id', $mapelId);
        if ($guruId)
            $modulQuery->where('guru_id', $guruId);
        $moduls = $modulQuery->latest()->paginate($this->perPage($request), ['*'], 'modul_page');

        // Dropdown mapel — guru hanya lihat mapel yang diampu
        $mapelQuery = Mapel::query();
        if ($user->isGuru() && $guru) {
            $mapelIds = $guru->pengajaranMapels()->pluck('mapel_id');
            $mapelQuery->whereIn('id', $mapelIds);
        } else {
            $mapelQuery->when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))
                ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)));
        }
        $mapels = $mapelQuery->orderBy('nama')->get();

        // Dropdown guru — admin_lembaga lihat semua guru lembaga, guru lihat guru lain di mapel yang sama
        $gurus = collect();
        if ($user->isAdminLembaga()) {
            $guruQuery = Guru::where('lembaga_id', $user->lembaga_id);
            if ($mapelId) {
                $guruQuery->whereHas('pengajaranMapels', fn($q) => $q->where('mapel_id', $mapelId));
            }
            $gurus = $guruQuery->orderBy('nama')->get();
        } elseif ($user->isGuru() && $guru) {
            $mapelIds = $guru->pengajaranMapels()->pluck('mapel_id');
            $gurus = Guru::whereHas('pengajaranMapels', fn($q) => $q->whereIn('mapel_id', $mapelIds))
                ->orderBy('nama')->get();
        }

        // Dropdown untuk modal form
        $guruId = $guru?->id;
        if ($user->isGuru() && $guru) {
            $mapelOptions = Mapel::whereIn('id', $guru->pengajaranMapels()->pluck('mapel_id'))->orderBy('nama')->get();
        } else {
            $mapelOptions = $mapels;
        }

        $cpOptions = Cp::with('mapel')
            ->when($guruId && $user->isGuru(), fn($q) => $q->where('guru_id', $guruId))
            ->get();
        $tpOptions = Tp::with('cp.mapel')
            ->when($guruId && $user->isGuru(), fn($q) => $q->whereHas('cp', fn($qc) => $qc->where('guru_id', $guruId)))
            ->get();

        return view('perangkat-ajar.index', compact(
            'cps',
            'tps',
            'atps',
            'moduls',
            'mapels',
            'gurus',
            'mapelOptions',
            'cpOptions',
            'tpOptions',
            'user',
            'guru'
        ));
    }

    // ============================================================
    // CP — CRUD
    // ============================================================

    public function storeCp(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'fase' => 'required|string|max:20',
            'kode' => 'nullable|string|max:50',
            'deskripsi' => 'required|string',
        ]);

        $validated['guru_id'] = $guru?->id ?? $request->guru_id;

        $cp = Cp::create($validated);
        LogAktivita::log('create', 'Menambah CP "' . $cp->kode . '"', $cp);

        return back()->with('success', 'CP berhasil ditambahkan.');
    }

    public function updateCp(Request $request, Cp $cp): RedirectResponse
    {
        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'fase' => 'required|string|max:20',
            'kode' => 'nullable|string|max:50',
            'deskripsi' => 'required|string',
        ]);

        $cp->update($validated);
        LogAktivita::log('update', 'Mengupdate CP "' . $cp->kode . '"', $cp);

        return back()->with('success', 'CP berhasil diperbarui.');
    }

    public function destroyCp(Cp $cp): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus CP "' . $cp->kode . '"', $cp);
        $cp->delete();

        return back()->with('success', 'CP berhasil dihapus.');
    }

    // ============================================================
    // TP — CRUD
    // ============================================================

    public function storeTp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cp_id' => 'required|exists:cps,id',
            'kode' => 'nullable|string|max:50',
            'deskripsi' => 'required|string',
        ]);

        $tp = Tp::create($validated);
        LogAktivita::log('create', 'Menambah TP "' . $tp->kode . '"', $tp);

        return back()->with('success', 'TP berhasil ditambahkan.');
    }

    public function updateTp(Request $request, Tp $tp): RedirectResponse
    {
        $validated = $request->validate([
            'cp_id' => 'required|exists:cps,id',
            'kode' => 'nullable|string|max:50',
            'deskripsi' => 'required|string',
        ]);

        $tp->update($validated);
        LogAktivita::log('update', 'Mengupdate TP "' . $tp->kode . '"', $tp);

        return back()->with('success', 'TP berhasil diperbarui.');
    }

    public function destroyTp(Tp $tp): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus TP "' . $tp->kode . '"', $tp);
        $tp->delete();

        return back()->with('success', 'TP berhasil dihapus.');
    }

    // ============================================================
    // ATP — CRUD
    // ============================================================

    public function storeAtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tp_id' => 'required|exists:tps,id',
            'minggu_ke' => 'required|integer|min:1',
            'materi' => 'required|string',
        ]);

        Atp::create($validated);
        LogAktivita::log('create', 'Menambah ATP minggu ke-' . $validated['minggu_ke']);

        return back()->with('success', 'ATP berhasil ditambahkan.');
    }

    public function updateAtp(Request $request, Atp $atp): RedirectResponse
    {
        $validated = $request->validate([
            'tp_id' => 'required|exists:tps,id',
            'minggu_ke' => 'required|integer|min:1',
            'materi' => 'required|string',
        ]);

        $atp->update($validated);
        LogAktivita::log('update', 'Mengupdate ATP minggu ke-' . $validated['minggu_ke']);

        return back()->with('success', 'ATP berhasil diperbarui.');
    }

    public function destroyAtp(Atp $atp): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus ATP minggu ke-' . $atp->minggu_ke);
        $atp->delete();

        return back()->with('success', 'ATP berhasil dihapus.');
    }

    // ============================================================
    // Modul Ajar — CRUD
    // ============================================================

    public function storeModul(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();

        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file' => 'nullable|file|mimes:doc,docx,pdf|max:10240',
        ]);

        $lembagaId = $user->lembaga_id ?? Mapel::find($validated['mapel_id'])->lembaga_id;

        $data = [
            'lembaga_id' => $lembagaId,
            'mapel_id' => $validated['mapel_id'],
            'guru_id' => $guru?->id ?? $request->guru_id,
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'file_path' => null,
        ];

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('modul-ajar', 'public');
        }

        $modul = ModulAjar::create($data);
        LogAktivita::log('create', 'Menambah Modul Ajar "' . $modul->judul . '"', $modul);

        return back()->with('success', 'Modul Ajar berhasil ditambahkan.');
    }

    public function updateModul(Request $request, ModulAjar $modulAjar): RedirectResponse
    {
        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file' => 'nullable|file|mimes:doc,docx,pdf|max:10240',
        ]);

        $modulAjar->mapel_id = $validated['mapel_id'];
        $modulAjar->judul = $validated['judul'];
        $modulAjar->deskripsi = $validated['deskripsi'] ?? null;

        if ($request->hasFile('file')) {
            if ($modulAjar->file_path && \Storage::disk('public')->exists($modulAjar->file_path)) {
                \Storage::disk('public')->delete($modulAjar->file_path);
            }
            $modulAjar->file_path = $request->file('file')->store('modul-ajar', 'public');
        }

        $modulAjar->save();
        LogAktivita::log('update', 'Mengupdate Modul Ajar "' . $modulAjar->judul . '"', $modulAjar);

        return back()->with('success', 'Modul Ajar berhasil diperbarui.');
    }

    public function destroyModul(ModulAjar $modulAjar): RedirectResponse
    {
        if ($modulAjar->file_path && \Storage::disk('public')->exists($modulAjar->file_path)) {
            \Storage::disk('public')->delete($modulAjar->file_path);
        }

        LogAktivita::log('delete', 'Menghapus Modul Ajar "' . $modulAjar->judul . '"', $modulAjar);
        $modulAjar->delete();

        return back()->with('success', 'Modul Ajar berhasil dihapus.');
    }

    public function downloadModul(ModulAjar $modulAjar)
    {
        if (!$modulAjar->file_path || !\Storage::disk('public')->exists($modulAjar->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return \Storage::disk('public')->download($modulAjar->file_path, $modulAjar->judul . '.' . pathinfo($modulAjar->file_path, PATHINFO_EXTENSION));
    }

    public function viewModul(ModulAjar $modulAjar)
    {
        if (!$modulAjar->file_path || !\Storage::disk('public')->exists($modulAjar->file_path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        $ext = strtolower(pathinfo($modulAjar->file_path, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            return response()->file(\Storage::disk('public')->path($modulAjar->file_path));
        }

        // doc/docx — render server-side via PhpWord → HTML
        $html = $this->convertDocToHtml(\Storage::disk('public')->path($modulAjar->file_path));
        return view('perangkat-ajar.view', compact('modulAjar', 'html'));
    }

    private function convertDocToHtml(string $filePath): string
    {
        try {
            $phpWord = PhpWordIOFactory::load($filePath);
            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');

            ob_start();
            $writer->save('php://output');
            $html = ob_get_clean();

            // Extract only body content — strip <html>, <head>, <body> wrappers
            if (preg_match('#<body[^>]*>(.*?)</body>#is', $html, $m)) {
                $html = $m[1];
            }

            // Rewrite image src from temp path to inline base64
            $tmpDir = sys_get_temp_dir() . '/';
            $html = preg_replace_callback(
                '#<img\s+[^>]*src="(' . preg_quote($tmpDir, '#') . '[^"]+)"[^>]*>#i',
                function ($matches) {
                    $imgPath = $matches[1];
                    if (file_exists($imgPath)) {
                        $mime = mime_content_type($imgPath) ?: 'image/png';
                        $b64 = base64_encode(file_get_contents($imgPath));
                        return '<img src="data:' . $mime . ';base64,' . $b64 . '" style="max-width:100%">';
                    }
                    return $matches[0];
                },
                $html
            );

            return $html ?: '<p class="text-gray-500">Dokumen kosong.</p>';
        } catch (\Throwable $e) {
            return '<p class="text-red-500">Gagal merender dokumen: ' . e($e->getMessage()) . '</p>';
        }
    }

    // ============================================================
    // IMPORT — Komprehensif (3 sheet: CP, TP, ATP)
    // Sheet 1 — CP: mapel_kode, fase, kode, deskripsi
    // Sheet 2 — TP: cp_kode, kode, deskripsi
    // Sheet 3 — ATP: tp_kode, minggu_ke, materi
    // ============================================================

    public function importKomprehensif(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();
        $lembagaId = $user->lembaga_id;
        $guruId = $guru?->id;

        $request->validate(['file' => 'required|file|mimes:xlsx|max:5120']);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $cpCreated = $tpCreated = $atpCreated = $skipped = 0;

        // === Sheet 1: CP ===
        $sheet = $spreadsheet->getSheetByName('CP') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        array_shift($rows);

        $mapelCache = [];

        foreach ($rows as $row) {
            $mapelKode = trim($row[0] ?? '');
            $fase = trim($row[1] ?? '');
            $kode = trim($row[2] ?? '');
            $deskripsi = trim($row[3] ?? '');

            if (empty($mapelKode) || empty($deskripsi)) {
                $skipped++;
                continue;
            }

            if (!isset($mapelCache[$mapelKode])) {
                $mapelCache[$mapelKode] = Mapel::where('kode', $mapelKode)
                    ->when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))
                    ->first();
            }
            $mapel = $mapelCache[$mapelKode];
            if (!$mapel) {
                $skipped++;
                continue;
            }

            $exists = Cp::where('kode', $kode)->where('mapel_id', $mapel->id)
                ->when($guruId, fn($q) => $q->where('guru_id', $guruId))
                ->exists();
            if (!$exists) {
                Cp::create([
                    'mapel_id' => $mapel->id,
                    'guru_id' => $guruId,
                    'fase' => $fase ?: 'Fase A',
                    'kode' => $kode ?: null,
                    'deskripsi' => $deskripsi,
                ]);
                $cpCreated++;
            } else {
                $skipped++;
            }
        }

        // === Sheet 2: TP ===
        $sheet2 = $spreadsheet->getSheetByName('TP');
        if ($sheet2) {
            $rows = $sheet2->toArray();
            array_shift($rows);
            $cpCache = [];

            foreach ($rows as $row) {
                $cpKode = trim($row[0] ?? '');
                $kode = trim($row[1] ?? '');
                $deskripsi = trim($row[2] ?? '');

                if (empty($cpKode) || empty($deskripsi)) {
                    $skipped++;
                    continue;
                }

                if (!isset($cpCache[$cpKode])) {
                    $cpCache[$cpKode] = Cp::where('kode', $cpKode)
                        ->when($guruId, fn($q) => $q->where('guru_id', $guruId))
                        ->first();
                }
                $cp = $cpCache[$cpKode];
                if (!$cp) {
                    $skipped++;
                    continue;
                }

                $exists = Tp::where('kode', $kode)->where('cp_id', $cp->id)->exists();
                if (!$exists) {
                    Tp::create(['cp_id' => $cp->id, 'kode' => $kode ?: null, 'deskripsi' => $deskripsi]);
                    $tpCreated++;
                } else {
                    $skipped++;
                }
            }
        }

        // === Sheet 3: ATP ===
        $sheet3 = $spreadsheet->getSheetByName('ATP');
        if ($sheet3) {
            $rows = $sheet3->toArray();
            array_shift($rows);
            $tpCache = [];

            foreach ($rows as $row) {
                $tpKode = trim($row[0] ?? '');
                $mingguKe = (int) trim($row[1] ?? '');
                $materi = trim($row[2] ?? '');

                if (empty($tpKode) || empty($materi) || $mingguKe < 1) {
                    $skipped++;
                    continue;
                }

                if (!isset($tpCache[$tpKode])) {
                    $tpCache[$tpKode] = Tp::where('kode', $tpKode)
                        ->when($guruId, fn($q) => $q->whereHas('cp', fn($qc) => $qc->where('guru_id', $guruId)))
                        ->first();
                }
                $tp = $tpCache[$tpKode];
                if (!$tp) {
                    $skipped++;
                    continue;
                }

                $exists = Atp::where('tp_id', $tp->id)->where('minggu_ke', $mingguKe)->exists();
                if (!$exists) {
                    Atp::create(['tp_id' => $tp->id, 'minggu_ke' => $mingguKe, 'materi' => $materi]);
                    $atpCreated++;
                } else {
                    $skipped++;
                }
            }
        }

        $msg = "Import selesai. CP: {$cpCreated} baru, TP: {$tpCreated} baru, ATP: {$atpCreated} baru, {$skipped} dilewati.";
        LogAktivita::log('import', $msg);
        return back()->with('success', $msg);
    }

    public function templateKomprehensif(): StreamedResponse
    {
        $user = auth()->user();
        $guru = Guru::where('id', $user->guru_id)->first();
        $lembagaId = $user->lembaga_id;

        // Ambil daftar kode mapel untuk dropdown
        $mapelKodes = Mapel::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))
            ->whereNotNull('kode')->pluck('kode')->values()->toArray();

        return response()->streamDownload(function () use ($mapelKodes) {
            $spreadsheet = new Spreadsheet();

            // ---- Sheet 1: CP ----
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('CP');
            $sheet->fromArray([['mapel_kode', 'fase', 'kode', 'deskripsi']], null, 'A1');
            $sheet->fromArray([
                ['MTK', 'Fase A', 'CP-1', 'Peserta didik mampu memahami operasi bilangan cacah'],
            ], null, 'A2');

            // Dropdown kolom A (mapel_kode)
            if (!empty($mapelKodes)) {
                $hiddenSheet = $spreadsheet->createSheet();
                $hiddenSheet->setTitle('_mapels');
                $hiddenSheet->fromArray(array_map(fn($v) => [$v], $mapelKodes), null, 'A1');
                $lastRow = count($mapelKodes);
                $validation = $sheet->getDataValidation('A2:A1048576');
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setFormula1("_mapels!\$A\$1:\$A\${$lastRow}");
                $validation->setAllowBlank(true);
                $validation->setShowDropDown(true);
                $hiddenSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);
            }

            // ---- Sheet 2: TP ----
            $sheet2 = $spreadsheet->createSheet();
            $sheet2->setTitle('TP');
            $sheet2->fromArray([['cp_kode', 'kode', 'deskripsi']], null, 'A1');
            $sheet2->fromArray([
                ['CP-1', 'TP-1.1', 'Menyebutkan bilangan cacah 1-100'],
                ['CP-1', 'TP-1.2', 'Menjumlahkan bilangan cacah'],
            ], null, 'A2');

            // ---- Sheet 3: ATP ----
            $sheet3 = $spreadsheet->createSheet();
            $sheet3->setTitle('ATP');
            $sheet3->fromArray([['tp_kode', 'minggu_ke', 'materi']], null, 'A1');
            $sheet3->fromArray([
                ['TP-1.1', '1', 'Bilangan cacah 1-20'],
                ['TP-1.1', '2', 'Penjumlahan bilangan cacah'],
            ], null, 'A2');

            (new Xlsx($spreadsheet))->save('php://output');
        }, 'template-import-perangkat-ajar.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
