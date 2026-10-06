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
        Schema::create('struktur_perusahaans', function (Blueprint $table) {
            $table->id();

            // Nama
            $table->string('nama'); // Indonesia
            $table->string('nama_en')->nullable(); // English
            $table->string('nama_zh')->nullable(); // 中文

            // Jabatan
            $table->string('jabatan'); // Indonesia
            $table->string('jabatan_en')->nullable(); // English
            $table->string('jabatan_zh')->nullable(); // 中文

            // Gambar
            $table->string('gambar')->nullable();

            // Pengaturan
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('struktur_perusahaans');
    }
};
