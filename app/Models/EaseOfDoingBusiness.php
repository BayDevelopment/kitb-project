<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EaseOfDoingBusiness extends Model
{
    use HasFactory;

    protected $table = 'ease_of_doing_businesses';

    protected $fillable = [
        'judul',
        'slug',
        'ringkasan',
        'deskripsi',
        'ikon',
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
     * Scope untuk data yang aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope untuk pengurutan berdasarkan urutan.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('urutan')
            ->orderBy('id');
    }
}
