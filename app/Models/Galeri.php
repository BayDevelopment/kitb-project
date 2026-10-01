<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Galeri extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'galeris';

    protected $fillable = [
        'judul',
        'slug',
        'deskripsi',
        'kategori',
        'gambar',
        'alt_text',
        'tanggal',
        'status',
        'urutan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'status' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Scope hanya galeri aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope berdasarkan kategori.
     */
    public function scopeKategori(
        Builder $query,
        ?string $kategori
    ): Builder {
        return $query->when(
            filled($kategori),
            fn(Builder $query) => $query->where('kategori', $kategori)
        );
    }

    /**
     * Scope untuk pengurutan galeri.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('urutan')
            ->orderByDesc('tanggal')
            ->orderByDesc('id');
    }
}
