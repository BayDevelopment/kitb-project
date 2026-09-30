<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KunjunganLahan extends Model
{
    use HasFactory;

    protected $table = 'kunjungan_lahan';

    protected $fillable = [
        'nomor_registrasi',
        'nama',
        'instansi',
        'jabatan',
        'email',
        'telepon',
        'tanggal_kunjungan',
        'waktu_mulai',
        'waktu_selesai',
        'jumlah_peserta',
        'area_lahan',
        'keperluan',
        'memerlukan_pendamping',
        'status',
        'catatan_admin',
        'disetujui_at',
        'ditolak_at',
        'selesai_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kunjungan' => 'date',
            'memerlukan_pendamping' => 'boolean',
            'disetujui_at' => 'datetime',
            'ditolak_at' => 'datetime',
            'selesai_at' => 'datetime',
        ];
    }

    public const STATUS_PENDING = 'pending';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    public const STATUS_SELESAI = 'selesai';

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_DISETUJUI,
            self::STATUS_DITOLAK,
            self::STATUS_SELESAI,
        ];
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isDisetujui(): bool
    {
        return $this->status === self::STATUS_DISETUJUI;
    }

    public function isDitolak(): bool
    {
        return $this->status === self::STATUS_DITOLAK;
    }

    public function isSelesai(): bool
    {
        return $this->status === self::STATUS_SELESAI;
    }
}
