<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrukturPerusahaan extends Model
{
    use HasFactory;

    protected $table = 'struktur_perusahaans';

    protected $fillable = [
        'nama',
        'jabatan',
        'gambar',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];
}
