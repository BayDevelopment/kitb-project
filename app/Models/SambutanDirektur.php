<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SambutanDirektur extends Model
{
    use HasFactory;

    protected $table = 'sambutan_direkturs';

    protected $fillable = [
        'nama_direktur',
        'nama_direktur_en',
        'nama_direktur_zh',
        'jabatan_direktur',
        'jabatan_direktur_en',
        'jabatan_direktur_zh',
        'sambutan_direktur',
        'sambutan_direktur_en',
        'sambutan_direktur_zh',
        'foto_direktur',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
