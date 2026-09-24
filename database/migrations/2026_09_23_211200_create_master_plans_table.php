<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_plans', function (Blueprint $table) {
            $table->id();
            $table->string('judul');                              // "Phase 1 · Industrial Zone"
            $table->text('deskripsi')->nullable();                // teks di samping gambar
            $table->string('gambar_path');                        // wajib: path di storage/public
            $table->string('keterangan')->nullable();             // caption di bawah gambar
            $table->decimal('total_luas_ha', 10, 2)->nullable();  // hanya dipakai bila ada zona
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['aktif', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_plans');
    }

};
