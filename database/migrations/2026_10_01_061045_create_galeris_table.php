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

            $table->string('judul', 255);

            $table->string('slug', 255)->unique();

            $table->text('deskripsi')->nullable();

            $table->string('kategori', 100)->nullable();

            $table->string('gambar', 500);

            $table->string('alt_text', 255)->nullable();

            $table->date('tanggal')->nullable();

            $table->boolean('status')->default(true);

            $table->unsignedInteger('urutan')->default(0);

            $table->timestamps();

            $table->softDeletes();

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
