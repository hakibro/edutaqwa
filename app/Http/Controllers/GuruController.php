<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JenisPtk;
use App\Models\Lembaga;
use App\Models\LogAktivita;
use App\Models\TahunAjaran;
use App\Models\TugasTambahan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GuruController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        $query = Guru::with('lembaga', 'tugasTambahans', 'jenisPtk');

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

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100]))
            $perPage = 10;

        $gurus = $query->latest()->paginate($perPage)->appends($request->except('page'));

        $jenisPtks = JenisPtk::whereIn('lembaga_id', $allowedLembagaIds)->where('is_active', true)->get();
        $tahunAjarans = TahunAjaran::where('is_active', true)->get();

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('guru._table', compact('gurus', 'jenisPtks', 'tahunAjarans'))->render();
            return response()->json(['html' => $html, 'pagination' => $gurus->links()->toHtml()]);
        }

        return view('guru.index', compact('gurus', 'jenisPtks', 'tahunAjarans'));
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
            'tmt' => 'nullable|date',
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
            'tmt' => 'nullable|date',
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
                    TugasTambahan::create([
                        'guru_id' => $guru->id,
                        'jenis' => $tt['jenis'],
                        'keterangan' => $tt['keterangan'] ?? null,
                        'tahun_ajaran_id' => $tt['tahun_ajaran_id'] ?? null,
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
     * Import guru dari file XLSX.
     * Format XLSX: nama,nip,nuptk,jenis_ptk,status_satminkal,tempat_lahir,tanggal_lahir,tmt,alamat,telp,email
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
                    'tmt' => trim($row[7] ?? '') ?: null,
                    'alamat' => trim($row[8] ?? '') ?: null,
                    'telp' => trim($row[9] ?? '') ?: null,
                    'email' => trim($row[10] ?? '') ?: null,
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
        $headers = ['nama', 'nip', 'nuptk', 'jenis_ptk', 'status_satminkal (Ya/Tidak)', 'tempat_lahir', 'tanggal_lahir (YYYY-MM-DD)', 'tmt (YYYY-MM-DD)', 'alamat', 'telp', 'email'];

        return response()->streamDownload(function () use ($headers) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->fromArray([['Ahmad Fauzi', '199001012020011001', '1234567890123456', 'Guru Mapel', 'Ya', 'Jakarta', '1990-01-01', '2026-07-01', 'Jl. Merdeka No. 1', '08123456789', 'ahmad@email.com']], null, 'A2');

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

        $perPage = (int) request()->get('per_page', 10);
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

        if ($guru->status_satminkal && !$guru->kode_guru_satminkal) {
            $updateData['kode_guru_satminkal'] = Guru::generateKodeSatminkal($lembaga);
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

            if ($guru->status_satminkal && !$guru->kode_guru_satminkal) {
                $updateData['kode_guru_satminkal'] = Guru::generateKodeSatminkal($lembaga);
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
}
