<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MitraPerusahaan extends Model
{
    use HasFactory;

    protected $table = 'mitra_perusahaans';

    protected $fillable = [
        'nama_perusahaan',
        'slug',
        'logo',
        'website',
        'aktif',
        'urutan',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (MitraPerusahaan $mitra): void {
            if (empty($mitra->slug)) {
                $mitra->slug = static::generateUniqueSlug(
                    $mitra->nama_perusahaan
                );
            }
        });
    }

    public static function generateUniqueSlug(
        string $nama,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($nama);

        if ($baseSlug === '') {
            $baseSlug = 'mitra-perusahaan';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            static::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->where('id', '!=', $ignoreId)
            )
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
