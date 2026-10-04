<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesanKontak extends Model
{
    use HasFactory;

    protected $table = 'pesan_kontaks';

    public const STATUS_BARU = 'baru';
    public const STATUS_DIBACA = 'dibaca';
    public const STATUS_DIBALAS = 'dibalas';

    public const STATUSES = [
        self::STATUS_BARU,
        self::STATUS_DIBACA,
        self::STATUS_DIBALAS,
    ];

    protected $fillable = [
        'nama',
        'email',
        'telepon',
        'perusahaan',
        'subjek',
        'pesan',
        'balasan',
        'status',
        'ip_address',
        'dibaca_at',
        'dibalas_at',
    ];

    protected function casts(): array
    {
        return [
            'dibaca_at' => 'datetime',
            'dibalas_at' => 'datetime',
        ];
    }

    public function scopeCari($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('perusahaan', 'like', "%{$search}%")
                ->orWhere('subjek', 'like', "%{$search}%")
                ->orWhere('pesan', 'like', "%{$search}%");
        });
    }

    public function scopeStatus($query, ?string $status)
    {
        if (!$status) {
            return $query;
        }

        return $query->where('status', $status);
    }
}
