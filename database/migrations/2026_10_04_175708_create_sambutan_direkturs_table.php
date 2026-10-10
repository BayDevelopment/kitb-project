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
        Schema::create('sambutan_direkturs', function (Blueprint $table) {
            $table->id();

            // Nama direktur
            $table->string('nama_direktur');
            $table->string('nama_direktur_en')->nullable();
            $table->string('nama_direktur_zh')->nullable();

            // Jabatan direktur
            $table->string('jabatan_direktur')->nullable();
            $table->string('jabatan_direktur_en')->nullable();
            $table->string('jabatan_direktur_zh')->nullable();

            // Sambutan direktur
            $table->text('sambutan_direktur');
            $table->text('sambutan_direktur_en')->nullable();
            $table->text('sambutan_direktur_zh')->nullable();

            // Foto direktur (tidak perlu diterjemahkan)
            $table->string('foto_direktur')->nullable();

            // Status publikasi (tidak perlu diterjemahkan)
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sambutan_direkturs');
    }
};
