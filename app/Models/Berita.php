<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    use HasFactory;

    protected $table = 'beritas';

    protected $fillable = [
        'judul',
        'slug',
        'excerpt',
        'konten',
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
            fn(Builder $query) => $query->where('kategori', $category)
        );
    }

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

    /**
     * Increment jumlah views.
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }
}
