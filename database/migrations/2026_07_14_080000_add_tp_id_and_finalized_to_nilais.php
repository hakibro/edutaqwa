<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->foreignId('tp_id')->nullable()->after('jenis_nilai_id')->constrained('tps')->nullOnDelete();
            $table->boolean('is_finalized')->default(false)->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->dropForeign(['tp_id']);
            $table->dropColumn(['tp_id', 'is_finalized']);
        });
    }
};
