<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanKontak extends Model
{
    protected $fillable = [
        'nama_perusahaan',
        'alamat',
        'telepon',
        'whatsapp',
        'email',
        'email_investor',
        'jam_operasional',
        'latitude',
        'longitude',
        'maps_embed_url',
        'facebook',
        'instagram',
        'linkedin',
        'youtube',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    /**
     * Ambil satu-satunya baris pengaturan
     * dan buat otomatis bila belum tersedia.
     */
    public static function instance(): self
    {
        return static::query()->firstOrCreate(
            [],
            [
                'nama_perusahaan' =>
                'PT Kawasan Industri Tanjung Buton',
            ],
        );
    }
}
