<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jurnal_mengajars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwals')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->integer('pertemuan_ke');
            $table->date('tanggal');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('foto_path')->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->text('materi')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['jadwal_id', 'tanggal']);
        });

        Schema::create('detail_jurnal_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurnal_mengajar_id')->constrained('jurnal_mengajars')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpha', 'terlambat'])->default('hadir');
            $table->string('keterangan', 255)->nullable();

            $table->unique(['jurnal_mengajar_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_jurnal_siswas');
        Schema::dropIfExists('jurnal_mengajars');
    }
};
