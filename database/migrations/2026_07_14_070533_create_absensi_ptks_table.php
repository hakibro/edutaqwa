<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_ptks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('lembaga_id')->constrained('lembagas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->time('jam_masuk_set')->nullable();
            $table->time('jam_pulang_set')->nullable();
            $table->enum('status', ['tepat_waktu', 'terlambat', 'pulang_awal', 'tidak_absen', 'libur'])->default('tidak_absen');
            $table->integer('keterlambatan_menit')->default(0);
            $table->string('lokasi_check_in', 255)->nullable();
            $table->string('lokasi_check_out', 255)->nullable();
            $table->string('foto_check_in', 255)->nullable();
            $table->string('foto_check_out', 255)->nullable();
            $table->timestamps();
            $table->unique(['guru_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_ptks');
    }
};
