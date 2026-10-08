<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LamaranStatus;
use App\Http\Controllers\Controller;
use App\Models\Lamaran;
use App\Models\Lowongan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class LamaranController extends Controller
{
    /**
     * Menampilkan daftar lamaran, statistik, dan filter.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        $allowedStatuses = collect(LamaranStatus::cases())
            ->map(fn(LamaranStatus $case) => $case->value)
            ->all();

        $statusInput = $request->input('status');

        $status = is_string($statusInput)
            && in_array($statusInput, $allowedStatuses, true)
            ? $statusInput
            : '';

        $lowonganIdInput = $request->input('lowongan_id');

        $lowonganId = is_scalar($lowonganIdInput)
            && filter_var(
                $lowonganIdInput,
                FILTER_VALIDATE_INT
            ) !== false
            && (int) $lowonganIdInput > 0
            ? (int) $lowonganIdInput
            : null;

        /*
         * DAFTAR LAMARAN
         * Menggunakan kolom lowongan multilingual.
         */
        $lamarans = Lamaran::query()
            ->with([
                'lowongan:id,judul_id,judul_en,judul_zh',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $keyword = '%' . $search . '%';

                $query->where(function ($query) use ($keyword) {
                    $query
                        ->where(
                            'nama_lengkap',
                            'like',
                            $keyword
                        )
                        ->orWhere('email', 'like', $keyword)
                        ->orWhere('no_hp', 'like', $keyword)
                        ->orWhere('pesan_id', 'like', $keyword)
                        ->orWhere('pesan_en', 'like', $keyword)
                        ->orWhere('pesan_zh', 'like', $keyword)
                        ->orWhereHas(
                            'lowongan',
                            function ($lowonganQuery) use ($keyword) {
                                $lowonganQuery
                                    ->where(
                                        'judul_id',
                                        'like',
                                        $keyword
                                    )
                                    ->orWhere(
                                        'judul_en',
                                        'like',
                                        $keyword
                                    )
                                    ->orWhere(
                                        'judul_zh',
                                        'like',
                                        $keyword
                                    );
                            }
                        );
                });
            })
            ->when(
                $status !== '',
                fn($query) => $query->where('status', $status)
            )
            ->when(
                $lowonganId !== null,
                fn($query) => $query->where(
                    'lowongan_id',
                    $lowonganId
                )
            )
            ->orderByRaw('submitted_at IS NULL ASC')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        /*
         * STATISTIK LAMARAN
         */
        $statusStats = collect(LamaranStatus::cases())
            ->mapWithKeys(function (LamaranStatus $case) {
                return [
                    $case->value => Lamaran::query()
                        ->where('status', $case->value)
                        ->count(),
                ];
            });

        $stats = [
            'total' => Lamaran::query()->count(),
            'by_status' => $statusStats,
        ];

        /*
         * PILIHAN LOWONGAN
         * Tidak menggunakan kolom 'judul' karena kolom
         * tersebut tidak tersedia pada migration.
         */
        $lowongans = Lowongan::query()
            ->select([
                'id',
                'judul_id',
                'judul_en',
                'judul_zh',
            ])
            ->orderBy('judul_id')
            ->get();

        /*
         * PILIHAN STATUS
         */
        $statuses = collect(LamaranStatus::cases())
            ->map(fn(LamaranStatus $case) => [
                'value' => $case->value,
                'label' => str($case->value)
                    ->replace(['_', '-'], ' ')
                    ->title()
                    ->toString(),
            ])
            ->values();

        return Inertia::render(
            'admin/Recruitment/Lamaran',
            [
                'lamarans' => $lamarans,
                'lowongans' => $lowongans,
                'statuses' => $statuses,
                'stats' => $stats,
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'lowongan_id' => $lowonganId,
                ],
            ]
        );
    }

    /**
     * Memperbarui data lamaran.
     */
    public function update(
        Request $request,
        Lamaran $lamaran
    ): RedirectResponse {
        $validated = $request->validate([
            'lowongan_id' => [
                'required',
                'integer',
                'exists:lowongans,id',
            ],
            'nama_lengkap' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'no_hp' => [
                'required',
                'string',
                'max:30',
            ],
            'linkedin' => [
                'nullable',
                'url',
                'max:255',
            ],
            'portfolio' => [
                'nullable',
                'url',
                'max:255',
            ],
            'pesan_id' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'pesan_en' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'pesan_zh' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'status' => [
                'required',
                Rule::enum(LamaranStatus::class),
            ],
            'submitted_at' => [
                'nullable',
                'date',
            ],
            'cv' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:1024',
            ],
            'surat_lamaran' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:1024',
            ],
        ], [
            'lowongan_id.required' =>
            'Lowongan wajib dipilih.',
            'lowongan_id.exists' =>
            'Lowongan yang dipilih tidak ditemukan.',
            'nama_lengkap.required' =>
            'Nama lengkap wajib diisi.',
            'email.required' =>
            'Email wajib diisi.',
            'email.email' =>
            'Format email tidak valid.',
            'no_hp.required' =>
            'Nomor telepon wajib diisi.',
            'linkedin.url' =>
            'Format URL LinkedIn tidak valid.',
            'portfolio.url' =>
            'Format URL portfolio tidak valid.',
            'cv.mimes' =>
            'CV harus berupa PDF, DOC, atau DOCX.',
            'cv.max' =>
            'Ukuran CV maksimal 1 MB.',
            'surat_lamaran.mimes' =>
            'Surat lamaran harus berupa PDF, DOC, atau DOCX.',
            'surat_lamaran.max' =>
            'Ukuran surat lamaran maksimal 1 MB.',
        ]);

        /*
         * CEGAH EMAIL DUPLIKAT PADA LOWONGAN YANG SAMA
         */
        $duplicate = Lamaran::query()
            ->where('id', '!=', $lamaran->id)
            ->where(
                'lowongan_id',
                $validated['lowongan_id']
            )
            ->where('email', $validated['email'])
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors([
                    'email' =>
                    'Email ini sudah digunakan untuk melamar posisi tersebut.',
                ])
                ->withInput();
        }

        /*
         * SIMPAN REFERENSI FILE LAMA
         */
        $oldCv = $lamaran->cv;
        $oldSuratLamaran = $lamaran->surat_lamaran;

        $cvPath = $oldCv;
        $suratLamaranPath = $oldSuratLamaran;

        try {
            /*
             * UNGGAH CV BARU JIKA ADA
             */
            if ($request->hasFile('cv')) {
                $cvPath = $request
                    ->file('cv')
                    ->store('lamaran/cv', 'public');

                if (!$cvPath) {
                    throw new \RuntimeException(
                        'Gagal menyimpan CV.'
                    );
                }
            }

            /*
             * UNGGAH SURAT LAMARAN BARU JIKA ADA
             */
            if ($request->hasFile('surat_lamaran')) {
                $suratLamaranPath = $request
                    ->file('surat_lamaran')
                    ->store('lamaran/surat', 'public');

                if (!$suratLamaranPath) {
                    throw new \RuntimeException(
                        'Gagal menyimpan surat lamaran.'
                    );
                }
            }

            /*
             * PERBARUI DATA DALAM TRANSAKSI
             */
            DB::transaction(function () use (
                $lamaran,
                $validated,
                $cvPath,
                $suratLamaranPath
            ): void {
                $lamaran->update([
                    'lowongan_id' =>
                    $validated['lowongan_id'],

                    'nama_lengkap' =>
                    $validated['nama_lengkap'],

                    'email' =>
                    $validated['email'],

                    'no_hp' =>
                    $validated['no_hp'],

                    'linkedin' =>
                    $validated['linkedin'] ?? null,

                    'portfolio' =>
                    $validated['portfolio'] ?? null,

                    'pesan_id' =>
                    $validated['pesan_id'] ?? null,

                    'pesan_en' =>
                    $validated['pesan_en'] ?? null,

                    'pesan_zh' =>
                    $validated['pesan_zh'] ?? null,

                    'status' =>
                    $validated['status'],

                    'submitted_at' =>
                    $validated['submitted_at']
                        ?? $lamaran->submitted_at,

                    'cv' => $cvPath,

                    'surat_lamaran' =>
                    $suratLamaranPath,
                ]);
            });
        } catch (Throwable $e) {
            /*
             * BERSIHKAN FILE BARU JIKA UPDATE GAGAL
             */
            if (
                $cvPath !== $oldCv
                && is_string($cvPath)
            ) {
                Storage::disk('public')->delete($cvPath);
            }

            if (
                $suratLamaranPath !== $oldSuratLamaran
                && is_string($suratLamaranPath)
            ) {
                Storage::disk('public')->delete(
                    $suratLamaranPath
                );
            }

            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' =>
                'Data lamaran gagal diperbarui. Silakan coba kembali.',
            ]);
        }

        /*
         * HAPUS FILE LAMA SETELAH UPDATE BERHASIL
         */
        if ($oldCv && $oldCv !== $cvPath) {
            Storage::disk('public')->delete($oldCv);
        }

        if (
            $oldSuratLamaran
            && $oldSuratLamaran !== $suratLamaranPath
        ) {
            Storage::disk('public')->delete(
                $oldSuratLamaran
            );
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' =>
            'Data lamaran berhasil diperbarui.',
        ]);
    }

    /**
     * Menghapus data lamaran.
     *
     * Model Lamaran dapat menangani penghapusan file
     * melalui event deleting/deleted yang sudah dibuat.
     */
    public function destroy(
        Lamaran $lamaran
    ): RedirectResponse {
        try {
            $lamaran->delete();
        } catch (Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' =>
                'Lamaran gagal dihapus. Silakan coba kembali.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' =>
            'Data lamaran berhasil dihapus.',
        ]);
    }

    /**
     * Mengunduh CV pelamar.
     */
    public function downloadCv(
        Lamaran $lamaran
    ): BinaryFileResponse {
        $path = $lamaran->cv;

        abort_unless(
            is_string($path)
                && $path !== ''
                && Storage::disk('public')->exists($path),
            404,
            'File CV tidak ditemukan.'
        );

        $absolutePath = Storage::disk('public')->path(
            $path
        );

        $filename = 'CV - '
            . $lamaran->nama_lengkap . '.'
            . pathinfo($path, PATHINFO_EXTENSION);

        return response()->download(
            $absolutePath,
            $filename
        );
    }

    /**
     * Mengunduh surat lamaran.
     */
    public function downloadSurat(
        Lamaran $lamaran
    ): BinaryFileResponse {
        $path = $lamaran->surat_lamaran;

        abort_unless(
            is_string($path)
                && $path !== ''
                && Storage::disk('public')->exists($path),
            404,
            'File surat lamaran tidak ditemukan.'
        );

        $absolutePath = Storage::disk('public')->path(
            $path
        );

        $filename = 'Surat Lamaran - '
            . $lamaran->nama_lengkap . '.'
            . pathinfo($path, PATHINFO_EXTENSION);

        return response()->download(
            $absolutePath,
            $filename
        );
    }
}
