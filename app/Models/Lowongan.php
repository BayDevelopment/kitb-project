<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lowongan extends Model
{
    use HasFactory;

    /**
     * Nama tabel.
     */
    protected $table = 'lowongans';

    /**
     * Kolom yang diperbolehkan untuk mass assignment.
     *
     * SECURITY:
     * `slug` sengaja tidak dimasukkan ke dalam $fillable.
     *
     * Slug hanya boleh dibuat dan diperbarui melalui logic
     * server-side di LowonganController.
     */
    protected $fillable = [
        'judul',
        'departemen',
        'lokasi',
        'tipe_pekerjaan',
        'deskripsi',
        'tanggung_jawab',
        'kualifikasi',
        'benefit',
        'tanggal_mulai',
        'tanggal_tutup',
        'status',
        'unggulan',
        'urutan',
    ];

    /**
     * Casting atribut.
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_tutup' => 'date',
            'unggulan' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    /**
     * Scope:
     * Lowongan yang sudah dipublikasikan.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope:
     * Lowongan yang masih aktif dan dapat dilamar.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $q) {
                $q->whereNull('tanggal_mulai')
                    ->orWhereDate('tanggal_mulai', '<=', now()->toDateString());
            })
            ->where(function (Builder $q) {
                $q->whereNull('tanggal_tutup')
                    ->orWhereDate('tanggal_tutup', '>=', now()->toDateString());
            });
    }

    /**
     * Scope:
     * Lowongan unggulan.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('unggulan', true);
    }

    /**
     * Scope:
     * Urutan default lowongan.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('urutan')
            ->orderByDesc('unggulan')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Scope:
     * Filter berdasarkan status.
     */
    public function scopeStatus(
        Builder $query,
        ?string $status
    ): Builder {
        return $query->when(
            filled($status),
            fn(Builder $query) => $query->where(
                'status',
                $status
            )
        );
    }

    /**
     * Scope:
     * Pencarian lowongan.
     */
    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        $search = trim((string) $search);

        return $query->when(
            $search !== '',
            function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where(
                            'judul',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'departemen',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'lokasi',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'tipe_pekerjaan',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'deskripsi',
                            'like',
                            "%{$search}%"
                        );
                });
            }
        );
    }

    /**
     * Accessor URL detail lowongan.
     */
    public function getUrlAttribute(): string
    {
        return '/karier/' . $this->slug;
    }

    /**
     * Accessor:
     * Mengecek apakah lowongan sudah expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->tanggal_tutup !== null
            && $this->tanggal_tutup->isBefore(
                now()->startOfDay()
            );
    }

    /**
     * Accessor:
     * Mengecek apakah lowongan masih terbuka.
     */
    public function getIsOpenAttribute(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if (
            $this->tanggal_tutup !== null
            && $this->tanggal_tutup->isBefore(
                now()->startOfDay()
            )
        ) {
            return false;
        }

        return true;
    }

    public function lamarans(): HasMany
    {
        return $this->hasMany(Lamaran::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Lowongan $lowongan) {
            // Hapus per model agar hook deleting di Lamaran ikut jalan
            $lowongan->lamarans()->each(
                fn(Lamaran $lamaran) => $lamaran->delete()
            );
        });
    }
}
