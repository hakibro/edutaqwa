<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jam_kerja_lembagas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembagas')->cascadeOnDelete();
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->integer('toleransi_keterlambatan')->default(15);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['lembaga_id', 'hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jam_kerja_lembagas');
    }
};
