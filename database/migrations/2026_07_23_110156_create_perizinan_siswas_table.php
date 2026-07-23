<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('perizinan_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembagas')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('validator_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('jenis', ['sakit', 'izin']);
            $table->string('keterangan', 255)->nullable();
            $table->boolean('is_applied')->default(false);
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tanggal']);
            $table->index(['lembaga_id', 'tanggal']);
            $table->index(['kelas_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perizinan_siswas');
    }
};
