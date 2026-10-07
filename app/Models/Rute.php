<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rute extends Model
{
    use HasFactory;

    protected $table = 'rutes';

    protected $fillable = [
        // Bahasa Indonesia
        'nama_rute',
        'jalur',
        'deskripsi',

        // Bahasa Inggris
        'nama_rute_en',
        'jalur_en',
        'deskripsi_en',

        // Bahasa Mandarin
        'nama_rute_zh',
        'jalur_zh',
        'deskripsi_zh',

        // Data teknis
        'jarak',
        'satuan_jarak',
        'waktu_tempuh',

        // Asal & tujuan
        'asal',
        'tujuan',

        // Lokasi
        'latitude',
        'longitude',

        // Geometry
        'geometry',

        // Gambar
        'gambar',

        // Pengaturan
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
