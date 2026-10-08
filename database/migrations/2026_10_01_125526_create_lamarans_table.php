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
            |--------------------------------------------------------------------------
            | Relasi ke lowongan
            |--------------------------------------------------------------------------
            */

            $table->foreignId('lowongan_id')
                ->constrained('lowongans')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Data pelamar
            |--------------------------------------------------------------------------
            |
            | Data personal berasal langsung dari pelamar sehingga
            | tidak menggunakan struktur multilingual.
            |
            */

            $table->string('nama_lengkap');
            $table->string('email');
            $table->string('no_hp', 30);

            /*
            |--------------------------------------------------------------------------
            | Dokumen lamaran
            |--------------------------------------------------------------------------
            */

            $table->string('cv');
            $table->string('surat_lamaran')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Informasi tambahan
            |--------------------------------------------------------------------------
            */

            $table->string('linkedin')->nullable();
            $table->string('portfolio')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Pesan lamaran multilingual
            |--------------------------------------------------------------------------
            |
            | Sesuai dengan form Lamaran.vue:
            | - pesan_id = Bahasa Indonesia
            | - pesan_en = English
            | - pesan_zh = 中文
            |
            */

            $table->text('pesan_id')->nullable();
            $table->text('pesan_en')->nullable();
            $table->text('pesan_zh')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status proses rekrutmen
            |--------------------------------------------------------------------------
            */

            $table->string('status', 30)
                ->default('submitted')
                ->index();

            $table->timestamp('submitted_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
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
