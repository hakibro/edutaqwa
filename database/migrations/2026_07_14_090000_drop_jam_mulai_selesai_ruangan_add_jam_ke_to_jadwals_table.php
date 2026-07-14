<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropColumn(['jam_mulai', 'jam_selesai', 'ruangan']);
            $table->unsignedTinyInteger('jam_ke')->after('hari');
        });
    }

    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropColumn('jam_ke');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('ruangan', 100)->nullable();
        });
    }
};
