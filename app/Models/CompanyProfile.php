<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'nama_perusahaan',
        'tentang_kami',
        'latar_belakang',
        'moto',
        'alamat',
        'email',
        'telepon',
        'website',
        'logo',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];
}
