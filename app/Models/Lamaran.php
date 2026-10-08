<?php

namespace App\Models;

use App\Enums\LamaranStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
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

        'pesan_id',
        'pesan_en',
        'pesan_zh',

        'status',
        'submitted_at',
    ];

    /**
     * Path file tidak ikut dikirim ke JSON/Inertia.
     *
     * Admin tetap dapat mengakses dokumen melalui
     * endpoint download yang disediakan LamaranController.
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

    /**
     * Hapus dokumen ketika data lamaran dihapus.
     */
    protected static function booted(): void
    {
        static::deleted(function (Lamaran $lamaran) {
            $paths = array_filter([
                $lamaran->cv,
                $lamaran->surat_lamaran,
            ]);

            DB::afterCommit(function () use ($paths) {
                foreach ($paths as $path) {
                    Storage::disk('public')->delete($path);
                }
            });
        });
    }

    /**
     * Relasi ke lowongan.
     */
    public function lowongan(): BelongsTo
    {
        return $this->belongsTo(Lowongan::class);
    }

    /**
     * Apakah pelamar memiliki CV.
     */
    protected function hasCv(): Attribute
    {
        return Attribute::get(
            fn() => filled($this->cv)
        );
    }

    /**
     * Apakah pelamar memiliki surat lamaran.
     */
    protected function hasSuratLamaran(): Attribute
    {
        return Attribute::get(
            fn() => filled($this->surat_lamaran)
        );
    }

    /**
     * Filter berdasarkan status.
     */
    public function scopeStatus(
        Builder $query,
        ?string $status
    ): Builder {
        return $query->when(
            $status,
            fn($q) => $q->where('status', $status)
        );
    }

    /**
     * Pencarian berdasarkan nama atau email.
     */
    public function scopeSearch(
        Builder $query,
        ?string $term
    ): Builder {
        return $query->when($term, function ($q) use ($term) {
            $q->where(function ($q) use ($term) {
                $q->where(
                    'nama_lengkap',
                    'like',
                    "%{$term}%"
                )->orWhere(
                    'email',
                    'like',
                    "%{$term}%"
                );
            });
        });
    }
}
