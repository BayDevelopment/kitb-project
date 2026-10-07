<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EaseOfDoingBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EaseOfDoingBusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        $easeOfDoingBusinesses = EaseOfDoingBusiness::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('judul', 'like', "%{$search}%")
                        ->orWhere('judul_en', 'like', "%{$search}%")
                        ->orWhere('judul_zh', 'like', "%{$search}%")
                        ->orWhere('ringkasan', 'like', "%{$search}%")
                        ->orWhere('ringkasan_en', 'like', "%{$search}%")
                        ->orWhere('ringkasan_zh', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhere('deskripsi_en', 'like', "%{$search}%")
                        ->orWhere('deskripsi_zh', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($status, ['aktif', 'nonaktif'], true),
                function ($query) use ($status) {
                    $query->where(
                        'aktif',
                        $status === 'aktif'
                    );
                }
            )
            ->orderBy('urutan')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'admin/HubunganInvestor/EaseOfDoingBusiness',
            [
                'kemudahanBerusaha' => $easeOfDoingBusinesses,

                'filters' => [
                    'search' => $search,
                    'status' => $status,
                ],
            ]
        );
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Bahasa Indonesia
            'judul' => [
                'required',
                'string',
                'max:255',
            ],

            'ringkasan' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'deskripsi' => [
                'nullable',
                'string',
                'max:20000',
            ],

            // Bahasa Inggris
            'judul_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'ringkasan_en' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'deskripsi_en' => [
                'nullable',
                'string',
                'max:20000',
            ],

            // Bahasa Mandarin
            'judul_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            'ringkasan_zh' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'deskripsi_zh' => [
                'nullable',
                'string',
                'max:20000',
            ],

            // Data umum
            'ikon' => [
                'nullable',
                'string',
                'max:100',
            ],

            'aktif' => [
                'nullable',
                'boolean',
            ],
        ]);

        try {
            EaseOfDoingBusiness::create([
                // Bahasa Indonesia
                'judul' => $validated['judul'],
                'ringkasan' => $validated['ringkasan'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,

                // Bahasa Inggris
                'judul_en' => $validated['judul_en'] ?? null,
                'ringkasan_en' => $validated['ringkasan_en'] ?? null,
                'deskripsi_en' => $validated['deskripsi_en'] ?? null,

                // Bahasa Mandarin
                'judul_zh' => $validated['judul_zh'] ?? null,
                'ringkasan_zh' => $validated['ringkasan_zh'] ?? null,
                'deskripsi_zh' => $validated['deskripsi_zh'] ?? null,

                // Data umum
                'slug' => $this->generateUniqueSlug(
                    $validated['judul']
                ),

                'ikon' => $validated['ikon'] ?? null,

                /**
                 * Jika data baru ditambahkan,
                 * letakkan di urutan paling belakang.
                 */
                'urutan' => (
                    (int) EaseOfDoingBusiness::max('urutan')
                ) + 1,

                'aktif' => $validated['aktif'] ?? true,
            ]);

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Ease of Doing Business berhasil ditambahkan.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Ease of Doing Business gagal ditambahkan.',
            ]);
        }
    }

    /**
     * Update the specified resource.
     */
    public function update(
        Request $request,
        EaseOfDoingBusiness $easeOfDoingBusiness
    ): RedirectResponse {
        $validated = $request->validate([
            // Bahasa Indonesia
            'judul' => [
                'required',
                'string',
                'max:255',
            ],

            'ringkasan' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'deskripsi' => [
                'nullable',
                'string',
                'max:20000',
            ],

            // Bahasa Inggris
            'judul_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'ringkasan_en' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'deskripsi_en' => [
                'nullable',
                'string',
                'max:20000',
            ],

            // Bahasa Mandarin
            'judul_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            'ringkasan_zh' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'deskripsi_zh' => [
                'nullable',
                'string',
                'max:20000',
            ],

            // Data umum
            'ikon' => [
                'nullable',
                'string',
                'max:100',
            ],

            'aktif' => [
                'nullable',
                'boolean',
            ],
        ]);

        try {
            /**
             * Slug TIDAK diubah saat edit.
             */
            $easeOfDoingBusiness->update([
                // Bahasa Indonesia
                'judul' => $validated['judul'],
                'ringkasan' => $validated['ringkasan'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,

                // Bahasa Inggris
                'judul_en' => $validated['judul_en'] ?? null,
                'ringkasan_en' => $validated['ringkasan_en'] ?? null,
                'deskripsi_en' => $validated['deskripsi_en'] ?? null,

                // Bahasa Mandarin
                'judul_zh' => $validated['judul_zh'] ?? null,
                'ringkasan_zh' => $validated['ringkasan_zh'] ?? null,
                'deskripsi_zh' => $validated['deskripsi_zh'] ?? null,

                // Data umum
                'ikon' => $validated['ikon'] ?? null,

                'aktif' => $validated['aktif']
                    ?? $easeOfDoingBusiness->aktif,
            ]);

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Ease of Doing Business berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Ease of Doing Business gagal diperbarui.',
            ]);
        }
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(
        EaseOfDoingBusiness $easeOfDoingBusiness
    ): RedirectResponse {
        try {
            DB::transaction(function () use ($easeOfDoingBusiness) {
                $deletedOrder = $easeOfDoingBusiness->urutan;

                $easeOfDoingBusiness->delete();

                /**
                 * Rapikan urutan setelah data dihapus.
                 */
                EaseOfDoingBusiness::query()
                    ->where('urutan', '>', $deletedOrder)
                    ->decrement('urutan');
            });

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Ease of Doing Business berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Ease of Doing Business gagal dihapus.',
            ]);
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleAktif(
        EaseOfDoingBusiness $easeOfDoingBusiness
    ): RedirectResponse {
        try {
            $easeOfDoingBusiness->update([
                'aktif' => ! $easeOfDoingBusiness->aktif,
            ]);

            return back()->with('toast', [
                'type' => 'success',
                'message' => $easeOfDoingBusiness->aktif
                    ? 'Ease of Doing Business berhasil diaktifkan.'
                    : 'Ease of Doing Business berhasil dinonaktifkan.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Status Ease of Doing Business gagal diperbarui.',
            ]);
        }
    }

    /**
     * Move item up.
     */
    public function moveUp(
        EaseOfDoingBusiness $easeOfDoingBusiness
    ): RedirectResponse {
        try {
            $moved = DB::transaction(function () use (
                $easeOfDoingBusiness
            ) {
                $neighbor = EaseOfDoingBusiness::query()
                    ->where(function ($query) use ($easeOfDoingBusiness) {
                        $query
                            ->where(
                                'urutan',
                                '<',
                                $easeOfDoingBusiness->urutan
                            )
                            ->orWhere(function ($query) use (
                                $easeOfDoingBusiness
                            ) {
                                $query
                                    ->where(
                                        'urutan',
                                        $easeOfDoingBusiness->urutan
                                    )
                                    ->where(
                                        'id',
                                        '<',
                                        $easeOfDoingBusiness->id
                                    );
                            });
                    })
                    ->orderByDesc('urutan')
                    ->orderByDesc('id')
                    ->first();

                if (! $neighbor) {
                    return false;
                }

                $currentOrder = $easeOfDoingBusiness->urutan;
                $neighborOrder = $neighbor->urutan;

                $easeOfDoingBusiness->update([
                    'urutan' => $neighborOrder,
                ]);

                $neighbor->update([
                    'urutan' => $currentOrder,
                ]);

                return true;
            });

            if (! $moved) {
                return back()->with('toast', [
                    'type' => 'error',
                    'message' =>
                    'Ease of Doing Business sudah berada di urutan paling atas.',
                ]);
            }

            return back()->with('toast', [
                'type' => 'success',
                'message' =>
                'Urutan Ease of Doing Business berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' =>
                'Urutan Ease of Doing Business gagal diperbarui.',
            ]);
        }
    }

    /**
     * Move item down.
     */
    public function moveDown(
        EaseOfDoingBusiness $easeOfDoingBusiness
    ): RedirectResponse {
        try {
            $moved = DB::transaction(function () use (
                $easeOfDoingBusiness
            ) {
                $neighbor = EaseOfDoingBusiness::query()
                    ->where(function ($query) use ($easeOfDoingBusiness) {
                        $query
                            ->where(
                                'urutan',
                                '>',
                                $easeOfDoingBusiness->urutan
                            )
                            ->orWhere(function ($query) use (
                                $easeOfDoingBusiness
                            ) {
                                $query
                                    ->where(
                                        'urutan',
                                        $easeOfDoingBusiness->urutan
                                    )
                                    ->where(
                                        'id',
                                        '>',
                                        $easeOfDoingBusiness->id
                                    );
                            });
                    })
                    ->orderBy('urutan')
                    ->orderBy('id')
                    ->first();

                if (! $neighbor) {
                    return false;
                }

                $currentOrder = $easeOfDoingBusiness->urutan;
                $neighborOrder = $neighbor->urutan;

                $easeOfDoingBusiness->update([
                    'urutan' => $neighborOrder,
                ]);

                $neighbor->update([
                    'urutan' => $currentOrder,
                ]);

                return true;
            });

            if (! $moved) {
                return back()->with('toast', [
                    'type' => 'error',
                    'message' =>
                    'Ease of Doing Business sudah berada di urutan paling bawah.',
                ]);
            }

            return back()->with('toast', [
                'type' => 'success',
                'message' =>
                'Urutan Ease of Doing Business berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' =>
                'Urutan Ease of Doing Business gagal diperbarui.',
            ]);
        }
    }

    /**
     * Generate unique slug.
     */
    private function generateUniqueSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'ease-of-doing-business';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            EaseOfDoingBusiness::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->whereKeyNot($ignoreId)
            )
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
