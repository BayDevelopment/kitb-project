<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visi extends Model
{
    protected $fillable = [
        'isi',
        'isi_en',
        'isi_zh',
    ];

    public function misis(): HasMany
    {
        return $this->hasMany(Misi::class)
            ->orderBy('urutan')
            ->orderBy('id');
    }
}
