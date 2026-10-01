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
        Schema::create('lowongans', function (Blueprint $table) {
            $table->id();

            // Identitas lowongan
            $table->string('judul');
            $table->string('slug')->unique();

            // Informasi pekerjaan
            $table->string('departemen')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('tipe_pekerjaan', 50)->nullable();

            // Konten lowongan
            $table->longText('deskripsi')->nullable();
            $table->longText('tanggung_jawab')->nullable();
            $table->longText('kualifikasi')->nullable();
            $table->longText('benefit')->nullable();

            // Periode recruitment
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_tutup')->nullable();

            // Publishing
            $table->string('status', 20)
                ->default('draft')
                ->index();

            $table->boolean('unggulan')
                ->default(false)
                ->index();

            // Sorting
            $table->unsignedInteger('urutan')
                ->default(0)
                ->index();

            $table->timestamps();

            // Index tambahan untuk pencarian/filter
            $table->index(['status', 'unggulan']);
            $table->index(['tanggal_mulai', 'tanggal_tutup']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lowongans');
    }
};
