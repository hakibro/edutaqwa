<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tp_id')->constrained('tps')->cascadeOnDelete();
            $table->integer('minggu_ke');
            $table->text('materi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atps');
    }
};
