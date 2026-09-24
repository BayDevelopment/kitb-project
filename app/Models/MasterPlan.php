<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterPlan extends Model
{
    protected $fillable = [
        'judul',
        'deskripsi',
        'gambar_path',
        'keterangan',
        'total_luas_ha',
        'urutan',
        'aktif',
    ];

    protected $casts = ['aktif' => 'boolean', 'total_luas_ha' => 'float'];

    public function zones(): HasMany
    {
        return $this->hasMany(KawasanZone::class)->orderBy('urutan');
    }
}
