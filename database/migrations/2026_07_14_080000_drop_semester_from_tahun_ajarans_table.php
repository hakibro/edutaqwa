<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // MySQL 8 won't let us drop the unique index because child FK refs
        // make MySQL hold an internal dependency on it. Simplest: rebuild table.
        Schema::create('tahun_ajarans_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('yayasan_id')->constrained('yayasans')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique(['yayasan_id', 'nama']);
        });

        DB::statement('INSERT INTO tahun_ajarans_new (id, yayasan_id, nama, tanggal_mulai, tanggal_selesai, is_active, created_at, updated_at) SELECT id, yayasan_id, nama, tanggal_mulai, tanggal_selesai, is_active, created_at, updated_at FROM tahun_ajarans');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::drop('tahun_ajarans');
        Schema::rename('tahun_ajarans_new', 'tahun_ajarans');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        Schema::create('tahun_ajarans_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('yayasan_id')->constrained('yayasans')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique(['yayasan_id', 'nama', 'semester']);
        });

        DB::statement('INSERT INTO tahun_ajarans_old (id, yayasan_id, nama, semester, tanggal_mulai, tanggal_selesai, is_active, created_at, updated_at) SELECT id, yayasan_id, nama, \'Ganjil\', tanggal_mulai, tanggal_selesai, is_active, created_at, updated_at FROM tahun_ajarans');

        Schema::drop('tahun_ajarans');
        Schema::rename('tahun_ajarans_old', 'tahun_ajarans');
    }
};
