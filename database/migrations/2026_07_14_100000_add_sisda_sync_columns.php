<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Lembaga: unit_formal untuk mapping ke Sisda API UnitFormal
        Schema::table('lembagas', function (Blueprint $table) {
            $table->string('unit_formal', 50)->nullable()->after('tingkat');
        });

        // Kelas: external_id untuk mapping ke Sisda API idkelasFormal
        Schema::table('kelas', function (Blueprint $table) {
            $table->string('external_id', 50)->nullable()->unique()->after('tingkat');
        });

        // Siswa: external_id untuk mapping ke Sisda API idperson
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('external_id', 50)->nullable()->unique()->after('lembaga_id');
        });

        // Jurusan: external_id opsional untuk mapping dari Sisda
        Schema::table('jurusans', function (Blueprint $table) {
            $table->string('external_id', 50)->nullable()->unique()->after('kode');
        });
    }

    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropColumn('unit_formal');
        });
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('external_id');
        });
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('external_id');
        });
        Schema::table('jurusans', function (Blueprint $table) {
            $table->dropColumn('external_id');
        });
    }
};
