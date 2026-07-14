<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 50)->nullable();
            $table->string('aksi', 100);                        // 'create', 'update', 'delete', 'approve', 'login', dll
            $table->string('deskripsi', 500);                   // 'Admin yayasan menambah lembaga "MI Al-Ihsan"'
            $table->string('model_type', 100)->nullable();       // 'App\Models\Lembaga'
            $table->unsignedBigInteger('model_id')->nullable();  // ID model terkait
            $table->unsignedBigInteger('yayasan_id')->nullable()->constrained('yayasans')->nullOnDelete();
            $table->unsignedBigInteger('lembaga_id')->nullable()->constrained('lembagas')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['yayasan_id', 'created_at']);
            $table->index(['lembaga_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};
