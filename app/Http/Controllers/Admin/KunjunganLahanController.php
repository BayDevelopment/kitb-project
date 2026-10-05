<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KunjunganLahan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class KunjunganLahanController extends Controller
{
    /**
     * Menampilkan daftar pengajuan kunjungan lahan.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');
        $tanggal = $request->input('tanggal');

        $kunjunganLahan = KunjunganLahan::query()
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where(
                                'nomor_registrasi',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'nama',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'instansi',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'email',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'telepon',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'area_lahan',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->when(
                in_array(
                    $status,
                    KunjunganLahan::statuses(),
                    true
                ),
                fn($query) => $query->where(
                    'status',
                    $status
                )
            )
            ->when(
                $tanggal,
                fn($query) => $query->whereDate(
                    'tanggal_kunjungan',
                    $tanggal
                )
            )
            ->orderBy('tanggal_kunjungan')
            ->orderBy('waktu_mulai')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'admin/HubunganInvestor/KunjunganLahan',
            [
                'kunjunganLahan' => $kunjunganLahan,

                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'tanggal' => $tanggal,
                ],

                'statuses' => KunjunganLahan::statuses(),
            ]
        );
    }

    /**
     * Menyimpan pengajuan kunjungan lahan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        if (
            $this->hasScheduleConflict(
                $validated['tanggal_kunjungan'],
                $validated['waktu_mulai'],
                $validated['waktu_selesai']
            )
        ) {
            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Jadwal kunjungan pada waktu tersebut sudah digunakan.',
                ]);
        }

        DB::transaction(function () use ($validated) {
            KunjunganLahan::create([
                ...$validated,
                'nomor_registrasi' => $this->generateRegistrationNumber(),
                'status' => KunjunganLahan::STATUS_PENDING,
                'disetujui_at' => null,
                'ditolak_at' => null,
                'selesai_at' => null,
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Pengajuan kunjungan lahan berhasil dibuat.',
        ]);
    }

    /**
     * Memperbarui data kunjungan lahan.
     *
     * Nomor registrasi dan status tidak dapat diubah
     * melalui update biasa.
     */
    public function update(
        Request $request,
        KunjunganLahan $kunjunganLahan
    ): RedirectResponse {
        $validated = $this->validateData($request);

        if (
            $this->hasScheduleConflict(
                $validated['tanggal_kunjungan'],
                $validated['waktu_mulai'],
                $validated['waktu_selesai'],
                $kunjunganLahan->id
            )
        ) {
            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Jadwal kunjungan pada waktu tersebut sudah digunakan.',
                ]);
        }

        $kunjunganLahan->update($validated);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Data kunjungan lahan berhasil diperbarui.',
        ]);
    }

    /**
     * Menghapus data kunjungan lahan.
     */
    public function destroy(
        KunjunganLahan $kunjunganLahan
    ): RedirectResponse {
        $kunjunganLahan->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Data kunjungan lahan berhasil dihapus.',
        ]);
    }

    /**
     * Menyetujui pengajuan kunjungan.
     */
    public function approve(
        KunjunganLahan $kunjunganLahan
    ): RedirectResponse {
        if (!$kunjunganLahan->isPending()) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Hanya pengajuan dengan status pending yang dapat disetujui.',
            ]);
        }

        if (
            $this->hasScheduleConflict(
                $kunjunganLahan->tanggal_kunjungan->format('Y-m-d'),
                $kunjunganLahan->waktu_mulai,
                $kunjunganLahan->waktu_selesai,
                $kunjunganLahan->id
            )
        ) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Jadwal kunjungan bertabrakan dengan kunjungan lain.',
            ]);
        }

        DB::transaction(function () use ($kunjunganLahan) {
            $kunjunganLahan->update([
                'status' => KunjunganLahan::STATUS_DISETUJUI,
                'disetujui_at' => now(),
                'ditolak_at' => null,
                'selesai_at' => null,
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Pengajuan kunjungan berhasil disetujui.',
        ]);
    }

    /**
     * Menolak pengajuan kunjungan.
     */
    public function reject(
        Request $request,
        KunjunganLahan $kunjunganLahan
    ): RedirectResponse {
        if (!$kunjunganLahan->isPending()) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Hanya pengajuan dengan status pending yang dapat ditolak.',
            ]);
        }

        $validated = $request->validate([
            'catatan_admin' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        DB::transaction(function () use (
            $kunjunganLahan,
            $validated
        ) {
            $kunjunganLahan->update([
                'status' => KunjunganLahan::STATUS_DITOLAK,
                'catatan_admin' => $validated['catatan_admin'] ?? null,
                'ditolak_at' => now(),
                'disetujui_at' => null,
                'selesai_at' => null,
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Pengajuan kunjungan berhasil ditolak.',
        ]);
    }

    /**
     * Menandai kunjungan sebagai selesai.
     */
    public function complete(
        KunjunganLahan $kunjunganLahan
    ): RedirectResponse {
        if (!$kunjunganLahan->isDisetujui()) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Hanya kunjungan yang sudah disetujui yang dapat diselesaikan.',
            ]);
        }

        DB::transaction(function () use ($kunjunganLahan) {
            $kunjunganLahan->update([
                'status' => KunjunganLahan::STATUS_SELESAI,
                'selesai_at' => now(),
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Kunjungan lahan berhasil ditandai sebagai selesai.',
        ]);
    }

    /**
     * Validasi data kunjungan.
     */
    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:150',
            ],

            'instansi' => [
                'nullable',
                'string',
                'max:200',
            ],

            'jabatan' => [
                'nullable',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
            ],

            'telepon' => [
                'required',
                'string',
                'max:25',
            ],

            'tanggal_kunjungan' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'waktu_mulai' => [
                'required',
                'date_format:H:i',
            ],

            'waktu_selesai' => [
                'required',
                'date_format:H:i',
                'after:waktu_mulai',
            ],

            'jumlah_peserta' => [
                'required',
                'integer',
                'min:1',
                'max:65535',
            ],

            'area_lahan' => [
                'required',
                'string',
                'max:255',
            ],

            'keperluan' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'memerlukan_pendamping' => [
                'required',
                'boolean',
            ],
        ]);

        $validated['memerlukan_pendamping'] = filter_var(
            $validated['memerlukan_pendamping'],
            FILTER_VALIDATE_BOOLEAN
        );

        return $validated;
    }

    /**
     * Mengecek bentrok jadwal kunjungan.
     */
    private function hasScheduleConflict(
        string $tanggal,
        string $waktuMulai,
        string $waktuSelesai,
        ?int $ignoreId = null
    ): bool {
        return KunjunganLahan::query()
            ->whereDate(
                'tanggal_kunjungan',
                $tanggal
            )
            ->whereIn('status', [
                KunjunganLahan::STATUS_PENDING,
                KunjunganLahan::STATUS_DISETUJUI,
            ])
            ->when(
                $ignoreId !== null,
                fn($query) => $query->where(
                    'id',
                    '!=',
                    $ignoreId
                )
            )
            ->where(function ($query) use (
                $waktuMulai,
                $waktuSelesai
            ) {
                $query
                    ->where(
                        'waktu_mulai',
                        '<',
                        $waktuSelesai
                    )
                    ->where(
                        'waktu_selesai',
                        '>',
                        $waktuMulai
                    );
            })
            ->exists();
    }

    /**
     * Membuat nomor registrasi unik berdasarkan tanggal.
     *
     * Format:
     * KITB-VISIT-YYYYMMDD-0001
     */
    private function generateRegistrationNumber(): string
    {
        $tanggal = now()->format('Ymd');

        $prefix = "KITB-VISIT-{$tanggal}-";

        $lastRegistration = KunjunganLahan::query()
            ->where(
                'nomor_registrasi',
                'like',
                "{$prefix}%"
            )
            ->orderByDesc('nomor_registrasi')
            ->value('nomor_registrasi');

        $nextNumber = 1;

        if ($lastRegistration) {
            $lastNumber = (int) substr(
                $lastRegistration,
                strlen($prefix)
            );

            $nextNumber = $lastNumber + 1;
        }

        do {
            $registrationNumber = $prefix . str_pad(
                (string) $nextNumber,
                4,
                '0',
                STR_PAD_LEFT
            );

            $exists = KunjunganLahan::query()
                ->where(
                    'nomor_registrasi',
                    $registrationNumber
                )
                ->exists();

            $nextNumber++;
        } while ($exists);

        return $registrationNumber;
    }
}
