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
        Schema::create('rutes', function (Blueprint $table) {
            $table->id();

            // =====================================================
            // INFORMASI RUTE
            // =====================================================

            // Bahasa Indonesia
            $table->string('nama_rute', 200);
            $table->string('jalur', 255);
            $table->text('deskripsi')->nullable();

            // Bahasa Inggris
            $table->string('nama_rute_en', 200)->nullable();
            $table->string('jalur_en', 255)->nullable();
            $table->text('deskripsi_en')->nullable();

            // Bahasa Mandarin
            $table->string('nama_rute_zh', 200)->nullable();
            $table->string('jalur_zh', 255)->nullable();
            $table->text('deskripsi_zh')->nullable();

            // =====================================================
            // DATA TEKNIS
            // =====================================================

            $table->decimal('jarak', 10, 2);

            $table->string(
                'satuan_jarak',
                20
            )->default('km');

            $table->string(
                'waktu_tempuh',
                100
            );

            // =====================================================
            // ASAL & TUJUAN
            // =====================================================

            $table->string(
                'asal',
                200
            )->nullable();

            $table->string(
                'tujuan',
                200
            )->nullable();

            // =====================================================
            // TITIK LOKASI UTAMA
            // Digunakan untuk marker Leaflet
            // =====================================================

            $table->decimal(
                'latitude',
                10,
                7
            )->nullable();

            $table->decimal(
                'longitude',
                10,
                7
            )->nullable();

            // =====================================================
            // GEOMETRY RUTE
            // Format GeoJSON
            //
            // Point
            // LineString
            // MultiLineString
            // =====================================================

            $table->json(
                'geometry'
            )->nullable();

            // =====================================================
            // GAMBAR / ILUSTRASI
            // =====================================================

            $table->string(
                'gambar'
            )->nullable();

            // =====================================================
            // PENGATURAN TAMPILAN
            // =====================================================

            $table->unsignedInteger(
                'urutan'
            )->default(0)->index();

            $table->boolean(
                'aktif'
            )->default(true)->index();

            // =====================================================
            // TIMESTAMPS
            // =====================================================

            $table->timestamps();

            // =====================================================
            // COMPOSITE INDEX
            // =====================================================

            $table->index([
                'aktif',
                'urutan',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutes');
    }
};
