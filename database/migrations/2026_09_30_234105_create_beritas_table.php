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
        Schema::create('beritas', function (Blueprint $table) {
            $table->id();

            $table->string('judul');
            $table->string('slug')->unique();

            $table->text('excerpt')->nullable();
            $table->longText('konten');

            $table->string('gambar')->nullable();

            $table->string('kategori')->nullable();

            $table->string('penulis')->nullable();

            $table->enum('status', [
                'draft',
                'published',
                'archived',
            ])->default('draft')->index();

            $table->timestamp('published_at')->nullable()->index();

            $table->boolean('is_featured')->default(false)->index();

            $table->unsignedBigInteger('views')->default(0);

            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beritas');
    }
};
