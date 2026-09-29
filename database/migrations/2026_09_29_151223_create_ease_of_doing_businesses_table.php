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
        Schema::create('ease_of_doing_businesses', function (Blueprint $table) {
            $table->id();

            $table->string('judul');
            $table->string('slug')->unique();

            $table->text('ringkasan')->nullable();
            $table->longText('deskripsi')->nullable();

            $table->string('ikon')->nullable();

            $table->unsignedInteger('urutan')->default(0);

            $table->boolean('aktif')->default(true);

            $table->timestamps();

            $table->index(['aktif', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ease_of_doing_businesses');
    }
};
