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
        'jabatan_direktur',
        'sambutan_direktur',
        'foto_direktur',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
