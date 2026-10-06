<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'nama_perusahaan',
        'tentang_kami',
        'tentang_kami_en',
        'tentang_kami_zh',

        'latar_belakang',
        'latar_belakang_en',
        'latar_belakang_zh',

        'moto',
        'moto_en',
        'moto_zh',
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
