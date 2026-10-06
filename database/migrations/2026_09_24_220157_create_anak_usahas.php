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
        Schema::create('anak_usahas', function (Blueprint $table) {
            $table->id();

            // Nama perusahaan
            $table->string('nama');
            $table->string('nama_en')->nullable();
            $table->string('nama_zh')->nullable();

            // Logo
            $table->string('logo')->nullable();

            // Deskripsi perusahaan
            $table->text('deskripsi')->nullable();
            $table->text('deskripsi_en')->nullable();
            $table->text('deskripsi_zh')->nullable();

            // Website perusahaan
            $table->string('website')->nullable();

            // Pengaturan tampilan
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
        Schema::dropIfExists('anak_usahas');
    }
};
