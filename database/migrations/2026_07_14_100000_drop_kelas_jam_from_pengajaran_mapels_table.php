<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajaran_mapels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kelas_id');
            $table->dropColumn('jam_per_minggu');
        });
    }

    public function down(): void
    {
        Schema::table('pengajaran_mapels', function (Blueprint $table) {
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->cascadeOnDelete();
            $table->integer('jam_per_minggu')->default(0);
        });
    }
};
