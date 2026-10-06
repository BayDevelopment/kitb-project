<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infrastrukturs', function (Blueprint $table): void {
            $table->id();

            // Nama
            $table->string('nama', 150);
            $table->string('nama_en', 150)->nullable();
            $table->string('nama_zh', 150)->nullable();

            // Slug
            $table->string('slug', 180)->unique();

            // Deskripsi
            $table->text('deskripsi')->nullable();
            $table->text('deskripsi_en')->nullable();
            $table->text('deskripsi_zh')->nullable();

            // Media
            $table->string('gambar', 255)->nullable();

            // Sorting & status
            $table->unsignedInteger('urutan')->default(0)->index();
            $table->boolean('aktif')->default(true)->index();

            $table->timestamps();

            $table->index(['aktif', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infrastrukturs');
    }
};
