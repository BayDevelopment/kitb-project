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
        Schema::create('profil_kawasans', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Informasi Utama
            |--------------------------------------------------------------------------
            */
            $table->string('judul');
            $table->string('slug')->unique();

            /*
            |--------------------------------------------------------------------------
            | Deskripsi Multilingual
            |--------------------------------------------------------------------------
            */
            $table->longText('deskripsi')->nullable();
            $table->longText('deskripsi_en')->nullable();
            $table->longText('deskripsi_zh')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Informasi Kawasan
            |--------------------------------------------------------------------------
            */
            $table->decimal('luas_kawasan', 15, 2)->nullable();
            $table->string('lokasi')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Koordinat Titik Kawasan
            |--------------------------------------------------------------------------
            */
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Batas Kawasan - GeoJSON
            |--------------------------------------------------------------------------
            */
            $table->json('batas_kawasan')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Tahun & Status
            |--------------------------------------------------------------------------
            */
            $table->unsignedSmallInteger('tahun_berdiri')->nullable();
            $table->boolean('status')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Gambar
            |--------------------------------------------------------------------------
            */
            $table->string('gambar')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */
            $table->index('status');
            $table->index('tahun_berdiri');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_kawasans');
    }
};
