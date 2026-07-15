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
        Schema::table('lembagas', function (Blueprint $table) {
            $table->string('lokasi_absen', 255)->nullable()->after('unit_formal')->comment('Nama lokasi / alamat titik absen');
            $table->decimal('latitude_absen', 10, 7)->nullable()->after('lokasi_absen');
            $table->decimal('longitude_absen', 10, 7)->nullable()->after('latitude_absen');
            $table->integer('radius_absen_meter')->default(100)->after('longitude_absen')->comment('Radius toleransi GPS dalam meter');
            $table->boolean('wajib_selfie')->default(false)->after('radius_absen_meter')->comment('Guru wajib upload selfie saat check-in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropColumn(['lokasi_absen', 'latitude_absen', 'longitude_absen', 'radius_absen_meter', 'wajib_selfie']);
        });
    }
};
