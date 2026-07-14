<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('akademik_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembagas')->cascadeOnDelete();
            $table->string('kunci', 100);
            $table->text('nilai');
            $table->string('label', 255)->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
            $table->unique(['lembaga_id', 'kunci']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akademik_settings');
    }
};
