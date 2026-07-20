<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JenisPtk;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Models\TahunAjaran;
use App\Models\TugasTambahan;
use App\Models\User;
use App\Services\PerPageTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GuruController extends Controller
{
    use PerPageTrait;

    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        $query = Guru::with('lembaga', 'tugasTambahans', 'jenisPtk', 'user');

        // Collect allowed lembaga IDs first
        $allowedLembagaIds = collect();
        if ($user->lembaga_id) {
            $allowedLembagaIds = collect([$user->lembaga_id]);
        } elseif ($user->yayasan_id) {
            $allowedLembagaIds = Lembaga::where('yayasan_id', $user->yayasan_id)->pluck('id');
        } else {
            $allowedLembagaIds = Lembaga::where('is_active', true)->pluck('id');
        }

        $query->whereIn('lembaga_id', $allowedLembagaIds);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('nuptk', 'like', "%{$search}%")
                    ->orWhere('niy', 'like', "%{$search}%")
                    ->orWhere('kode_guru_lembaga', 'like', "%{$search}%");
            });
        }

        // Filter status satminkal
        if ($request->has('status_satminkal') && $request->input('status_satminkal') !== '') {
            $query->where('status_satminkal', (int) $request->input('status_satminkal'));
        }

        // Filter TMT range
        if ($tmtFrom = $request->input('tmt_from')) {
            $query->whereDate('tmt', '>=', $tmtFrom);
        }
        if ($tmtTo = $request->input('tmt_to')) {
            $query->whereDate('tmt', '<=', $tmtTo);
        }

        $perPage = $this->perPage($request, 10);
        $gurus = $query->latest()->paginate($perPage)->appends($request->except('page'));

        $jenisPtks = JenisPtk::whereIn('lembaga_id', $allowedLembagaIds)->where('is_active', true)->get();
        $tahunAjarans = TahunAjaran::where('is_active', true)->get();

        $kelasOptions = \App\Models\Kelas::whereIn('lembaga_id', $gurus->pluck('lembaga_id')->unique())
            ->orderBy('nama')->get(['id', 'nama', 'lembaga_id']);

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('guru._table', compact('gurus', 'jenisPtks', 'tahunAjarans'))->render();
            return response()->json(['html' => $html, 'pagination' => $gurus->links()->toHtml()]);
        }

        return view('guru.index', compact('gurus', 'jenisPtks', 'tahunAjarans', 'kelasOptions'));
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
        $kelas = $lembagaId ? \App\Models\Kelas::where('lembaga_id', $lembagaId)->orderBy('nama')->get() : collect();

        return view('guru.create', compact('lembagas', 'jenisPtks', 'tahunAjarans', 'kelas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        // Filter baris kosong dari tugas_tambahan
        $tugasTambahanRaw = $request->input('tugas_tambahan', []);
        $tugasTambahanFiltered = array_values(array_filter($tugasTambahanRaw, fn($tt) => !empty($tt['jenis'])));
        $request->merge(['tugas_tambahan' => $tugasTambahanFiltered]);

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:30',
            'nuptk' => 'nullable|string|max:30',
            'kode_guru_lembaga' => 'required|string|max:50|unique:gurus,kode_guru_lembaga',
            'kode_guru_satminkal' => 'nullable|string|max:50|unique:gurus,kode_guru_satminkal',
            'jenis_ptk_id' => 'nullable|exists:jenis_ptks,id',
            'status_satminkal' => 'boolean',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'tmt' => 'required|date',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'dokumen.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tugas_tambahan' => 'nullable|array',
            'tugas_tambahan.*.jenis' => 'required_with:tugas_tambahan|string|max:50',
            'tugas_tambahan.*.tahun_ajaran_id' => 'required_with:tugas_tambahan|exists:tahun_ajarans,id',
            'tugas_tambahan.*.kelas_id' => 'nullable|exists:kelas,id',
        ]);

        $lembaga = Lembaga::findOrFail($lembagaId);
        $validated['lembaga_id'] = $lembagaId;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['status_satminkal'] = $request->boolean('status_satminkal');

        // Upload dokumen
        $validated['dokumen'] = $this->handleDokumenUpload($request, null);

        $tugasTambahan = $request->input('tugas_tambahan', []);
        unset($validated['tugas_tambahan']);

        try {
            $guru = Guru::create($validated);

            // Simpan tugas tambahan
            foreach ($tugasTambahan as $tt) {
                if (!empty($tt['jenis'])) {
                    $this->validateWaliKelasUnique($lembagaId, $tt);
                    TugasTambahan::create([
                        'guru_id' => $guru->id,
                        'jenis' => $tt['jenis'],
                        'keterangan' => $tt['keterangan'] ?? null,
                        'tahun_ajaran_id' => $tt['tahun_ajaran_id'],
                        'kelas_id' => ($tt['jenis'] === 'Wali Kelas') ? ($tt['kelas_id'] ?? null) : null,
                        'is_active' => true,
                    ]);
                }
            }

            LogAktivita::log('create', 'Menambah guru "' . $guru->nama . '"', $guru);

            return redirect()->route('guru.index')->with('success', 'Guru berhasil ditambahkan. Kode: ' . $validated['kode_guru_lembaga']);
        } catch (\Exception $e) {
            // Hapus dokumen yang sudah terupload jika guru gagal dibuat
            if (!empty($validated['dokumen'])) {
                foreach ($validated['dokumen'] as $file) {
                    \Illuminate\Support\Facades\Storage::delete($file);
                }
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan guru: ' . $e->getMessage());
        }
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
        $kelas = \App\Models\Kelas::where('lembaga_id', $guru->lembaga_id)->orderBy('nama')->get();

        return view('guru.edit', compact('guru', 'lembagas', 'jenisPtks', 'tahunAjarans', 'kelas'));
    }

    public function update(Request $request, Guru $guru): RedirectResponse
    {
        $user = auth()->user();
        $lembagaId = $user->lembaga_id ?? $request->lembaga_id;

        // Filter baris kosong dari tugas_tambahan
        $tugasTambahanRaw = $request->input('tugas_tambahan', []);
        $tugasTambahanFiltered = array_values(array_filter($tugasTambahanRaw, fn($tt) => !empty($tt['jenis'])));
        $request->merge(['tugas_tambahan' => $tugasTambahanFiltered]);

        $validated = $request->validate([
            'lembaga_id' => $user->lembaga_id ? 'nullable' : 'required|exists:lembagas,id',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:30',
            'nuptk' => 'nullable|string|max:30',
            'kode_guru_lembaga' => 'required|string|max:50|unique:gurus,kode_guru_lembaga,' . $guru->id,
            'kode_guru_satminkal' => 'nullable|string|max:50|unique:gurus,kode_guru_satminkal,' . $guru->id,
            'jenis_ptk_id' => 'nullable|exists:jenis_ptks,id',
            'status_satminkal' => 'boolean',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'tmt' => 'nullable|date',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'dokumen.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tugas_tambahan' => 'nullable|array',
            'tugas_tambahan.*.jenis' => 'required_with:tugas_tambahan|string|max:50',
            'tugas_tambahan.*.tahun_ajaran_id' => 'required_with:tugas_tambahan|exists:tahun_ajarans,id',
            'tugas_tambahan.*.kelas_id' => 'nullable|exists:kelas,id',
        ]);

        $lembaga = Lembaga::findOrFail($lembagaId);
        $validated['lembaga_id'] = $lembagaId;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['status_satminkal'] = $request->boolean('status_satminkal');

        // Upload dokumen
        $validated['dokumen'] = $this->handleDokumenUpload($request, $guru->dokumen);

        $tugasTambahan = $request->input('tugas_tambahan', []);
        unset($validated['tugas_tambahan']);

        try {
            $guru->update($validated);

            // Sync tugas tambahan: hapus lama, simpan baru
            $guru->tugasTambahans()->delete();
            foreach ($tugasTambahan as $tt) {
                if (!empty($tt['jenis'])) {
                    $this->validateWaliKelasUnique($lembagaId, $tt, $guru->id);
                    TugasTambahan::create([
                        'guru_id' => $guru->id,
                        'jenis' => $tt['jenis'],
                        'keterangan' => $tt['keterangan'] ?? null,
                        'tahun_ajaran_id' => $tt['tahun_ajaran_id'],
                        'kelas_id' => ($tt['jenis'] === 'Wali Kelas') ? ($tt['kelas_id'] ?? null) : null,
                        'is_active' => true,
                    ]);
                }
            }

            LogAktivita::log('update', 'Mengupdate guru "' . $guru->nama . '"', $guru);

            return redirect()->route('guru.index')->with('success', 'Guru berhasil diperbarui.');
        } catch (\Exception $e) {
            // Hapus dokumen yang sudah terupload jika update gagal
            if (!empty($validated['dokumen'])) {
                foreach ($validated['dokumen'] as $file) {
                    \Illuminate\Support\Facades\Storage::delete($file);
                }
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui guru: ' . $e->getMessage());
        }
    }

    public function destroy(Guru $guru): RedirectResponse
    {
        LogAktivita::log('delete', 'Menghapus guru "' . $guru->nama . '"', $guru);
        $guru->delete();
        return redirect()->route('guru.index')->with('success', 'Guru berhasil dihapus.');
    }

    /**
     * Inline update dari dropdown di tabel index.
     */
    public function inlineUpdate(Request $request, Guru $guru): JsonResponse
    {
        $field = $request->input('field');
        $value = $request->input('value');

        if ($field === 'jenis_ptk_id') {
            $guru->update(['jenis_ptk_id' => $value ?: null]);
            LogAktivita::log('update', 'Update jenis PTK guru "' . $guru->nama . '"', $guru);
            return response()->json(['success' => true]);
        }

        if ($field === 'tugas_tambahan') {
            $tugasTambahan = $request->input('tugas_tambahan', []);
            $guru->tugasTambahans()->delete();
            foreach ($tugasTambahan as $tt) {
                if (!empty($tt['jenis'])) {
                    $this->validateWaliKelasUnique($guru->lembaga_id, $tt, $guru->id);
                    TugasTambahan::create([
                        'guru_id' => $guru->id,
                        'jenis' => $tt['jenis'],
                        'keterangan' => $tt['keterangan'] ?? null,
                        'tahun_ajaran_id' => $tt['tahun_ajaran_id'] ?? null,
                        'kelas_id' => ($tt['jenis'] === 'Wali Kelas') ? ($tt['kelas_id'] ?? null) : null,
                        'is_active' => true,
                    ]);
                }
            }
            LogAktivita::log('update', 'Update tugas tambahan guru "' . $guru->nama . '"', $guru);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Field tidak dikenali.'], 400);
    }

    /**
     * Import / update guru dari file XLSX.
     * Jika kolom ID (kolom ke-2) berisi ID guru yang valid → update data guru tersebut.
     * Jika ID kosong → tambah guru baru.
     * Format XLSX: no, id, kode_lembaga, kode_satminkal, niy, nama, nip, nuptk, lembaga, jenis_ptk, tugas_tambahan, status_satminkal, tempat_lahir, tanggal_lahir, tmt, alamat, telp, email, status
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
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $row) {
            // Skip empty rows
            $nama = trim($row[5] ?? $row[4] ?? ''); // nama di kolom 5 (export) atau 0 (template)
            if (empty($nama)) {
                $skipped++;
                continue;
            }

            $guruId = trim((string) ($row[1] ?? ''));

            // Jika ada ID valid → update
            if (!empty($guruId) && is_numeric($guruId)) {
                $guru = Guru::where('id', (int) $guruId)
                    ->where('lembaga_id', $lembagaId)
                    ->first();

                if (!$guru) {
                    $skipped++;
                    continue;
                }

                try {
                    $guru->update([
                        'kode_guru_lembaga' => trim($row[2] ?? '') ?: null,
                        'kode_guru_satminkal' => trim($row[3] ?? '') ?: null,
                        'nama' => $nama,
                        'nip' => trim($row[6] ?? '') ?: null,
                        'nuptk' => trim($row[7] ?? '') ?: null,
                        'jenis_ptk' => trim($row[9] ?? '') ?: null,
                        'status_satminkal' => str_contains(strtolower(trim($row[11] ?? '')), 'satminkal'),
                        'tempat_lahir' => trim($row[12] ?? '') ?: null,
                        'tanggal_lahir' => trim($row[13] ?? '') ?: null,
                        'tmt' => trim($row[14] ?? '') ?: null,
                        'alamat' => trim($row[15] ?? '') ?: null,
                        'telp' => trim($row[16] ?? '') ?: null,
                        'email' => trim($row[17] ?? '') ?: null,
                        'is_active' => strtolower(trim($row[18] ?? '')) === 'aktif',
                    ]);
                    $updated++;
                } catch (\Exception $e) {
                    $errors[] = $nama . ': ' . $e->getMessage();
                    $skipped++;
                }
                continue;
            }

            // Tidak ada ID → tambah baru (format template lama: 0=nama, 1=nip, 2=nuptk, 3=jenis_ptk, 4=status_satminkal, ..., 11=kode_guru_lembaga, 12=kode_guru_satminkal)
            $nama = trim($row[0] ?? ''); // override nama dari kolom 0 untuk format template
            if (empty($nama)) {
                $skipped++;
                continue;
            }

            // Cek duplikasi via kode_guru_lembaga
            $kodeLembaga = trim($row[11] ?? '') ?: null;
            if ($kodeLembaga) {
                $exists = Guru::where('lembaga_id', $lembagaId)
                    ->where('kode_guru_lembaga', $kodeLembaga)
                    ->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }
            } else {
                // Fallback: cek nama + lembaga
                $exists = Guru::where('lembaga_id', $lembagaId)->where('nama', $nama)->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }
            }

            try {
                Guru::create([
                    'lembaga_id' => $lembagaId,
                    'kode_guru_lembaga' => $kodeLembaga,
                    'nama' => $nama,
                    'nip' => trim($row[1] ?? '') ?: null,
                    'nuptk' => trim($row[2] ?? '') ?: null,
                    'jenis_ptk' => trim($row[3] ?? '') ?: null,
                    'status_satminkal' => strtolower(trim($row[4] ?? '')) === 'ya',
                    'tempat_lahir' => trim($row[5] ?? '') ?: null,
                    'tanggal_lahir' => trim($row[6] ?? '') ?: null,
                    'tmt' => trim($row[7] ?? '') ?: null,
                    'alamat' => trim($row[8] ?? '') ?: null,
                    'telp' => trim($row[9] ?? '') ?: null,
                    'email' => trim($row[10] ?? '') ?: null,
                    'kode_guru_satminkal' => trim($row[12] ?? '') ?: null,
                ]);
                $created++;
            } catch (\Exception $e) {
                $errors[] = $nama . ': ' . $e->getMessage();
                $skipped++;
            }
        }

        $msg = "Import selesai. {$created} guru baru, {$updated} diperbarui, {$skipped} dilewati.";
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
        $headers = ['nama', 'nip', 'nuptk', 'jenis_ptk', 'status_satminkal (Ya/Tidak)', 'tempat_lahir', 'tanggal_lahir (YYYY-MM-DD)', 'tmt (YYYY-MM-DD)', 'alamat', 'telp', 'email', 'kode_guru_lembaga', 'kode_guru_satminkal'];

        return response()->streamDownload(function () use ($headers) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->fromArray([['Ahmad Fauzi', '199001012020011001', '1234567890123456', 'Guru Mapel', 'Ya', 'Jakarta', '1990-01-01', '2026-07-01', 'Jl. Merdeka No. 1', '08123456789', 'ahmad@email.com', 'SMA.001', 'YYS.SMA.001']], null, 'A2');

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

        $perPage = $this->perPage(request(), 10);
        $gurus = $query->latest()->paginate($perPage)->withQueryString();

        return view('guru.approval', compact('gurus', 'perPage'));
    }

    /**
     * Setujui guru.
     */
    public function approve(Guru $guru): RedirectResponse
    {
        $admin = auth()->user();
        $lembaga = $guru->lembaga;

        $updateData = [
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ];

        // Generate NIY saat approve (jika belum punya dan TMT sudah diisi)
        if (!$guru->niy && $guru->tmt) {
            $updateData['niy'] = Guru::generateNiy($lembaga, $guru->tmt->format('Y-m-d'));
        }

        $guru->update($updateData);

        // Refresh guru agar niy terbaca
        $guru->refresh();

        // Buat akun user untuk guru jika belum ada — username=NIY, password=NIY
        $niy = $guru->niy;
        $user = User::where('guru_id', $guru->id)->first();

        if (!$user) {
            $email = $niy . '@daruttaqwa.or.id';

            User::create([
                'guru_id' => $guru->id,
                'lembaga_id' => $guru->lembaga_id,
                'yayasan_id' => $lembaga->yayasan_id,
                'name' => $guru->nama,
                'username' => $niy,
                'email' => $email,
                'password' => $niy,
                'role' => 'guru',
                'is_active' => true,
                'must_change_password' => true,
            ]);
        } else {
            $user->update([
                'is_active' => true,
                'must_change_password' => true,
                'username' => $user->username ?? $niy,
            ]);
        }

        LogAktivita::log('approve', 'Menyetujui guru "' . $guru->nama . '" (NIY: ' . $niy . ')', $guru);

        return redirect()->route('guru.approval')->with('success', 'Guru "' . $guru->nama . '" telah disetujui. Username: ' . $niy . ' | Password: ' . $niy);
    }

    /**
     * Setujui banyak guru sekaligus.
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:gurus,id',
        ]);

        $admin = auth()->user();
        $count = 0;

        $gurus = Guru::with('lembaga')->whereIn('id', $request->ids)->get();

        foreach ($gurus as $guru) {
            if ($guru->is_approved)
                continue;

            $lembaga = $guru->lembaga;
            $updateData = [
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ];

            if (!$guru->niy && $guru->tmt) {
                $updateData['niy'] = Guru::generateNiy($lembaga, $guru->tmt->format('Y-m-d'));
            }

            $guru->update($updateData);
            $guru->refresh();

            $niy = $guru->niy;
            $user = User::where('guru_id', $guru->id)->first();

            if (!$user) {
                $email = $niy . '@daruttaqwa.or.id';

                User::create([
                    'guru_id' => $guru->id,
                    'lembaga_id' => $guru->lembaga_id,
                    'yayasan_id' => $lembaga->yayasan_id,
                    'name' => $guru->nama,
                    'username' => $niy,
                    'email' => $email,
                    'password' => $niy,
                    'role' => 'guru',
                    'is_active' => true,
                    'must_change_password' => true,
                ]);
            } else {
                $user->update([
                    'is_active' => true,
                    'must_change_password' => true,
                    'username' => $user->username ?? $niy,
                ]);
            }

            LogAktivita::log('approve', 'Menyetujui guru "' . $guru->nama . '" (NIY: ' . $niy . ')', $guru);
            $count++;
        }

        return redirect()->route('guru.approval')->with('success', $count . ' guru berhasil disetujui.');
    }

    /**
     * Bulk update status guru (activate/deactivate).
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:gurus,id',
            'action' => 'required|in:activate,deactivate',
        ]);

        $isActive = $request->input('action') === 'activate';
        $count = Guru::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        $label = $isActive ? 'diaktifkan' : 'dinonaktifkan';
        LogAktivita::log('bulk', $count . ' guru ' . $label . ' secara massal');

        return redirect()->route('guru.index')->with('success', $count . ' guru ' . $label . '.');
    }

    /**
     * Bulk hapus guru.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:gurus,id',
        ]);

        $gurus = Guru::whereIn('id', $request->ids)->get();
        $count = $gurus->count();

        foreach ($gurus as $guru) {
            LogAktivita::log('delete', 'Menghapus guru "' . $guru->nama . '" (bulk)', $guru);
            $guru->delete();
        }

        return redirect()->route('guru.index')->with('success', $count . ' guru berhasil dihapus.');
    }

    /**
     * Tolak banyak guru sekaligus.
     */
    public function bulkReject(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:gurus,id',
        ]);

        $count = Guru::whereIn('id', $request->ids)
            ->where('is_approved', false)
            ->update(['is_active' => false, 'is_approved' => false]);

        LogAktivita::log('reject', 'Menolak ' . $count . ' guru secara massal');

        return redirect()->route('guru.approval')->with('success', $count . ' guru ditolak.');
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

    /**
     * Export guru ke XLSX.
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();
        $query = Guru::with('lembaga', 'jenisPtk', 'tugasTambahans.tahunAjaran');

        // Scope lembaga sama seperti index
        if ($user->lembaga_id) {
            $query->where('lembaga_id', $user->lembaga_id);
        } elseif ($user->yayasan_id) {
            $query->whereHas('lembaga', fn($q) => $q->where('yayasan_id', $user->yayasan_id));
        }

        // Apply filters same as index
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('nuptk', 'like', "%{$search}%")
                    ->orWhere('niy', 'like', "%{$search}%")
                    ->orWhere('kode_guru_lembaga', 'like', "%{$search}%");
            });
        }
        if (request()->has('status_satminkal') && request('status_satminkal') !== '') {
            $query->where('status_satminkal', (int) request('status_satminkal'));
        }
        if ($tmtFrom = request('tmt_from')) {
            $query->whereDate('tmt', '>=', $tmtFrom);
        }
        if ($tmtTo = request('tmt_to')) {
            $query->whereDate('tmt', '<=', $tmtTo);
        }

        $gurus = $query->latest()->get();

        $filename = 'export-guru-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($gurus) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = [
                'No',
                'ID',
                'Kode Lembaga',
                'Kode Satminkal',
                'NIY',
                'Nama',
                'NIP',
                'NUPTK',
                'Lembaga',
                'Jenis PTK',
                'Tugas Tambahan',
                'Status Satminkal',
                'Tempat Lahir',
                'Tanggal Lahir',
                'TMT',
                'Alamat',
                'Telp',
                'Email',
                'Status',
            ];
            $sheet->fromArray([$headers], null, 'A1');

            $row = 2;
            foreach ($gurus as $i => $g) {
                $tugasTambahan = $g->tugasTambahans
                    ->map(fn($tt) => $tt->jenis . ($tt->keterangan ? " ({$tt->keterangan})" : '') . ($tt->tahunAjaran ? ' TA:' . $tt->tahunAjaran->nama : ''))
                    ->implode(', ');

                $sheet->fromArray([
                    [
                        $i + 1,
                        $g->id,
                        $g->kode_guru_lembaga,
                        $g->kode_guru_satminkal,
                        $g->niy,
                        $g->nama,
                        $g->nip,
                        $g->nuptk,
                        $g->lembaga?->nama,
                        $g->jenisPtk?->nama,
                        $tugasTambahan ?: '-',
                        $g->status_satminkal ? 'Satminkal' : 'Non-Satminkal',
                        $g->tempat_lahir,
                        $g->tanggal_lahir?->format('Y-m-d'),
                        $g->tmt?->format('Y-m-d'),
                        $g->alamat,
                        $g->telp,
                        $g->email,
                        $g->is_active ? 'Aktif' : 'Nonaktif',
                    ],
                ], null, "A{$row}");
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'S') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * Form reset password user guru.
     */
    public function resetPasswordForm(Guru $guru): View
    {
        $user = User::where('guru_id', $guru->id)->first();

        return view('guru.reset-password', compact('guru', 'user'));
    }

    /**
     * Proses reset password user guru.
     */
    public function resetPassword(Request $request, Guru $guru): RedirectResponse
    {
        $user = User::where('guru_id', $guru->id)->first();

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!$user) {
            // Buat akun baru jika belum ada
            $niy = $guru->niy ?: $guru->kode_guru_lembaga;
            $email = $niy . '@daruttaqwa.or.id';

            $user = User::create([
                'guru_id' => $guru->id,
                'lembaga_id' => $guru->lembaga_id,
                'yayasan_id' => $guru->lembaga?->yayasan_id,
                'name' => $guru->nama,
                'username' => $niy,
                'email' => $email,
                'password' => bcrypt($validated['password']),
                'role' => 'guru',
                'is_active' => true,
                'must_change_password' => true,
            ]);

            LogAktivita::log('create', 'Reset password: akun guru "' . $guru->nama . '" dibuat (sebelumnya belum ada)', $guru);

            return redirect()->route('guru.index')
                ->with('success', 'Akun user untuk guru "' . $guru->nama . '" berhasil dibuat. Password telah diset.');
        }

        $user->update([
            'password' => bcrypt($validated['password']),
            'must_change_password' => true,
            'is_active' => true,
        ]);

        LogAktivita::log('update', 'Reset password user guru "' . $guru->nama . '"', $guru);

        return redirect()->route('guru.index')
            ->with('success', 'Password user guru "' . $guru->nama . '" berhasil direset.');
    }

    /**
     * Validasi bahwa 1 kelas hanya boleh punya 1 Wali Kelas per tahun ajaran.
     */
    private function validateWaliKelasUnique(int $lembagaId, array $tt, ?int $exceptGuruId = null): void
    {
        if ($tt['jenis'] !== 'Wali Kelas' || empty($tt['kelas_id'])) {
            return;
        }

        $exists = TugasTambahan::where('jenis', 'Wali Kelas')
            ->where('kelas_id', $tt['kelas_id'])
            ->where('tahun_ajaran_id', $tt['tahun_ajaran_id'])
            ->where('is_active', true)
            ->when($exceptGuruId, fn($q) => $q->whereHas('guru', fn($q) => $q->where('id', '!=', $exceptGuruId)))
            ->exists();

        if ($exists) {
            abort(422, 'Kelas ini sudah memiliki Wali Kelas untuk tahun ajaran tersebut.');
        }
    }
}
