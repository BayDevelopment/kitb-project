<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lahan extends Model
{
    use HasFactory;

    protected $table = 'lahans';

    protected $fillable = [
        'judul',
        'nilai',
        'satuan',
        'deskripsi',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:1',
            'urutan' => 'integer',
        ];
    }
}
