<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lowongans', function (Blueprint $table): void {
            $table->id();

            // =========================
            // IDENTITAS LOWONGAN
            // =========================
            $table->string('slug')->unique();

            // =========================
            // JUDUL — 3 BAHASA
            // =========================
            $table->string('judul_id');
            $table->string('judul_en');
            $table->string('judul_zh');

            // =========================
            // DEPARTEMEN — 3 BAHASA
            // =========================
            $table->string('departemen_id')->nullable();
            $table->string('departemen_en')->nullable();
            $table->string('departemen_zh')->nullable();

            // =========================
            // LOKASI — 3 BAHASA
            // =========================
            $table->string('lokasi_id')->nullable();
            $table->string('lokasi_en')->nullable();
            $table->string('lokasi_zh')->nullable();

            // =========================
            // TIPE PEKERJAAN
            // =========================
            $table->string('tipe_pekerjaan', 50)->nullable();

            // =========================
            // DESKRIPSI — 3 BAHASA
            // =========================
            $table->longText('deskripsi_id')->nullable();
            $table->longText('deskripsi_en')->nullable();
            $table->longText('deskripsi_zh')->nullable();

            // =========================
            // TANGGUNG JAWAB — 3 BAHASA
            // =========================
            $table->longText('tanggung_jawab_id')->nullable();
            $table->longText('tanggung_jawab_en')->nullable();
            $table->longText('tanggung_jawab_zh')->nullable();

            // =========================
            // KUALIFIKASI — 3 BAHASA
            // =========================
            $table->longText('kualifikasi_id')->nullable();
            $table->longText('kualifikasi_en')->nullable();
            $table->longText('kualifikasi_zh')->nullable();

            // =========================
            // BENEFIT — 3 BAHASA
            // =========================
            $table->longText('benefit_id')->nullable();
            $table->longText('benefit_en')->nullable();
            $table->longText('benefit_zh')->nullable();

            // =========================
            // PERIODE LOWONGAN
            // =========================
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_tutup')->nullable();

            // =========================
            // STATUS
            // =========================
            $table->string('status', 20)
                ->default('draft')
                ->index();

            // =========================
            // FEATURED / UNGGULAN
            // =========================
            $table->boolean('unggulan')
                ->default(false)
                ->index();

            // =========================
            // URUTAN
            // =========================
            $table->unsignedInteger('urutan')
                ->default(0)
                ->index();

            $table->timestamps();

            // =========================
            // INDEX TAMBAHAN
            // =========================
            $table->index([
                'status',
                'unggulan',
            ]);

            $table->index([
                'tanggal_mulai',
                'tanggal_tutup',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lowongans');
    }
};
