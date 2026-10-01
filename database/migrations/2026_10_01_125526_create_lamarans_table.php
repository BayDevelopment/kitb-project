<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lamarans', function (Blueprint $table) {
            $table->id();

            /*
             * Relasi ke lowongan.
             */
            $table->foreignId('lowongan_id')
                ->constrained('lowongans')
                ->cascadeOnDelete();

            /*
             * Data pelamar.
             */
            $table->string('nama_lengkap');
            $table->string('email');
            $table->string('no_hp', 30);

            /*
             * Dokumen lamaran.
             */
            $table->string('cv');
            $table->string('surat_lamaran')->nullable();

            /*
             * Informasi tambahan.
             */
            $table->string('linkedin')->nullable();
            $table->string('portfolio')->nullable();
            $table->text('pesan')->nullable();

            /*
             * Status proses recruitment.
             */
            $table->string('status', 30)
                ->default('submitted')
                ->index();

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            /*
             * Index.
             */
            $table->index(['lowongan_id', 'status']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lamarans');
    }
};
