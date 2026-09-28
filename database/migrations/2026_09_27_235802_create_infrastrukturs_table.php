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

            $table->string('nama', 150);
            $table->string('slug', 180)->unique();

            $table->text('deskripsi')->nullable();

            $table->string('gambar', 255)->nullable();

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
