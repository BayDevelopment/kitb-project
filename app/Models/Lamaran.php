<?php

namespace App\Models;

use App\Enums\LamaranStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Lamaran extends Model
{
    use HasFactory;

    protected $table = 'lamarans';

    protected $fillable = [
        'lowongan_id',
        'nama_lengkap',
        'email',
        'no_hp',
        'cv',
        'surat_lamaran',
        'linkedin',
        'portfolio',
        'pesan',
        'status',
        'submitted_at',
    ];

    /**
     * Path file tidak boleh ikut ke JSON/Inertia.
     */
    protected $hidden = [
        'cv',
        'surat_lamaran',
    ];

    protected $appends = [
        'has_cv',
        'has_surat_lamaran',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'status' => LamaranStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Lamaran $lamaran) {
            foreach ([$lamaran->cv, $lamaran->surat_lamaran] as $path) {
                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            }
        });
    }

    public function lowongan(): BelongsTo
    {
        return $this->belongsTo(Lowongan::class);
    }

    protected function hasCv(): Attribute
    {
        return Attribute::get(
            fn() => filled($this->cv)
        );
    }

    protected function hasSuratLamaran(): Attribute
    {
        return Attribute::get(
            fn() => filled($this->surat_lamaran)
        );
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when(
            $status,
            fn($q) => $q->where('status', $status)
        );
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function ($q) use ($term) {
            $q->where(function ($q) use ($term) {
                $q->where('nama_lengkap', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        });
    }
}
