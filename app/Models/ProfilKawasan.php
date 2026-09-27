<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProfilKawasan extends Model
{
    use HasFactory;

    protected $table = 'profil_kawasans';

    protected $fillable = [
        'judul',
        'slug',
        'deskripsi',
        'luas_kawasan',
        'lokasi',
        'tahun_berdiri',
        'status',
        'gambar',
    ];

    protected function casts(): array
    {
        return [
            'luas_kawasan' => 'decimal:2',
            'tahun_berdiri' => 'integer',
            'status' => 'boolean',
        ];
    }

    /**
     * Generate slug unik berdasarkan judul.
     */
    public static function generateUniqueSlug(
        string $judul,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($judul);
        $slug = $baseSlug;
        $counter = 1;

        while (
            static::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId,
                fn($query) => $query->whereKeyNot($ignoreId)
            )
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
