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
        /*
        |--------------------------------------------------------------------------
        | Judul - 3 Bahasa
        |--------------------------------------------------------------------------
        */
        'judul_id',
        'judul_en',
        'judul_zh',

        /*
        |--------------------------------------------------------------------------
        | Informasi Pekerjaan - 3 Bahasa
        |--------------------------------------------------------------------------
        */
        'departemen_id',
        'departemen_en',
        'departemen_zh',

        'lokasi_id',
        'lokasi_en',
        'lokasi_zh',

        /*
        |--------------------------------------------------------------------------
        | Tipe Pekerjaan
        |--------------------------------------------------------------------------
        */
        'tipe_pekerjaan',

        /*
        |--------------------------------------------------------------------------
        | Konten Lowongan - 3 Bahasa
        |--------------------------------------------------------------------------
        */
        'deskripsi_id',
        'deskripsi_en',
        'deskripsi_zh',

        'tanggung_jawab_id',
        'tanggung_jawab_en',
        'tanggung_jawab_zh',

        'kualifikasi_id',
        'kualifikasi_en',
        'kualifikasi_zh',

        'benefit_id',
        'benefit_en',
        'benefit_zh',

        /*
        |--------------------------------------------------------------------------
        | Periode Recruitment
        |--------------------------------------------------------------------------
        */
        'tanggal_mulai',
        'tanggal_tutup',

        /*
        |--------------------------------------------------------------------------
        | Publishing
        |--------------------------------------------------------------------------
        */
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
                    ->orWhereDate(
                        'tanggal_mulai',
                        '<=',
                        now()->toDateString()
                    );
            })
            ->where(function (Builder $q) {
                $q->whereNull('tanggal_tutup')
                    ->orWhereDate(
                        'tanggal_tutup',
                        '>=',
                        now()->toDateString()
                    );
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
     *
     * Pencarian dilakukan terhadap seluruh konten
     * yang tersedia dalam 3 bahasa.
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

                    /*
                    |--------------------------------------------------------------------------
                    | Judul
                    |--------------------------------------------------------------------------
                    */
                    $query
                        ->where(
                            'judul_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'judul_en',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'judul_zh',
                            'like',
                            "%{$search}%"
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Departemen
                        |--------------------------------------------------------------------------
                        */
                        ->orWhere(
                            'departemen_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'departemen_en',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'departemen_zh',
                            'like',
                            "%{$search}%"
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Lokasi
                        |--------------------------------------------------------------------------
                        */
                        ->orWhere(
                            'lokasi_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'lokasi_en',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'lokasi_zh',
                            'like',
                            "%{$search}%"
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Tipe Pekerjaan
                        |--------------------------------------------------------------------------
                        */
                        ->orWhere(
                            'tipe_pekerjaan',
                            'like',
                            "%{$search}%"
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Deskripsi
                        |--------------------------------------------------------------------------
                        */
                        ->orWhere(
                            'deskripsi_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'deskripsi_en',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'deskripsi_zh',
                            'like',
                            "%{$search}%"
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Tanggung Jawab
                        |--------------------------------------------------------------------------
                        */
                        ->orWhere(
                            'tanggung_jawab_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'tanggung_jawab_en',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'tanggung_jawab_zh',
                            'like',
                            "%{$search}%"
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Kualifikasi
                        |--------------------------------------------------------------------------
                        */
                        ->orWhere(
                            'kualifikasi_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'kualifikasi_en',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'kualifikasi_zh',
                            'like',
                            "%{$search}%"
                        )

                        /*
                        |--------------------------------------------------------------------------
                        | Benefit
                        |--------------------------------------------------------------------------
                        */
                        ->orWhere(
                            'benefit_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'benefit_en',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'benefit_zh',
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

    /**
     * Relasi:
     * Satu lowongan memiliki banyak lamaran.
     */
    public function lamarans(): HasMany
    {
        return $this->hasMany(Lamaran::class);
    }

    /**
     * Hapus seluruh lamaran ketika lowongan dihapus.
     *
     * Lamaran dihapus satu per satu agar
     * event deleting pada model Lamaran tetap berjalan,
     * termasuk penghapusan file CV dan surat lamaran.
     */
    protected static function booted(): void
    {
        static::deleting(function (Lowongan $lowongan) {
            $lowongan->lamarans()->each(
                fn(Lamaran $lamaran) => $lamaran->delete()
            );
        });
    }
}
