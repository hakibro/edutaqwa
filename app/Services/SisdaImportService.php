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
    protected string $baseUrl = 'https://apiakademik.daruttaqwa.or.id/api';

    /**
     * Sync semua siswa dari API Akademik.
     * Proses per lembaga: ambil semua lembaga yang punya kode_sisda,
     * lalu tarik data kelas per lembaga, lalu siswa per kelas.
     */
    public function syncAll(): array
    {
        $lembagas = Lembaga::whereNotNull('kode_sisda')
            ->where('kode_sisda', '!=', '')
            ->where('is_active', true)
            ->get();

        $results = [];
        foreach ($lembagas as $lembaga) {
            $results[$lembaga->id] = $this->syncForLembaga($lembaga);
        }

        return $results;
    }

    /**
     * Sync siswa untuk satu lembaga via API Akademik.
     * Iterasi kelas dari GET /lembaga/{kode_sisda}/kelas,
     * lalu per kelas ambil siswa dari GET /lembaga/{kode_sisda}/kelas/{idkelas}/siswa.
     * Setelah sync, soft-delete siswa yang tidak ada di API.
     */
    public function syncForLembaga(Lembaga $lembaga): array
    {
        $idunit = $lembaga->kode_sisda;
        if (!$idunit) {
            return ['success' => false, 'message' => 'Lembaga tidak punya kode_sisda', 'count' => 0];
        }

        $kelasList = $this->fetchKelas($idunit);
        if ($kelasList === null) {
            return ['success' => false, 'message' => 'Gagal mengambil data kelas dari API Akademik', 'count' => 0];
        }

        $tahunAjaranAktif = TahunAjaran::where('yayasan_id', $lembaga->yayasan_id)
            ->where('is_active', true)
            ->first();

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'kelas_created' => 0, 'jurusan_created' => 0, 'deleted' => 0];
        $details = ['created' => [], 'updated' => [], 'deleted' => [], 'restored' => []];
        $totalSiswa = 0;
        $idpersonDariApi = [];

        foreach ($kelasList as $kelasData) {
            $idkelas = $kelasData['idkelas'] ?? null;
            if (!$idkelas) {
                continue;
            }

            $siswaList = $this->fetchSiswaByKelas($idunit, $idkelas);
            if ($siswaList === null) {
                continue;
            }

            foreach ($siswaList as $data) {
                $data['_kelas'] = $kelasData;
                $this->processSiswa($lembaga, $data, $tahunAjaranAktif, $stats, $details);
                $totalSiswa++;

                if ($idperson = $data['idperson'] ?? null) {
                    $idpersonDariApi[] = $idperson;
                }
            }
        }

        // Soft-delete siswa lembaga ini yang tidak ada di response API
        $deletedSiswa = Siswa::where('lembaga_id', $lembaga->id)
            ->whereNull('deleted_at')
            ->whereNotIn('idperson', $idpersonDariApi)
            ->whereNotNull('idperson')
            ->get();

        $deletedCount = $deletedSiswa->count();

        if ($deletedCount > 0) {
            foreach ($deletedSiswa as $s) {
                $details['deleted'][] = "{$s->nama} (NISN: " . ($s->nisn ?? '-') . ")";
            }

            Siswa::where('lembaga_id', $lembaga->id)
                ->whereNull('deleted_at')
                ->whereNotIn('idperson', $idpersonDariApi)
                ->whereNotNull('idperson')
                ->update(['is_active' => false]);

            Siswa::where('lembaga_id', $lembaga->id)
                ->whereNull('deleted_at')
                ->whereNotIn('idperson', $idpersonDariApi)
                ->whereNotNull('idperson')
                ->delete();

            $stats['deleted'] = $deletedCount;
        }

        Log::info("Sisda sync: lembaga_id={$lembaga->id} kode_sisda={$idunit}", $stats);

        return [
            'success' => true,
            'message' => "Sync selesai. {$stats['created']} baru, {$stats['updated']} diperbarui, {$stats['skipped']} dilewati, {$stats['deleted']} dihapus.",
            'count' => $totalSiswa,
            'stats' => $stats,
            'details' => $details,
        ];
    }

    protected function fetchKelas(string $idunit): ?array
    {
        $url = $this->baseUrl . '/lembaga/' . $idunit . '/kelas';
        try {
            $response = Http::timeout(30)->get($url);
            if ($response->successful()) {
                $json = $response->json();
                return $json['data'] ?? null;
            }
            Log::error('API Akademik error (kelas)', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('API Akademik exception (kelas)', ['message' => $e->getMessage()]);
            return null;
        }
    }

    protected function fetchSiswaByKelas(string $idunit, string $idkelas): ?array
    {
        $url = $this->baseUrl . '/lembaga/' . $idunit . '/kelas/' . $idkelas . '/siswa';
        try {
            $response = Http::timeout(30)->get($url);
            if ($response->successful()) {
                $json = $response->json();
                return $json['data'] ?? null;
            }
            Log::error('API Akademik error (siswa)', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('API Akademik exception (siswa)', ['message' => $e->getMessage()]);
            return null;
        }
    }

    protected function processSiswa(Lembaga $lembaga, array $data, ?TahunAjaran $tahunAjaran, array &$stats, array &$details = []): void
    {
        // Pastikan kelas ada (auto-create)
        $kelasData = $data['_kelas'] ?? [];
        $kelas = $this->resolveKelas($lembaga, $kelasData, $stats);

        // Siswa: cari by idperson (acuan sync)
        $idperson = $data['idperson'] ?? null;
        if (!$idperson) {
            $stats['skipped']++;
            return;
        }

        $siswa = Siswa::where('lembaga_id', $lembaga->id)
            ->where('idperson', $idperson)
            ->first();

        $siswaData = $this->mapSiswaData($lembaga, $data);
        $needsRiwayat = false;
        $namaSiswa = $siswaData['nama'];
        $nisnSiswa = $siswaData['nisn'] ?? '-';

        if ($siswa) {
            // Jika sebelumnya di-soft-delete, restore
            if ($siswa->trashed()) {
                $siswa->restore();
                $siswa->update(array_merge($siswaData, ['is_active' => true]));
                $needsRiwayat = true;
                $stats['created']++;
                $details['restored'][] = "{$namaSiswa} (NISN: {$nisnSiswa}) — dipulihkan";
            } else {
                // Cek apakah ada perubahan nyata
                $changed = false;
                $current = $siswa->only(array_keys($siswaData));
                foreach ($siswaData as $key => $val) {
                    // Normalize null vs empty string
                    $currentVal = $current[$key] ?? null;
                    if ((string) $currentVal !== (string) $val) {
                        $changed = true;
                        break;
                    }
                }

                if ($changed) {
                    $siswa->update($siswaData);
                    $stats['updated']++;
                    $details['updated'][] = "{$namaSiswa} (NISN: {$nisnSiswa})";
                } else {
                    $stats['skipped']++;
                }
            }
        } else {
            $siswa = Siswa::create($siswaData);
            $needsRiwayat = true;
            $stats['created']++;
            $details['created'][] = "{$namaSiswa} (NISN: {$nisnSiswa})";
        }

        // Assign ke kelas via riwayat_kelas_siswas
        if ($kelas && $tahunAjaran && $needsRiwayat) {
            $tglMasuk = $data['tgl_masuk'] ?? now()->toDateString();
            $this->assignKelas($siswa, $kelas, $tahunAjaran, $tglMasuk);
        } elseif ($kelas && $tahunAjaran) {
            $tglMasuk = $data['tgl_masuk'] ?? now()->toDateString();
            $this->ensureRiwayatKelas($siswa, $kelas, $tahunAjaran, $tglMasuk);
        }
    }

    protected function resolveKelas(Lembaga $lembaga, array $kelasData, array &$stats): ?Kelas
    {
        $idkelas = $kelasData['idkelas'] ?? null;
        if (!$idkelas) {
            return null;
        }

        $kelas = Kelas::where('external_id', $idkelas)->first();
        if ($kelas) {
            return $kelas;
        }

        // Resolve jurusan dari field jurusan API
        $jurusan = null;
        $namaJurusan = $kelasData['jurusan'] ?? null;
        if ($namaJurusan && $namaJurusan !== '-') {
            $jurusan = Jurusan::firstOrCreate(
                ['lembaga_id' => $lembaga->id, 'nama' => $namaJurusan],
                ['kode' => strtoupper($namaJurusan)]
            );
            if ($jurusan->wasRecentlyCreated) {
                $stats['jurusan_created']++;
            }
        }

        $kelas = Kelas::create([
            'lembaga_id' => $lembaga->id,
            'jurusan_id' => $jurusan?->id,
            'nama' => $kelasData['nama'] ?? $idkelas,
            'tingkat' => $kelasData['tingkat'] ?? '',
            'external_id' => $idkelas,
        ]);
        $stats['kelas_created']++;

        return $kelas;
    }

    protected function mapSiswaData(Lembaga $lembaga, array $data): array
    {
        $gender = strtoupper($data['gender'] ?? 'L');
        if (!in_array($gender, ['L', 'P'])) {
            $gender = 'L';
        }

        return [
            'lembaga_id' => $lembaga->id,
            'idperson' => $data['idperson'],
            'nisn' => $data['nisn'] ?? null,
            'nama' => $data['nama'] ?? '',
            'jenis_kelamin' => $gender,
            'status' => 'aktif',
            'is_active' => true,
        ];
    }

    protected function assignKelas(Siswa $siswa, Kelas $kelas, TahunAjaran $tahunAjaran, ?string $tanggalMasuk = null): void
    {
        RiwayatKelasSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'tahun_ajaran_id' => $tahunAjaran->id,
            'tanggal_masuk' => $tanggalMasuk ?? now()->toDateString(),
        ]);
    }

    protected function ensureRiwayatKelas(Siswa $siswa, Kelas $kelas, TahunAjaran $tahunAjaran, ?string $tanggalMasuk = null): void
    {
        $existing = RiwayatKelasSiswa::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->whereNull('tanggal_keluar')
            ->first();

        if (!$existing) {
            $this->assignKelas($siswa, $kelas, $tahunAjaran, $tanggalMasuk);
        } elseif ($existing->kelas_id !== $kelas->id) {
            // Pindah kelas: tutup yang lama, buat baru
            $existing->update(['tanggal_keluar' => now()->toDateString()]);
            $this->assignKelas($siswa, $kelas, $tahunAjaran, $tanggalMasuk);
        }
    }

    /**
     * Sync kenaikan kelas: panggil saat tahun ajaran baru aktif.
     * Ambil data terbaru dari API Akademik, update riwayat kelas siswa.
     */
    public function syncKenaikanKelas(Lembaga $lembaga, TahunAjaran $tahunAjaranBaru): array
    {
        $idunit = $lembaga->kode_sisda;
        if (!$idunit) {
            return ['success' => false, 'message' => 'Lembaga tidak punya kode_sisda'];
        }

        $kelasList = $this->fetchKelas($idunit);
        if ($kelasList === null) {
            return ['success' => false, 'message' => 'Gagal mengambil data kelas dari API Akademik'];
        }

        $stats = ['naik_kelas' => 0, 'tetap' => 0, 'tidak_ditemukan' => 0];

        foreach ($kelasList as $kelasData) {
            $idkelas = $kelasData['idkelas'] ?? null;
            if (!$idkelas) {
                continue;
            }

            $siswaList = $this->fetchSiswaByKelas($idunit, $idkelas);
            if ($siswaList === null) {
                continue;
            }

            foreach ($siswaList as $data) {
                $idperson = $data['idperson'] ?? null;
                if (!$idperson) {
                    $stats['tidak_ditemukan']++;
                    continue;
                }

                $siswa = Siswa::where('lembaga_id', $lembaga->id)
                    ->where('idperson', $idperson)
                    ->first();

                if (!$siswa) {
                    $stats['tidak_ditemukan']++;
                    continue;
                }

                $kelas = $this->resolveKelas($lembaga, $kelasData, $stats);

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

                    $tglMasuk = $data['tgl_masuk'] ?? now()->toDateString();

                    // Buat riwayat baru
                    RiwayatKelasSiswa::create([
                        'siswa_id' => $siswa->id,
                        'kelas_id' => $kelas->id,
                        'tahun_ajaran_id' => $tahunAjaranBaru->id,
                        'tanggal_masuk' => $tglMasuk,
                    ]);
                    $stats['naik_kelas']++;
                }
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
