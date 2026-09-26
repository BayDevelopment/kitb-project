<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnakUsaha extends Model
{
    protected $table = 'anak_usahas';

    protected $fillable = [
        'nama',
        'logo',
        'deskripsi',
        'website',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'aktif' => 'boolean',
    ];
}
