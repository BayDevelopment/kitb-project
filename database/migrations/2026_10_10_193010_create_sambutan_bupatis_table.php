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
        Schema::create('sambutan_bupatis', function (Blueprint $table) {
            $table->id();

            // Nama bupati
            $table->string('nama_bupati');
            $table->string('nama_bupati_en')->nullable();
            $table->string('nama_bupati_zh')->nullable();

            // Jabatan bupati
            $table->string('jabatan_bupati')->nullable();
            $table->string('jabatan_bupati_en')->nullable();
            $table->string('jabatan_bupati_zh')->nullable();

            // Isi sambutan bupati
            $table->text('sambutan_bupati');
            $table->text('sambutan_bupati_en')->nullable();
            $table->text('sambutan_bupati_zh')->nullable();

            // Foto bupati
            $table->string('foto_bupati')->nullable();

            // Status publikasi
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sambutan_bupatis');
    }
};
