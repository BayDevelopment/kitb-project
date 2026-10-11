<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SambutanBupati extends Model
{
    use HasFactory;

    protected $table = 'sambutan_bupatis';

    protected $fillable = [
        'nama_bupati',
        'nama_bupati_en',
        'nama_bupati_zh',

        'jabatan_bupati',
        'jabatan_bupati_en',
        'jabatan_bupati_zh',

        'sambutan_bupati',
        'sambutan_bupati_en',
        'sambutan_bupati_zh',

        'foto_bupati',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
