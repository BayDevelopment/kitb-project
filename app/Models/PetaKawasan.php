<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PetaKawasan extends Model
{
    protected $table = 'peta_kawasan';

    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
        'gambar',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('urutan')
            ->orderBy('id');
    }

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
