<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // MySQL 8.0 uses the lembaga_id_nis unique index for the FK on lembaga_id.
        // Must drop FK first, then drop unique index, then modify column, then recreate FK.
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropForeign(['lembaga_id']);
            $table->dropUnique(['lembaga_id', 'nis']);
            $table->string('nis', 50)->nullable()->change();
            $table->foreign('lembaga_id')->references('id')->on('lembagas')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropForeign(['lembaga_id']);
            $table->string('nis', 50)->nullable(false)->change();
            $table->unique(['lembaga_id', 'nis']);
            $table->foreign('lembaga_id')->references('id')->on('lembagas')->cascadeOnDelete();
        });
    }
};
