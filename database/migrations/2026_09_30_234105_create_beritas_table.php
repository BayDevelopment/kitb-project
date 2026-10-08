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

            /*
            |--------------------------------------------------------------------------
            | Bahasa Indonesia
            |--------------------------------------------------------------------------
            */
            $table->string('judul_id');
            $table->text('excerpt_id')->nullable();
            $table->longText('konten_id');

            /*
            |--------------------------------------------------------------------------
            | English
            |--------------------------------------------------------------------------
            */
            $table->string('judul_en')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->longText('konten_en')->nullable();

            /*
            |--------------------------------------------------------------------------
            | 中文 / Chinese
            |--------------------------------------------------------------------------
            */
            $table->string('judul_zh')->nullable();
            $table->text('excerpt_zh')->nullable();
            $table->longText('konten_zh')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Slug
            |--------------------------------------------------------------------------
            */
            $table->string('slug')->unique();

            /*
            |--------------------------------------------------------------------------
            | Media & Informasi Berita
            |--------------------------------------------------------------------------
            */
            $table->string('gambar')->nullable();

            $table->string('kategori')->nullable();

            $table->string('penulis')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'draft',
                'published',
                'archived',
            ])->default('draft')->index();

            /*
            |--------------------------------------------------------------------------
            | Publication
            |--------------------------------------------------------------------------
            */
            $table->timestamp('published_at')
                ->nullable()
                ->index();

            $table->boolean('is_featured')
                ->default(false)
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Views
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('views')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Composite Index
            |--------------------------------------------------------------------------
            */
            $table->index([
                'status',
                'published_at',
            ]);

            $table->index([
                'is_featured',
                'status',
            ]);
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
