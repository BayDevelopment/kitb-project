<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnakUsaha extends Model
{
    protected $table = 'anak_usahas';

    protected $fillable = [
        'nama',
        'nama_en',
        'nama_zh',
        'logo',
        'deskripsi',
        'deskripsi_en',
        'deskripsi_zh',
        'website',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];
}
