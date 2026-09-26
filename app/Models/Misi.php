<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Misi extends Model
{
    protected $fillable = [
        'visi_id',
        'isi',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function visi(): BelongsTo
    {
        return $this->belongsTo(Visi::class);
    }
}
