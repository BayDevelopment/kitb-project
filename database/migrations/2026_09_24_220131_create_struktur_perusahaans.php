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

            $table->string('nama');
            $table->string('jabatan');
            $table->string('gambar')->nullable();

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
