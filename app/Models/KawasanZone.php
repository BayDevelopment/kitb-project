<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KawasanZone extends Model
{
    protected $fillable = [
        'master_plan_id',
        'kode',
        'label',
        'luas_ha',
        'warna',
        'catatan',
        'urutan',
        'aktif',
    ];

    protected $casts = ['aktif' => 'boolean', 'luas_ha' => 'float'];

    public function masterPlan(): BelongsTo
    {
        return $this->belongsTo(MasterPlan::class);
    }
}
