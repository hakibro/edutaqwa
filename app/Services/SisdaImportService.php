<?php

namespace App\Services;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\RiwayatKelasSiswa;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SisdaImportService
{
    protected string $baseUrl = 'https://api.daruttaqwa.or.id/sisda/v1';

    /**
     * Sync semua siswa dari Sisda API.
     * Proses per lembaga: ambil semua lembaga yang punya unit_formal,
     * lalu tarik data siswa yang UnitFormal-nya cocok.
     */
    public function syncAll(): array
    {
        $lembagas = Lembaga::whereNotNull('unit_formal')
            ->where('unit_formal', '!=', '')
            ->where('is_active', true)
            ->get();

        $results = [];
        foreach ($lembagas as $lembaga) {
            $results[$lembaga->id] = $this->syncForLembaga($lembaga);
        }

        return $results;
    }

    /**
     * Sync siswa untuk satu lembaga.
     */
    public function syncForLembaga(Lembaga $lembaga): array
    {
        $response = $this->fetchSiswa();
        if (!$response || !isset($response['data'])) {
            return ['success' => false, 'message' => 'Gagal mengambil data dari Sisda API', 'count' => 0];
        }

        $siswas = collect($response['data'])->filter(function ($item) use ($lembaga) {
            return strtoupper($item['UnitFormal'] ?? '') === strtoupper($lembaga->unit_formal);
        });

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $lembaga->yayasan_id)
            ->where('is_active', true)
            ->first();

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'kelas_created' => 0, 'jurusan_created' => 0];

        foreach ($siswas as $data) {
            $this->processSiswa($lembaga, $data, $tahunAjaranAktif, $stats);
        }

        Log::info("Sisda sync: lembaga_id={$lembaga->id} unit={$lembaga->unit_formal}", $stats);

        return [
            'success' => true,
            'message' => "Sync selesai. {$stats['created']} baru, {$stats['updated']} diperbarui, {$stats['skipped']} dilewati.",
            'count' => $siswas->count(),
            'stats' => $stats,
        ];
    }

    protected function fetchSiswa(): ?array
    {
        $url = $this->baseUrl . '/siswa';
        try {
            $response = Http::timeout(30)->get($url);
            if ($response->successful()) {
                return $response->json();
            }
            Log::error('Sisda API error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Sisda API exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    protected function processSiswa(Lembaga $lembaga, array $data, ?TahunAjaran $tahunAjaran, array &$stats): void
    {
        // Pastikan kelas ada (auto-create)
        $kelas = $this->resolveKelas($lembaga, $data, $stats);

        // Siswa: cari by external_id atau nis (pakai idperson sebagai nis)
        $siswa = Siswa::where('lembaga_id', $lembaga->id)
            ->where(function ($q) use ($data) {
                $q->where('external_id', $data['idperson'])
                    ->orWhere('nis', $data['idperson']);
            })
            ->first();

        $siswaData = $this->mapSiswaData($lembaga, $data);

        if ($siswa) {
            $siswa->update($siswaData);
            $stats['updated']++;
        } else {
            $siswa = Siswa::create($siswaData);
            $stats['created']++;
        }

        // Assign ke kelas via riwayat_kelas_siswas (bila ada tahun ajaran aktif & kelas ditemukan)
        if ($kelas && $tahunAjaran && $siswa->wasRecentlyCreated) {
            $this->assignKelas($siswa, $kelas, $tahunAjaran);
        } elseif ($kelas && $tahunAjaran) {
            // Update existing: pastikan riwayat kelas terbaru
            $this->ensureRiwayatKelas($siswa, $kelas, $tahunAjaran);
        }
    }

    protected function resolveKelas(Lembaga $lembaga, array $data, array &$stats): ?Kelas
    {
        if (empty($data['idkelasFormal'])) {
            return null;
        }

        $kelas = Kelas::where('external_id', $data['idkelasFormal'])->first();
        if ($kelas) {
            return $kelas;
        }

        // Cari atau buat jurusan dari nama kelas (misal "11 ALL 1" -> jurusan "ALL")
        $jurusan = null;
        $namaKelas = $data['KelasFormal'] ?? '';

        if ($namaKelas) {
            // Asumsi format: "TINGKAT JURUSAN NOMOR" misal "11 ALL 1", "XI TKJ 3"
            $parts = explode(' ', $namaKelas);
            if (count($parts) >= 2) {
                $namaJurusan = $parts[1]; // ALL, TKJ, IPA, etc
                $jurusan = Jurusan::firstOrCreate(
                    ['lembaga_id' => $lembaga->id, 'nama' => $namaJurusan],
                    ['kode' => strtoupper($namaJurusan)]
                );
                if ($jurusan->wasRecentlyCreated) {
                    $stats['jurusan_created']++;
                }
            }
        }

        $kelas = Kelas::create([
            'lembaga_id' => $lembaga->id,
            'jurusan_id' => $jurusan?->id,
            'nama' => $namaKelas ?: $data['idkelasFormal'],
            'tingkat' => $this->extractTingkat($namaKelas),
            'external_id' => $data['idkelasFormal'],
        ]);
        $stats['kelas_created']++;

        return $kelas;
    }

    protected function extractTingkat(string $namaKelas): string
    {
        $parts = explode(' ', $namaKelas);
        return $parts[0] ?? $namaKelas;
    }

    protected function mapSiswaData(Lembaga $lembaga, array $data): array
    {
        $gender = strtoupper($data['gender'] ?? 'L');
        if (!in_array($gender, ['L', 'P'])) {
            $gender = 'L';
        }

        return [
            'lembaga_id' => $lembaga->id,
            'external_id' => $data['idperson'],
            'nis' => $data['idperson'], // pakai idperson sbg NIS
            'nisn' => null, // Sisda tidak menyediakan NISN
            'nama' => $data['nama'],
            'tempat_lahir' => $data['lahirtempat'] ?? null,
            'tanggal_lahir' => $data['lahirtanggal'] ?? null,
            'jenis_kelamin' => $gender,
            'telp' => $data['phone'] ?? null,
            'status' => ($data['siswa_status'] ?? '1') === '1' ? 'aktif' : 'keluar',
            'is_active' => ($data['person_status'] ?? '1') === '1',
        ];
    }

    protected function assignKelas(Siswa $siswa, Kelas $kelas, TahunAjaran $tahunAjaran): void
    {
        RiwayatKelasSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'tahun_ajaran_id' => $tahunAjaran->id,
            'tanggal_masuk' => now()->toDateString(),
        ]);
    }

    protected function ensureRiwayatKelas(Siswa $siswa, Kelas $kelas, TahunAjaran $tahunAjaran): void
    {
        $existing = RiwayatKelasSiswa::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->whereNull('tanggal_keluar')
            ->first();

        if (!$existing) {
            $this->assignKelas($siswa, $kelas, $tahunAjaran);
        } elseif ($existing->kelas_id !== $kelas->id) {
            // Pindah kelas: tutup yang lama, buat baru
            $existing->update(['tanggal_keluar' => now()->toDateString()]);
            $this->assignKelas($siswa, $kelas, $tahunAjaran);
        }
    }

    /**
     * Sync kenaikan kelas: panggil saat tahun ajaran baru aktif.
     * Ambil data terbaru dari Sisda, update riwayat kelas siswa.
     */
    public function syncKenaikanKelas(Lembaga $lembaga, TahunAjaran $tahunAjaranBaru): array
    {
        $response = $this->fetchSiswa();
        if (!$response || !isset($response['data'])) {
            return ['success' => false, 'message' => 'Gagal mengambil data dari Sisda API'];
        }

        $siswas = collect($response['data'])->filter(function ($item) use ($lembaga) {
            return strtoupper($item['UnitFormal'] ?? '') === strtoupper($lembaga->unit_formal);
        });

        $stats = ['naik_kelas' => 0, 'tetap' => 0, 'tidak_ditemukan' => 0];

        foreach ($siswas as $data) {
            $siswa = Siswa::where('lembaga_id', $lembaga->id)
                ->where('external_id', $data['idperson'])
                ->first();

            if (!$siswa) {
                $stats['tidak_ditemukan']++;
                continue;
            }

            $kelas = $this->resolveKelas($lembaga, $data, $stats);

            if (!$kelas) {
                $stats['tidak_ditemukan']++;
                continue;
            }

            // Cek riwayat tahun ajaran baru
            $existing = RiwayatKelasSiswa::where('siswa_id', $siswa->id)
                ->where('tahun_ajaran_id', $tahunAjaranBaru->id)
                ->first();

            if ($existing) {
                if ($existing->kelas_id !== $kelas->id) {
                    $existing->update(['kelas_id' => $kelas->id, 'tanggal_keluar' => null]);
                    $stats['naik_kelas']++;
                } else {
                    $stats['tetap']++;
                }
            } else {
                // Tutup riwayat tahun lalu
                RiwayatKelasSiswa::where('siswa_id', $siswa->id)
                    ->whereNull('tanggal_keluar')
                    ->update(['tanggal_keluar' => now()->toDateString()]);

                // Buat riwayat baru
                RiwayatKelasSiswa::create([
                    'siswa_id' => $siswa->id,
                    'kelas_id' => $kelas->id,
                    'tahun_ajaran_id' => $tahunAjaranBaru->id,
                    'tanggal_masuk' => now()->toDateString(),
                ]);
                $stats['naik_kelas']++;
            }
        }

        Log::info("Sisda kenaikan kelas: lembaga_id={$lembaga->id}", $stats);

        return [
            'success' => true,
            'message' => "Kenaikan kelas selesai. {$stats['naik_kelas']} naik, {$stats['tetap']} tetap.",
            'stats' => $stats,
        ];
    }
}
