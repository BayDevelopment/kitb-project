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
        Schema::create('fasilitas', function (Blueprint $table): void {
            $table->id();

            // Bahasa Indonesia
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();

            // English
            $table->string('nama_en', 150)->nullable();
            $table->text('deskripsi_en')->nullable();

            // Chinese
            $table->string('nama_zh', 150)->nullable();
            $table->text('deskripsi_zh')->nullable();

            // URL
            $table->string('slug', 180)->unique();

            // Media & status
            $table->string('gambar')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);

            $table->timestamps();

            // Index
            $table->index(['aktif', 'urutan']);
            $table->index('urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fasilitas');
    }
};
