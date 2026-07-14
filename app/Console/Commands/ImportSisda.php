<?php

namespace App\Console\Commands;

use App\Services\SisdaImportService;
use Illuminate\Console\Command;

class ImportSisda extends Command
{
    protected $signature = 'sisda:import {--lembaga= : ID lembaga spesifik}';
    protected $description = 'Import/sync data siswa dari Sisda API';

    public function handle(SisdaImportService $service): int
    {
        $lembagaId = $this->option('lembaga');

        if ($lembagaId) {
            $lembaga = \App\Models\Lembaga::find($lembagaId);
            if (!$lembaga) {
                $this->error("Lembaga ID {$lembagaId} tidak ditemukan.");
                return self::FAILURE;
            }
            $result = $service->syncForLembaga($lembaga);
            $this->info($result['message']);
            if (isset($result['stats'])) {
                $this->table(['Created', 'Updated', 'Skipped', 'Kelas Baru', 'Jurusan Baru'], [
                    [
                        $result['stats']['created'],
                        $result['stats']['updated'],
                        $result['stats']['skipped'],
                        $result['stats']['kelas_created'],
                        $result['stats']['jurusan_created'],
                    ]
                ]);
            }
        } else {
            $results = $service->syncAll();
            foreach ($results as $id => $result) {
                $lembaga = \App\Models\Lembaga::find($id);
                $nama = $lembaga?->nama ?? $id;
                $this->info("[{$nama}] {$result['message']}");
            }
        }

        return self::SUCCESS;
    }
}
