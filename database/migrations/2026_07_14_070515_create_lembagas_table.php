<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lembagas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('yayasan_id')->constrained('yayasans')->cascadeOnDelete();
            $table->string('nama', 255);
            $table->string('kode', 50);
            $table->string('npsn', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('telp', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('tingkat', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['yayasan_id', 'kode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lembagas');
    }
};
