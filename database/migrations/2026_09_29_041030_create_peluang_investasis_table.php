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
        Schema::create('peluang_investasis', function (Blueprint $table): void {
            $table->id();

            // Bahasa Indonesia
            $table->string('judul');
            $table->string('sektor_industri')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('lokasi')->nullable();

            // English
            $table->string('judul_en')->nullable();
            $table->string('sektor_industri_en')->nullable();
            $table->text('deskripsi_en')->nullable();
            $table->string('lokasi_en')->nullable();

            // Chinese
            $table->string('judul_zh')->nullable();
            $table->string('sektor_industri_zh')->nullable();
            $table->text('deskripsi_zh')->nullable();
            $table->string('lokasi_zh')->nullable();

            // General
            $table->string('slug')->unique();

            $table->decimal('luas_lahan', 15, 2)->nullable();
            $table->string('satuan_luas', 20)->default('Ha');

            $table->string('status', 30)
                ->default('tersedia')
                ->index();

            $table->decimal('nilai_investasi', 20, 2)->nullable();
            $table->string('mata_uang', 10)->default('IDR');

            $table->string('gambar')->nullable();

            $table->unsignedInteger('urutan')
                ->default(0)
                ->index();

            $table->boolean('aktif')
                ->default(true)
                ->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['aktif', 'urutan']);
            $table->index(['status', 'aktif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peluang_investasis');
    }
};
