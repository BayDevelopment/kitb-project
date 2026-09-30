<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rute extends Model
{
    use HasFactory;

    protected $table = 'rutes';

    protected $fillable = [
        'nama_rute',
        'jalur',
        'jarak',
        'satuan_jarak',
        'waktu_tempuh',
        'deskripsi',
        'asal',
        'tujuan',
        'latitude',
        'longitude',
        'geometry',
        'gambar',
        'urutan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'jarak' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geometry' => 'array',
            'urutan' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('urutan')
            ->orderBy('id');
    }
}
