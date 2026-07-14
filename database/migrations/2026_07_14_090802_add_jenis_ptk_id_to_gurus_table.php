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
        Schema::table('gurus', function (Blueprint $table) {
            $table->foreignId('jenis_ptk_id')->nullable()->after('nuptk')->constrained('jenis_ptks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropForeign(['jenis_ptk_id']);
            $table->dropColumn('jenis_ptk_id');
        });
    }
};
