<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeluangInvestasi extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'peluang_investasis';

    protected $fillable = [
        'judul',
        'slug',
        'sektor_industri',
        'deskripsi',
        'luas_lahan',
        'satuan_luas',
        'lokasi',
        'status',
        'nilai_investasi',
        'mata_uang',
        'gambar',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'luas_lahan' => 'decimal:2',
        'nilai_investasi' => 'decimal:2',
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];

    /**
     * Scope untuk peluang investasi yang aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope untuk filter berdasarkan status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk mengurutkan data berdasarkan urutan.
     */
    public function scopeTerurut(Builder $query): Builder
    {
        return $query
            ->orderBy('urutan')
            ->orderBy('id');
    }

    /**
     * Label status untuk kebutuhan tampilan.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'tersedia' => 'Tersedia',
            'proses' => 'Dalam Proses',
            'terisi' => 'Terisi',
            'ditutup' => 'Ditutup',
            default => ucfirst(
                str_replace('_', ' ', $this->status)
            ),
        };
    }

    /**
     * URL halaman publik berdasarkan slug.
     */
    public function getUrlAttribute(): string
    {
        return url('/peluang-investasi/' . $this->slug);
    }
}
