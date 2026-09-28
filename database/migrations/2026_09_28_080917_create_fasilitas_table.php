<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fasilitas', function (Blueprint $table): void {
            $table->id();
            $table->string('nama', 150);
            $table->string('slug', 180)->unique();
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['aktif', 'urutan']);
            $table->index('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasilitas');
    }
};
