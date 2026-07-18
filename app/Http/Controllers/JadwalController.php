<?php

namespace App\Http\Controllers;

use App\Models\AkademikSetting;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Models\Mapel;
use App\Models\PengajaranMapel;
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

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

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
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $guruList = Guru::when($user->lembaga_id, fn($q) => $q->where('lembaga_id', $user->lembaga_id))
            ->when($user->yayasan_id, fn($q) => $q->whereHas('lembaga', fn($ql) => $ql->where('yayasan_id', $user->yayasan_id)))
            ->orderBy('nama')
            ->get();

        // Default grid kelas: from query param or first kelas
        $gridKelasId = $request->filled('grid_kelas_id')
            ? $request->grid_kelas_id
            : $kelasList->first()?->id;

        // Guru filter: find which kelas_ids this guru teaches (for tab highlighting)
        $kelasWithGuru = [];
        $guruNama = null;
        $guruId = $request->filled('guru_id') ? $request->guru_id : null;
        if ($guruId) {
            $guruNama = Guru::find($guruId)?->nama;
            $yayasanId = $user->yayasan_id ?? Lembaga::find($user->lembaga_id)?->yayasan_id;
            $tahunAktif = TahunAjaran::where('yayasan_id', $yayasanId)->where('is_active', true)->first();
            $kelasWithGuru = Jadwal::where('guru_id', $guruId)
                ->where('lembaga_id', $user->lembaga_id)
                ->when($tahunAktif, fn($q) => $q->where('tahun_ajaran_id', $tahunAktif->id))
                ->pluck('kelas_id')
                ->unique()
                ->toArray();
        }

        // Grid view
        $gridView = [];
        $bentrokMap = [];
        $isGuruMode = $request->filled('guru_id');

        if ($gridKelasId) {
            if ($isGuruMode) {
                // Guru mode: fetch ALL jadwal for this guru across all kelas
                $jadwalKelas = Jadwal::with(['mapel', 'guru', 'kelas'])
                    ->where('guru_id', $guruId)
                    ->where('lembaga_id', $user->lembaga_id)
                    ->get()
                    ->groupBy('hari');

                foreach ($hariList as $hari) {
                    $gridView[$hari] = $jadwalKelas->get($hari, collect())->sortBy('jam_ke');
                }

                // Bentrok: all other jadwal in lembaga not taught by this guru
                $allOtherJadwal = Jadwal::with(['mapel', 'guru', 'kelas'])
                    ->where('lembaga_id', $user->lembaga_id)
                    ->where('guru_id', '!=', $guruId)
                    ->get(['guru_id', 'hari', 'jam_ke', 'mapel_id', 'kelas_id']);
                foreach ($allOtherJadwal as $j) {
                    $bentrokMap[$j->guru_id . '|' . $j->hari . '|' . $j->jam_ke] = [
                        'guru_nama' => $j->guru->nama,
                        'mapel_nama' => $j->mapel->nama,
                        'kelas_nama' => $j->kelas->nama,
                    ];
                }
            } else {
                // Single-kelas mode
                $jadwalKelas = Jadwal::with(['mapel', 'guru'])
                    ->where('kelas_id', $gridKelasId)
                    ->where('lembaga_id', $user->lembaga_id)
                    ->get()
                    ->groupBy('hari');

                foreach ($hariList as $hari) {
                    $gridView[$hari] = $jadwalKelas->get($hari, collect())->sortBy('jam_ke');
                }

                // Build bentrok lookup: all jadwal lembaga except this kelas
                $allOtherJadwal = Jadwal::with(['mapel', 'guru', 'kelas'])
                    ->where('lembaga_id', $user->lembaga_id)
                    ->where('kelas_id', '!=', $gridKelasId)
                    ->get(['guru_id', 'hari', 'jam_ke', 'mapel_id', 'kelas_id']);
                foreach ($allOtherJadwal as $j) {
                    $bentrokMap[$j->guru_id . '|' . $j->hari . '|' . $j->jam_ke] = [
                        'guru_nama' => $j->guru->nama,
                        'mapel_nama' => $j->mapel->nama,
                        'kelas_nama' => $j->kelas->nama,
                    ];
                }
            }
        }

        return view('jadwal.index', compact('kelasList', 'guruList', 'hariList', 'gridView', 'gridKelasId', 'timetableLabels', 'bentrokMap', 'kelasWithGuru', 'isGuruMode', 'guruId', 'guruNama'));
    }

    /**
     * Tampilkan jadwal mengajar guru yang login (Jadwal Saya).
     */
    public function jadwalSaya(): View
    {
        $user = auth()->user();
        $guru = \App\Models\Guru::find($user->guru_id);

        abort_if(!$guru, 404, 'Data guru tidak ditemukan.');

        $lembagaId = $user->lembaga_id;
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        // Timetable labels
        $timetableLabels = [];
        if ($lembagaId) {
            foreach ($hariList as $h) {
                $kbmItems = \App\Models\AkademikSetting::getKbmItems($lembagaId, $h);
                foreach ($kbmItems as $n => $item) {
                    $timetableLabels[$h][$n] = $item['label'] . ' (' . $item['jam_mulai'] . '-' . $item['jam_selesai'] . ')';
                }
            }
        }

        $jadwal = \App\Models\Jadwal::with(['kelas', 'mapel'])
            ->where('guru_id', $guru->id)
            ->where('lembaga_id', $lembagaId)
            ->get()
            ->groupBy('hari');

        $gridView = [];
        foreach ($hariList as $hari) {
            $gridView[$hari] = $jadwal->get($hari, collect())->sortBy('jam_ke');
        }

        return view('jadwal.saya', compact('guru', 'hariList', 'gridView', 'timetableLabels'));
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

        // Cek bentrok (warning only, tetap simpan)
        $bentrokMsg = Jadwal::cekBentrok(
            $validated['guru_id'],
            $validated['hari'],
            (int) $validated['jam_ke']
        );

        Jadwal::create($validated);

        LogAktivita::log('create', 'Menambah jadwal ' . $validated['hari'] . ' Jam ' . $validated['jam_ke']);

        $msg = 'Jadwal berhasil ditambahkan.';
        if ($bentrokMsg) {
            $msg .= ' ⚠ ' . $bentrokMsg;
        }
        return redirect()->route('jadwal.index')->with('success', $msg);
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

        // Cek bentrok (warning only, tetap simpan)
        $bentrokMsg = Jadwal::cekBentrok(
            $validated['guru_id'],
            $validated['hari'],
            (int) $validated['jam_ke'],
            $jadwal->id
        );

        $jadwal->update($validated);

        LogAktivita::log('update', 'Mengupdate jadwal');

        $msg = 'Jadwal berhasil diperbarui.';
        if ($bentrokMsg) {
            $msg .= ' ⚠ ' . $bentrokMsg;
        }
        return redirect()->route('jadwal.index')->with('success', $msg);
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus jadwal');
        $jadwal->delete();

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    // === BATCH STORE (grid editor) ===

    /**
     * Batch store/update/delete jadwal entries for one kelas.
     * Returns JSON for AJAX grid editor.
     */
    public function storeBatch(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'entries' => 'required|array',
            'entries.*.hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'entries.*.jam_ke' => 'required|integer|min:1|max:20',
            'entries.*.mapel_id' => 'nullable|exists:mapels,id',
            'entries.*.guru_id' => 'nullable|exists:gurus,id',
        ]);

        $saved = 0;
        $deleted = 0;
        $errors = [];

        foreach ($validated['entries'] as $entry) {
            $hari = $entry['hari'];
            $jamKe = (int) $entry['jam_ke'];
            $mapelId = $entry['mapel_id'] ?? null;
            $guruId = $entry['guru_id'] ?? null;

            // Validate jam_ke is KBM slot
            $kbmItems = AkademikSetting::getKbmItems($lembagaId, $hari);
            if (!isset($kbmItems[$jamKe])) {
                $errors[] = "{$hari} Jam {$jamKe}: bukan slot KBM valid.";
                continue;
            }

            // Delete if both null
            if (!$mapelId && !$guruId) {
                $deleted += Jadwal::where('lembaga_id', $lembagaId)
                    ->where('kelas_id', $validated['kelas_id'])
                    ->where('hari', $hari)
                    ->where('jam_ke', $jamKe)
                    ->delete();
                continue;
            }

            // Need both
            if (!$mapelId || !$guruId) {
                $errors[] = "{$hari} Jam {$jamKe}: mapel dan guru harus diisi keduanya.";
                continue;
            }

            // Check bentrok
            $existing = Jadwal::where('lembaga_id', $lembagaId)
                ->where('kelas_id', $validated['kelas_id'])
                ->where('hari', $hari)
                ->where('jam_ke', $jamKe)
                ->first();

            $bentrokMsg = Jadwal::cekBentrok($guruId, $hari, $jamKe, $existing?->id);

            if ($existing) {
                $existing->update([
                    'mapel_id' => $mapelId,
                    'guru_id' => $guruId,
                    'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
                ]);
            } else {
                Jadwal::create([
                    'lembaga_id' => $lembagaId,
                    'kelas_id' => $validated['kelas_id'],
                    'mapel_id' => $mapelId,
                    'guru_id' => $guruId,
                    'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
                    'hari' => $hari,
                    'jam_ke' => $jamKe,
                ]);
            }
            $saved++;
            if ($bentrokMsg) {
                $errors[] = "{$hari} Jam {$jamKe}: bentrok — {$bentrokMsg} (tetap disimpan)";
            }
        }

        LogAktivita::log('update', "Batch jadwal: {$saved} disimpan, {$deleted} dihapus, " . count($errors) . " error.");

        $msg = "{$saved} jadwal disimpan, {$deleted} dihapus.";
        return response()->json([
            'success' => true,
            'message' => $msg,
            'errors' => $errors,
        ]);
    }

    /**
     * JSON endpoint: return pengajaran mapel pairs for a given kelas.
     * Used by the grid editor inline dropdown.
     */
    public function slotSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
        ]);

        // Get pengajaran_mapel for this lembaga (through mapel relation)
        $pengajaran = PengajaranMapel::with(['mapel', 'guru'])
            ->whereHas('mapel', fn($q) => $q->where('lembaga_id', $lembagaId))
            ->get();

        if ($pengajaran->isNotEmpty()) {
            $slots = $pengajaran->map(fn($p) => [
                'mapel_id' => $p->mapel_id,
                'mapel_nama' => $p->mapel->nama,
                'guru_id' => $p->guru_id,
                'guru_nama' => $p->guru->nama,
                'label' => "{$p->mapel->nama} — {$p->guru->nama}",
            ])->unique(fn($s) => "{$s['mapel_id']}-{$s['guru_id']}")->values();
        } else {
            // Fallback: all mapels and all approved gurus in this lembaga
            $mapels = Mapel::where('lembaga_id', $lembagaId)->get(['id', 'nama']);
            $gurus = Guru::where('lembaga_id', $lembagaId)->where('is_approved', true)->get(['id', 'nama']);
            $slots = collect();
            foreach ($mapels as $m) {
                foreach ($gurus as $g) {
                    $slots->push([
                        'mapel_id' => $m->id,
                        'mapel_nama' => $m->nama,
                        'guru_id' => $g->id,
                        'guru_nama' => $g->nama,
                        'label' => "{$m->nama} — {$g->nama}",
                    ]);
                }
            }
        }

        return response()->json(['slots' => $slots]);
    }

    /**
     * Export semua jadwal lembaga ke Excel (format sama dengan template import).
     * User bisa export → edit → import ulang.
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id;

        $query = Jadwal::with(['kelas', 'mapel', 'guru', 'tahunAjaran'])
            ->where('lembaga_id', $lembagaId)
            ->orderBy('kelas_id')
            ->orderBy('hari')
            ->orderBy('jam_ke');

        $yayasanId = $user->yayasan_id ?? Lembaga::find($lembagaId)?->yayasan_id;
        $tahunAktif = TahunAjaran::where('yayasan_id', $yayasanId)->where('is_active', true)->first();
        if ($tahunAktif) {
            $query->where('tahun_ajaran_id', $tahunAktif->id);
        }

        $jadwals = $query->get();

        $filename = 'export-jadwal-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($jadwals) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header — sama dengan template import
            $sheet->fromArray([['kelas', 'mapel', 'guru', 'hari', 'jam_ke']], null, 'A1');

            $row = 2;
            foreach ($jadwals as $j) {
                $sheet->fromArray([
                    [
                        $j->kelas?->nama ?? '-',
                        $j->mapel?->nama ?? '-',
                        $j->guru?->nama ?? '-',
                        $j->hari,
                        $j->jam_ke,
                    ]
                ], null, "A{$row}");
                $row++;
            }

            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
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

        $headers = array_map('strtolower', $rows[0] ?? []);
        $imported = 0;
        $skipped = 0;
        $errors = [];

        $kelasMap = Kelas::where('lembaga_id', $lembagaId)->pluck('id', 'nama')->toArray();
        $mapelMap = Mapel::where('lembaga_id', $lembagaId)->pluck('id', 'nama')->toArray();
        $guruMap = Guru::where('lembaga_id', $lembagaId)->where('is_approved', true)->pluck('id', 'nama')->toArray();
        $hariValid = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        // 1) Backup jurnal sebelum hapus jadwal (cascade hapus jurnal)
        $jadwalLama = Jadwal::with('jurnalMengajars')
            ->where('lembaga_id', $lembagaId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->get();

        $jurnalBackup = [];
        foreach ($jadwalLama as $j) {
            foreach ($j->jurnalMengajars as $jurnal) {
                $jurnalBackup[] = $jurnal->only([
                    'guru_id',
                    'kelas_id',
                    'pertemuan_ke',
                    'tanggal',
                    'jam_mulai',
                    'jam_selesai',
                    'foto_path',
                    'latitude',
                    'longitude',
                    'materi',
                    'is_verified',
                    'verified_at',
                    'verified_by',
                    'metadata',
                ]);
            }
        }

        // 2) Hapus semua jadwal tahun aktif (cascade hapus jurnal)
        $deleted = Jadwal::where('lembaga_id', $lembagaId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->delete();

        // 3) Insert jadwal dari file
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

            // Cek bentrok (warning only, tetap simpan)
            $bentrok = Jadwal::cekBentrok($guruId, $hariTitle, $jamKe);
            if ($bentrok) {
                $errors[] = "Baris $rowNum: bentrok — $bentrok (tetap disimpan)";
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

        // 4) Reassign jurnal ke jadwal baru (cocok berdasarkan guru_id + kelas_id + mapel_id)
        $reassigned = 0;
        $jurnalGagal = 0;

        if (!empty($jurnalBackup)) {
            $jadwalBaru = Jadwal::where('lembaga_id', $lembagaId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->get()
                ->groupBy(fn($j) => "{$j->guru_id}|{$j->kelas_id}|{$j->mapel_id}");

            foreach ($jurnalBackup as $data) {
                $oldJadwal = $jadwalLama->first(
                    fn($j) =>
                    $j->guru_id == $data['guru_id'] &&
                    $j->jurnalMengajars->contains('tanggal', $data['tanggal'])
                );
                $oldMapelId = $oldJadwal?->mapel_id;

                if (!$oldMapelId) {
                    $jurnalGagal++;
                    $errors[] = "Jurnal tanggal {$data['tanggal']} tidak ditemukan jadwal lamanya.";
                    continue;
                }

                $key = "{$data['guru_id']}|{$data['kelas_id']}|{$oldMapelId}";
                $match = $jadwalBaru->get($key)?->first();

                if ($match) {
                    $data['jadwal_id'] = $match->id;
                    $exist = \App\Models\JurnalMengajar::where('jadwal_id', $match->id)
                        ->where('tanggal', $data['tanggal'])
                        ->exists();
                    if (!$exist) {
                        \App\Models\JurnalMengajar::create($data);
                        $reassigned++;
                    } else {
                        $jurnalGagal++;
                        $errors[] = "Jurnal tanggal {$data['tanggal']} sudah ada di jadwal baru, dilewati.";
                    }
                } else {
                    $jurnalGagal++;
                    $errors[] = "Jurnal tanggal {$data['tanggal']} (guru_id={$data['guru_id']}, kelas_id={$data['kelas_id']}, mapel_id={$oldMapelId}) tidak cocok dengan jadwal baru.";
                }
            }
        }

        LogAktivita::log('import', "Import jadwal: $imported berhasil, $skipped dilewati, $deleted jadwal lama dihapus, $reassigned jurnal di-reassign, $jurnalGagal jurnal gagal.");

        $message = "Import selesai. $imported berhasil, $skipped dilewati. $reassigned jurnal di-reassign.";
        if ($jurnalGagal > 0) {
            $message .= " $jurnalGagal jurnal gagal di-reassign.";
        }
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
