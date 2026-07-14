<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembagas')->cascadeOnDelete();
            $table->string('nis', 50);
            $table->string('nisn', 20)->nullable();
            $table->string('nama', 255);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('telp', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('foto', 255)->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('nama_ayah', 255)->nullable();
            $table->string('nama_ibu', 255)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->string('telp_orang_tua', 50)->nullable();
            $table->enum('status', ['aktif', 'alumni', 'pindah', 'keluar', 'dropout'])->default('aktif');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['lembaga_id', 'nis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
