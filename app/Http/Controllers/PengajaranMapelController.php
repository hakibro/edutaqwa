<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\LogAktivita;
use App\Models\Mapel;
use App\Models\PengajaranMapel;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PengajaranMapelController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;
        $query = PengajaranMapel::with(['mapel', 'guru', 'tahunAjaran']);

        if ($lembagaId) {
            $query->whereHas('guru', fn($q) => $q->where('lembaga_id', $lembagaId));
        } elseif ($user->yayasan_id) {
            $query->whereHas('guru.lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('guru', fn($qg) => $qg->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('mapel', fn($qm) => $qm->where('nama', 'like', "%{$search}%"));
            });
        }

        $pengajarans = $query->latest()->paginate(10);
        $tahunAjarans = TahunAjaran::when($user->yayasan_id, fn($q) => $q->where('yayasan_id', $user->yayasan_id))->get();

        $mapels = Mapel::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))
            ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)))
            ->get();
        $gurus = Guru::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))
            ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)))
            ->get();

        return view('pengajaran-mapel.index', compact('pengajarans', 'tahunAjarans', 'mapels', 'gurus'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $mapels = Mapel::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))
            ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)))
            ->get();
        $gurus = Guru::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))
            ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)))
            ->get();
        $tahunAjarans = TahunAjaran::when($user->yayasan_id, fn($q) => $q->where('yayasan_id', $user->yayasan_id))->get();

        return view('pengajaran-mapel.create', compact('mapels', 'gurus', 'tahunAjarans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'guru_id' => 'required|exists:gurus,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
        ]);

        $exists = PengajaranMapel::where('mapel_id', $validated['mapel_id'])
            ->where('guru_id', $validated['guru_id'])
            ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->exists();

        if ($exists) {
            return redirect()->route('pengajaran-mapel.index', ['tahun_ajaran_id' => $validated['tahun_ajaran_id']])
                ->withInput()
                ->withErrors(['mapel_id' => 'Guru sudah ditugaskan ke mapel ini untuk tahun ajaran tersebut.']);
        }

        $pengajaran = PengajaranMapel::create($validated);

        LogAktivita::log('create', 'Menugaskan guru ke mapel', $pengajaran);

        return redirect()->route('pengajaran-mapel.index', ['tahun_ajaran_id' => $validated['tahun_ajaran_id']])
            ->with('success', 'Penugasan guru berhasil ditambahkan.');
    }

    public function edit(PengajaranMapel $pengajaranMapel): View
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $mapels = Mapel::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))
            ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)))
            ->get();
        $gurus = Guru::when($lembagaId, fn($q) => $q->where('lembaga_id', $lembagaId))
            ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)))
            ->get();
        $tahunAjarans = TahunAjaran::when($user->yayasan_id, fn($q) => $q->where('yayasan_id', $user->yayasan_id))->get();

        return view('pengajaran-mapel.edit', compact('pengajaranMapel', 'mapels', 'gurus', 'tahunAjarans'));
    }

    public function update(Request $request, PengajaranMapel $pengajaranMapel): RedirectResponse
    {
        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapels,id',
            'guru_id' => 'required|exists:gurus,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
        ]);

        $exists = PengajaranMapel::where('mapel_id', $validated['mapel_id'])
            ->where('guru_id', $validated['guru_id'])
            ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('id', '!=', $pengajaranMapel->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['mapel_id' => 'Guru sudah ditugaskan ke mapel ini untuk tahun ajaran tersebut.']);
        }

        $pengajaranMapel->update($validated);

        LogAktivita::log('update', 'Mengupdate penugasan guru ke mapel', $pengajaranMapel);

        return redirect()->route('pengajaran-mapel.index')->with('success', 'Penugasan guru berhasil diperbarui.');
    }

    public function destroy(PengajaranMapel $pengajaranMapel): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus penugasan guru ke mapel', $pengajaranMapel);
        $pengajaranMapel->delete();

        return redirect()->route('pengajaran-mapel.index')->with('success', 'Penugasan guru berhasil dihapus.');
    }

    /**
     * Import penugasan guru dari file XLSX.
     * Kolom: mapel (nama), guru (nama), tahun_ajaran (nama)
     */
    public function import(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'file' => 'required|file|mimes:xlsx|max:5120',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        array_shift($rows); // Skip header
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $row) {
            $namaMapel = trim($row[0] ?? '');
            $namaGuru = trim($row[1] ?? '');
            $namaTa = trim($row[2] ?? '');

            if (empty($namaMapel) || empty($namaGuru) || empty($namaTa)) {
                $bagian = [];
                if (empty($namaMapel))
                    $bagian[] = 'mapel';
                if (empty($namaGuru))
                    $bagian[] = 'guru';
                if (empty($namaTa))
                    $bagian[] = 'tahun_ajaran';
                $errors[] = 'Baris dilewati: kolom ' . implode(', ', $bagian) . ' kosong';
                $skipped++;
                continue;
            }

            // Resolve mapel
            $mapel = Mapel::where('lembaga_id', $lembagaId)->where('nama', $namaMapel)->first();
            if (!$mapel) {
                $errors[] = "Mapel '{$namaMapel}' tidak ditemukan";
                $skipped++;
                continue;
            }

            // Resolve guru
            $guru = Guru::where('lembaga_id', $lembagaId)->where('nama', $namaGuru)->first();
            if (!$guru) {
                $errors[] = "Guru '{$namaGuru}' tidak ditemukan";
                $skipped++;
                continue;
            }

            // Resolve tahun ajaran
            $ta = TahunAjaran::where('nama', $namaTa)->first();
            if (!$ta) {
                $errors[] = "Tahun Ajaran '{$namaTa}' tidak ditemukan";
                $skipped++;
                continue;
            }

            // Cek duplikasi
            $exists = PengajaranMapel::where('mapel_id', $mapel->id)
                ->where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $ta->id)
                ->exists();

            if ($exists) {
                $errors[] = "Baris dilewati: penugasan {$namaMapel} - {$namaGuru} ({$namaTa}) sudah ada";
                $skipped++;
                continue;
            }

            try {
                PengajaranMapel::create([
                    'mapel_id' => $mapel->id,
                    'guru_id' => $guru->id,
                    'tahun_ajaran_id' => $ta->id,
                ]);
                $created++;
            } catch (\Exception $e) {
                $errors[] = "{$namaMapel}-{$namaGuru}: " . $e->getMessage();
                $skipped++;
            }
        }

        $msg = "Import selesai. {$created} penugasan baru, {$skipped} dilewati.";
        if (!empty($errors)) {
            LogAktivita::log('import', 'Import penugasan XLSX — ' . $msg . ' Errors: ' . implode('; ', array_slice($errors, 0, 5)));
        } else {
            LogAktivita::log('import', 'Import penugasan XLSX — ' . $msg);
        }

        if (!empty($errors)) {
            return redirect()->route('pengajaran-mapel.index')
                ->with('success', $msg)
                ->with('import_errors', $errors);
        }

        return redirect()->route('pengajaran-mapel.index')->with('success', $msg);
    }

    /**
     * Download template XLSX untuk import penugasan guru.
     */
    public function template(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = ['mapel', 'guru', 'tahun_ajaran'];

        return response()->streamDownload(function () use ($headers) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->fromArray([
                ['Matematika', 'Ahmad Fauzi', '2025/2026'],
                ['Bahasa Indonesia', 'Siti Nurhaliza', '2025/2026'],
            ], null, 'A2');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'template-import-penugasan.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
