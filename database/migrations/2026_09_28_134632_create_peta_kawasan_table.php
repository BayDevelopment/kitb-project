<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peta_kawasan', function (Blueprint $table): void {
            $table->id();

            // Bahasa Indonesia
            $table->string('nama');
            $table->text('deskripsi')->nullable();

            // English
            $table->string('nama_en')->nullable();
            $table->text('deskripsi_en')->nullable();

            // Chinese
            $table->string('nama_zh')->nullable();
            $table->text('deskripsi_zh')->nullable();

            // General
            $table->string('slug')->unique();
            $table->string('gambar')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);

            $table->timestamps();

            $table->index(['aktif', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peta_kawasan');
    }
};
