<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Fasilitas extends Model
{
    protected $table = 'fasilitas';

    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
        'gambar',
        'urutan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    /**
     * Scope untuk data berdasarkan urutan.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('urutan')
            ->orderBy('id');
    }

    /**
     * Generate slug otomatis dari nama.
     */
    public static function generateUniqueSlug(
        string $nama,
        ?int $ignoreId = null,
    ): string {
        $baseSlug = Str::slug($nama);

        if ($baseSlug === '') {
            $baseSlug = 'fasilitas';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            static::query()
            ->when(
                $ignoreId !== null,
                fn(Builder $query) => $query->whereKeyNot($ignoreId),
            )
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
