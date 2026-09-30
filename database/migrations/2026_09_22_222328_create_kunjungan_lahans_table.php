<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_lahan', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | IDENTITAS PERMOHONAN
            |--------------------------------------------------------------------------
            */
            $table->string('nomor_registrasi', 40)->unique();

            /*
            |--------------------------------------------------------------------------
            | DATA PEMOHON
            |--------------------------------------------------------------------------
            */
            $table->string('nama', 150);
            $table->string('instansi', 200)->nullable();
            $table->string('jabatan', 100)->nullable();

            $table->string('email', 150);
            $table->string('telepon', 25);

            /*
            |--------------------------------------------------------------------------
            | JADWAL KUNJUNGAN
            |--------------------------------------------------------------------------
            */
            $table->date('tanggal_kunjungan')->index();

            $table->time('waktu_mulai');
            $table->time('waktu_selesai');

            /*
            |--------------------------------------------------------------------------
            | DATA KUNJUNGAN
            |--------------------------------------------------------------------------
            */
            $table->unsignedSmallInteger('jumlah_peserta')
                ->default(1);

            $table->string('area_lahan', 255);

            $table->text('keperluan')
                ->nullable();

            $table->boolean('memerlukan_pendamping')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | STATUS PERMOHONAN
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'pending',
                'disetujui',
                'ditolak',
                'selesai',
            ])
                ->default('pending')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | CATATAN ADMIN
            |--------------------------------------------------------------------------
            */
            $table->text('catatan_admin')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | TIMESTAMP STATUS
            |--------------------------------------------------------------------------
            */
            $table->timestamp('disetujui_at')
                ->nullable();

            $table->timestamp('ditolak_at')
                ->nullable();

            $table->timestamp('selesai_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | TIMESTAMPS
            |--------------------------------------------------------------------------
            */
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | COMPOSITE INDEX
            |--------------------------------------------------------------------------
            */
            $table->index([
                'tanggal_kunjungan',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan_lahan');
    }
};
