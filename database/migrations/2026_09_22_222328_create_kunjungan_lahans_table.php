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
        Schema::create('kunjungan_lahan', function (Blueprint $table) {
            $table->id();

            $table->string('nama');
            $table->string('instansi')->nullable();
            $table->string('email');
            $table->string('telepon');

            $table->date('tanggal_kunjungan');

            $table->unsignedInteger('jumlah_peserta')
                ->nullable();

            $table->text('keperluan')
                ->nullable();

            $table->enum('status', [
                'pending',
                'disetujui',
                'ditolak',
                'selesai'
            ])->default('pending');

            $table->text('catatan_admin')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_lahan');
    }
};
