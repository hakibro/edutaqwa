<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JenisPtk;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Models\TahunAjaran;
use App\Models\TugasTambahan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GuruController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $query = Guru::with('lembaga', 'tugasTambahans', 'jenisPtk');

        if ($user->lembaga_id) {
            $query->where('lembaga_id', $user->lembaga_id);
        } elseif ($user->yayasan_id) {
            $query->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        $gurus = $query->latest()->paginate(10);

        return view('guru.index', compact('gurus'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $lembagas = collect();

        if ($user->lembaga_id) {
            $lembagas = Lembaga::where('id', $user->lembaga_id)->get();
        } elseif ($user->yayasan_id) {
            $lembagas = Lembaga::where('yayasan_id', $user->yayasan_id)->get();
        } else {
            $lembagas = Lembaga::where('is_active', true)->get();
        }

        $lembagaId = $user->lembaga_id ?? $lembagas->first()?->id;
        $jenisPtks = $lembagaId ? JenisPtk::where('lembaga_id', $lembagaId)->where('is_active', true)->get() : collect();
        $tahunAjarans = TahunAjaran::where('is_active', true)->get();

        return view('guru.create', compact('lembagas', 'jenisPtks', 'tahunAjarans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:30',
            'nuptk' => 'nullable|string|max:30',
            'jenis_ptk_id' => 'nullable|exists:jenis_ptks,id',
            'status_satminkal' => 'boolean',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'dokumen.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tugas_tambahan' => 'nullable|array',
            'tugas_tambahan.*.jenis' => 'required_with:tugas_tambahan|string|max:50',
            'tugas_tambahan.*.tahun_ajaran_id' => 'required_with:tugas_tambahan|exists:tahun_ajarans,id',
        ]);

        $lembaga = Lembaga::findOrFail($lembagaId);
        $validated['lembaga_id'] = $lembagaId;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['status_satminkal'] = $request->boolean('status_satminkal');

        // Auto-generate kode guru lembaga
        $validated['kode_guru_lembaga'] = Guru::generateKodeLembaga($lembaga);

        // Jika satminkal, generate kode satminkal juga
        if ($validated['status_satminkal']) {
            $validated['kode_guru_satminkal'] = Guru::generateKodeSatminkal($lembaga);
        }

        // Upload dokumen
        $validated['dokumen'] = $this->handleDokumenUpload($request, null);

        $tugasTambahan = $request->input('tugas_tambahan', []);
        unset($validated['tugas_tambahan']);

        $guru = Guru::create($validated);

        // Simpan tugas tambahan
        foreach ($tugasTambahan as $tt) {
            if (!empty($tt['jenis'])) {
                TugasTambahan::create([
                    'guru_id' => $guru->id,
                    'jenis' => $tt['jenis'],
                    'keterangan' => $tt['keterangan'] ?? null,
                    'tahun_ajaran_id' => $tt['tahun_ajaran_id'],
                    'is_active' => true,
                ]);
            }
        }

        LogAktivita::log('create', 'Menambah guru "' . $guru->nama . '"', $guru);

        return redirect()->route('guru.index')->with('success', 'Guru berhasil ditambahkan. Kode: ' . $validated['kode_guru_lembaga']);
    }

    public function edit(Guru $guru): View
    {
        $user = auth()->user();
        $lembagas = collect();

        if ($user->lembaga_id) {
            $lembagas = Lembaga::where('id', $user->lembaga_id)->get();
        } elseif ($user->yayasan_id) {
            $lembagas = Lembaga::where('yayasan_id', $user->yayasan_id)->get();
        } else {
            $lembagas = Lembaga::where('is_active', true)->get();
        }

        $jenisPtks = JenisPtk::where('lembaga_id', $guru->lembaga_id)->where('is_active', true)->get();
        $tahunAjarans = TahunAjaran::where('is_active', true)->get();

        return view('guru.edit', compact('guru', 'lembagas', 'jenisPtks', 'tahunAjarans'));
    }

    public function update(Request $request, Guru $guru): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:30',
            'nuptk' => 'nullable|string|max:30',
            'jenis_ptk_id' => 'nullable|exists:jenis_ptks,id',
            'status_satminkal' => 'boolean',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'dokumen.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tugas_tambahan' => 'nullable|array',
            'tugas_tambahan.*.jenis' => 'required_with:tugas_tambahan|string|max:50',
            'tugas_tambahan.*.tahun_ajaran_id' => 'required_with:tugas_tambahan|exists:tahun_ajarans,id',
        ]);

        $lembaga = Lembaga::findOrFail($lembagaId);
        $validated['lembaga_id'] = $lembagaId;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['status_satminkal'] = $request->boolean('status_satminkal');

        // Jika baru jadi satminkal & belum punya kode satminkal
        if ($validated['status_satminkal'] && !$guru->kode_guru_satminkal) {
            $validated['kode_guru_satminkal'] = Guru::generateKodeSatminkal($lembaga);
        }

        // Upload dokumen
        $validated['dokumen'] = $this->handleDokumenUpload($request, $guru->dokumen);

        $tugasTambahan = $request->input('tugas_tambahan', []);
        unset($validated['tugas_tambahan']);

        $guru->update($validated);

        // Sync tugas tambahan: hapus lama, simpan baru
        $guru->tugasTambahans()->delete();
        foreach ($tugasTambahan as $tt) {
            if (!empty($tt['jenis'])) {
                TugasTambahan::create([
                    'guru_id' => $guru->id,
                    'jenis' => $tt['jenis'],
                    'keterangan' => $tt['keterangan'] ?? null,
                    'tahun_ajaran_id' => $tt['tahun_ajaran_id'],
                    'is_active' => true,
                ]);
            }
        }

        LogAktivita::log('update', 'Mengupdate guru "' . $guru->nama . '"', $guru);

        return redirect()->route('guru.index')->with('success', 'Guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus guru "' . $guru->nama . '"', $guru);
        $guru->delete();
        return redirect()->route('guru.index')->with('success', 'Guru berhasil dihapus.');
    }

    /**
     * Import guru dari file XLSX.
     * Format XLSX: nama,nip,nuptk,jenis_ptk,status_satminkal,tempat_lahir,tanggal_lahir,alamat,telp,email
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

        $header = array_shift($rows); // Skip header row
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $row) {
            if (count($row) < 2 || empty($row[0]))
                continue; // Skip baris kosong

            $nama = trim($row[0] ?? '');
            if (empty($nama)) {
                $skipped++;
                continue;
            }

            // Cek duplikasi: nama + lembaga
            $exists = Guru::where('lembaga_id', $lembagaId)->where('nama', $nama)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            try {
                Guru::create([
                    'lembaga_id' => $lembagaId,
                    'kode_guru_lembaga' => Guru::generateKodeLembaga($lembaga),
                    'nama' => $nama,
                    'nip' => trim($row[1] ?? '') ?: null,
                    'nuptk' => trim($row[2] ?? '') ?: null,
                    'jenis_ptk' => trim($row[3] ?? '') ?: null,
                    'status_satminkal' => strtolower(trim($row[4] ?? '')) === 'ya',
                    'tempat_lahir' => trim($row[5] ?? '') ?: null,
                    'tanggal_lahir' => trim($row[6] ?? '') ?: null,
                    'alamat' => trim($row[7] ?? '') ?: null,
                    'telp' => trim($row[8] ?? '') ?: null,
                    'email' => trim($row[9] ?? '') ?: null,
                ]);
                $created++;
            } catch (\Exception $e) {
                $errors[] = $nama . ': ' . $e->getMessage();
                $skipped++;
            }
        }

        $msg = "Import selesai. {$created} guru baru, {$skipped} dilewati.";
        if (!empty($errors)) {
            LogAktivita::log('import', 'Import guru XLSX — ' . $msg . ' Errors: ' . implode('; ', array_slice($errors, 0, 5)));
        } else {
            LogAktivita::log('import', 'Import guru XLSX — ' . $msg);
        }

        return redirect()->route('guru.index')->with('success', $msg);
    }

    /**
     * Download template XLSX.
     */
    public function template(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = ['nama', 'nip', 'nuptk', 'jenis_ptk', 'status_satminkal (Ya/Tidak)', 'tempat_lahir', 'tanggal_lahir (YYYY-MM-DD)', 'alamat', 'telp', 'email'];

        return response()->streamDownload(function () use ($headers) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->fromArray([['Ahmad Fauzi', '199001012020011001', '1234567890123456', 'Guru Mapel', 'Ya', 'Jakarta', '1990-01-01', 'Jl. Merdeka No. 1', '08123456789', 'ahmad@email.com']], null, 'A2');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'template-import-guru.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    protected function handleDokumenUpload(Request $request, ?string $existing): ?string
    {
        $files = [];
        $existingFiles = [];

        if ($existing) {
            $existingFiles = json_decode($existing, true) ?? [];
        }

        // Keep existing files if not removed
        $keep = $request->input('keep_dokumen', []);
        foreach ($existingFiles as $idx => $path) {
            if (in_array((string) $idx, $keep)) {
                $files[] = $path;
            }
        }

        // Upload new files
        if ($request->hasFile('dokumen')) {
            foreach ($request->file('dokumen') as $file) {
                $files[] = $file->store('guru/dokumen', 'public');
            }
        }

        return empty($files) ? null : json_encode($files);
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

    /**
     * Halaman approval guru untuk Admin Yayasan.
     */
    public function approval(): View
    {
        $user = auth()->user();
        $query = Guru::with('lembaga.yayasan', 'jenisPtk')
            ->where('is_approved', false)
            ->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));

        $gurus = $query->latest()->paginate(10);

        return view('guru.approval', compact('gurus'));
    }

    /**
     * Setujui guru.
     */
    public function approve(Guru $guru): RedirectResponse
    {
        $user = auth()->user();
        $lembaga = $guru->lembaga;

        // Generate kode satminkal jika status satminkal dan belum ada
        $updateData = [
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $user->id,
        ];

        if ($guru->status_satminkal && !$guru->kode_guru_satminkal) {
            $updateData['kode_guru_satminkal'] = Guru::generateKodeSatminkal($lembaga);
        }

        $guru->update($updateData);

        LogAktivita::log('approve', 'Menyetujui guru "' . $guru->nama . '" (Kode: ' . ($guru->kode_guru_satminkal ?: $guru->kode_guru_lembaga) . ')', $guru);

        return redirect()->route('guru.approval')->with('success', 'Guru "' . $guru->nama . '" telah disetujui.');
    }

    /**
     * Tolak guru (set is_active = false, biarkan is_approved = false).
     */
    public function reject(Request $request, Guru $guru): RedirectResponse
    {
        $request->validate(['alasan' => 'nullable|string|max:500']);

        $guru->update(['is_active' => false, 'is_approved' => false]);

        LogAktivita::log('reject', 'Menolak guru "' . $guru->nama . '" — Alasan: ' . ($request->alasan ?: 'Tidak disebutkan'), $guru);

        return redirect()->route('guru.approval')->with('success', 'Guru "' . $guru->nama . '" telah ditolak.');
    }
}
