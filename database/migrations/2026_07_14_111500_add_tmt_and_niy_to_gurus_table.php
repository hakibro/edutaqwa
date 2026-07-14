<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->date('tmt')->nullable()->after('tanggal_lahir');
            $table->string('niy', 20)->nullable()->unique()->after('nuptk');
        });
    }

    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn(['tmt', 'niy']);
        });
    }
};
