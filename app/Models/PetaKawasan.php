<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PetaKawasan extends Model
{
    protected $table = 'peta_kawasan';

    protected $fillable = [
        // Bahasa Indonesia
        'nama',
        'deskripsi',

        // English
        'nama_en',
        'deskripsi_en',

        // Chinese
        'nama_zh',
        'deskripsi_zh',

        // General
        'slug',
        'gambar',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];

    /**
     * Scope untuk mengurutkan data peta kawasan.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('urutan')
            ->orderBy('id');
    }

    /**
     * Generate slug unik berdasarkan nama.
     */
    public static function generateUniqueSlug(
        string $nama,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($nama);
        $slug = $baseSlug;
        $counter = 1;

        while (
            static::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn(Builder $query) => $query->where('id', '!=', $ignoreId)
            )
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
