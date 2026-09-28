<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Infrastruktur extends Model
{
    use HasFactory;

    protected $table = 'infrastrukturs';

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
     * Generate slug otomatis dari nama.
     *
     * Slug tidak dimasukkan sebagai input user.
     */
    protected static function booted(): void
    {
        static::creating(function (Infrastruktur $infrastruktur): void {
            $infrastruktur->slug = static::generateUniqueSlug(
                $infrastruktur->nama
            );

            if ($infrastruktur->urutan === 0) {
                $infrastruktur->urutan = ((int) static::max('urutan')) + 1;
            }
        });

        static::updating(function (Infrastruktur $infrastruktur): void {
            if ($infrastruktur->isDirty('nama')) {
                $infrastruktur->slug = static::generateUniqueSlug(
                    $infrastruktur->nama,
                    $infrastruktur->id
                );
            }
        });
    }

    /**
     * Generate slug unik.
     */
    public static function generateUniqueSlug(
        string $nama,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($nama);

        if ($baseSlug === '') {
            $baseSlug = 'infrastruktur';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            static::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->whereKeyNot($ignoreId)
            )
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Scope untuk data aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope berdasarkan urutan.
     */
    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('urutan')
            ->orderBy('id');
    }
}
