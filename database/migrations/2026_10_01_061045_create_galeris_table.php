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
        Schema::create('galeris', function (Blueprint $table) {
            $table->id();

            // Indonesian
            $table->string('judul_id', 255);
            $table->text('deskripsi_id')->nullable();
            $table->string('alt_text_id', 255)->nullable();

            // English
            $table->string('judul_en', 255)->nullable();
            $table->text('deskripsi_en')->nullable();
            $table->string('alt_text_en', 255)->nullable();

            // Chinese / Mandarin
            $table->string('judul_zh', 255)->nullable();
            $table->text('deskripsi_zh')->nullable();
            $table->string('alt_text_zh', 255)->nullable();

            // Shared fields
            $table->string('slug', 255)->unique();

            $table->string('kategori', 100)->nullable();

            $table->string('gambar', 500);

            $table->date('tanggal')->nullable();

            $table->boolean('status')->default(true);

            $table->unsignedInteger('urutan')->default(0);

            $table->timestamps();

            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('kategori');
            $table->index('tanggal');
            $table->index(['status', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galeris');
    }
};
