<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->boolean('is_draft')->default(true)->after('metadata');
            $table->tinyInteger('draft_step')->default(0)->after('is_draft');
        });

        // Drop unique constraint — buat index biasa dulu utk FK
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->index('jadwal_id', 'jurnal_mengajars_jadwal_id_index');
        });

        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->dropUnique('jurnal_mengajars_jadwal_id_tanggal_unique');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->unique(['jadwal_id', 'tanggal'], 'jurnal_mengajars_jadwal_id_tanggal_unique');
        });

        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->dropIndex('jurnal_mengajars_jadwal_id_index');
        });

        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->dropColumn(['is_draft', 'draft_step']);
        });
    }
};
