<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kalender_akademiks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('yayasan_id')->constrained('yayasans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('label', 255);             // 'Hari Efektif', 'Libur Nasional', 'Libur Semester', 'PTS', 'PAS', dll
            $table->enum('jenis', ['efektif', 'libur', 'ujian', 'lainnya'])->default('libur');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['yayasan_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalender_akademiks');
    }
};
