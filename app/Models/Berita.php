<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Berita extends Model
{
    use HasFactory;

    protected $table = 'beritas';

    protected $fillable = [
        /*
        |--------------------------------------------------------------------------
        | Bahasa Indonesia
        |--------------------------------------------------------------------------
        */
        'judul_id',
        'excerpt_id',
        'konten_id',

        /*
        |--------------------------------------------------------------------------
        | English
        |--------------------------------------------------------------------------
        */
        'judul_en',
        'excerpt_en',
        'konten_en',

        /*
        |--------------------------------------------------------------------------
        | 中文 / Chinese
        |--------------------------------------------------------------------------
        */
        'judul_zh',
        'excerpt_zh',
        'konten_zh',

        /*
        |--------------------------------------------------------------------------
        | Informasi Berita
        |--------------------------------------------------------------------------
        */
        'gambar',
        'kategori',
        'penulis',
        'status',
        'published_at',
        'is_featured',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'views' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (Berita $berita) {
            $berita->slug = static::generateUniqueSlug(
                $berita->judul_id
            );
        });

        static::updating(function (Berita $berita) {
            /*
             * Slug hanya dibuat ulang jika judul Bahasa Indonesia berubah.
             * Judul Indonesia digunakan sebagai judul utama untuk URL.
             */
            if ($berita->isDirty('judul_id')) {
                $berita->slug = static::generateUniqueSlug(
                    $berita->judul_id,
                    $berita->id
                );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Unique Slug
    |--------------------------------------------------------------------------
    */

    protected static function generateUniqueSlug(
        string $judul,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($judul);

        /*
         * Jika judul menghasilkan slug kosong.
         */
        if ($baseSlug === '') {
            $baseSlug = 'berita';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            static::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId,
                fn(Builder $query) =>
                $query->whereKeyNot($ignoreId)
            )
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope berita yang sudah dipublikasikan.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope berita unggulan.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope berita berdasarkan kategori.
     */
    public function scopeCategory(
        Builder $query,
        ?string $category
    ): Builder {
        return $query->when(
            $category,
            fn(Builder $query) =>
            $query->where('kategori', $category)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * URL gambar berita.
     */
    public function getGambarUrlAttribute(): ?string
    {
        if (! $this->gambar) {
            return null;
        }

        return asset('storage/' . $this->gambar);
    }

    /**
     * Cek apakah berita sudah dipublikasikan.
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    /**
     * Increment jumlah views.
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }
}
