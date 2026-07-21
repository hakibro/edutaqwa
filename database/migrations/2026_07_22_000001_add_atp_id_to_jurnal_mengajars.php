<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->foreignId('atp_id')->nullable()->after('kelas_id')
                ->constrained('atps')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->dropForeign(['atp_id']);
            $table->dropColumn('atp_id');
        });
    }
};
