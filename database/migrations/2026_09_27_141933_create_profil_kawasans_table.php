<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_kawasans', function (Blueprint $table) {
            $table->id();

            $table->string('judul');
            $table->string('slug')->unique();

            $table->longText('deskripsi')->nullable();

            $table->decimal('luas_kawasan', 15, 2)->nullable();
            $table->string('lokasi')->nullable();

            // Koordinat titik kawasan (untuk peta Leaflet)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Batas kawasan dalam format GeoJSON (opsional)
            $table->json('batas_kawasan')->nullable();

            $table->unsignedSmallInteger('tahun_berdiri')->nullable();

            $table->boolean('status')->default(true);

            $table->string('gambar')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('tahun_berdiri');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_kawasans');
    }
};
