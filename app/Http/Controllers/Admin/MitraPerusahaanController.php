<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MitraPerusahaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class MitraPerusahaanController extends Controller
{
    /**
     * Display a paginated list of company partners.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));

        $mitraPerusahaans = MitraPerusahaan::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('nama_perusahaan', 'like', "%{$search}%")
                        ->orWhere('nama_perusahaan_en', 'like', "%{$search}%")
                        ->orWhere('nama_perusahaan_zh', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('website', 'like', "%{$search}%");
                });
            })
            ->orderBy('urutan')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'admin/ProfilPerusahaan/MitraPerusahaan',
            [
                'mitraPerusahaans' => $mitraPerusahaans,
                'filters' => [
                    'search' => $search,
                ],
            ]
        );
    }

    /**
     * Store a newly created company partner.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_perusahaan' => [
                'required',
                'string',
                'max:255',
            ],
            'nama_perusahaan_en' => [
                'nullable',
                'string',
                'max:255',
            ],
            'nama_perusahaan_zh' => [
                'nullable',
                'string',
                'max:255',
            ],
            'website' => [
                'nullable',
                'string',
                'url',
                'max:255',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024',
            ],
            'aktif' => [
                'nullable',
                'boolean',
            ],
        ]);

        $logoPath = null;

        try {
            /*
             * Upload terlebih dahulu.
             * Jika proses database gagal, file akan dihapus kembali.
             */
            if ($request->hasFile('logo')) {
                $logoPath = $request
                    ->file('logo')
                    ->store('mitra-perusahaan', 'public');
            }

            DB::transaction(function () use (
                $request,
                $validated,
                $logoPath
            ): void {
                $lastUrutan = MitraPerusahaan::query()
                    ->lockForUpdate()
                    ->max('urutan');

                MitraPerusahaan::create([
                    'nama_perusahaan' => trim($validated['nama_perusahaan']),
                    'nama_perusahaan_en' => isset($validated['nama_perusahaan_en'])
                        ? trim($validated['nama_perusahaan_en'])
                        : null,
                    'nama_perusahaan_zh' => isset($validated['nama_perusahaan_zh'])
                        ? trim($validated['nama_perusahaan_zh'])
                        : null,
                    'slug' => MitraPerusahaan::generateUniqueSlug(
                        trim($validated['nama_perusahaan'])
                    ),
                    'logo' => $logoPath,
                    'website' => $validated['website'] ?? null,
                    'aktif' => $request->boolean('aktif', true),
                    'urutan' => ((int) ($lastUrutan ?? -1)) + 1,
                ]);
            });
        } catch (Throwable $e) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }

            throw $e;
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Mitra perusahaan berhasil ditambahkan.',
        ]);
    }

    /**
     * Update the specified company partner.
     */
    public function update(
        Request $request,
        MitraPerusahaan $mitraPerusahaan
    ): RedirectResponse {
        $validated = $request->validate([
            'nama_perusahaan' => [
                'required',
                'string',
                'max:255',
            ],
            'nama_perusahaan_en' => [
                'nullable',
                'string',
                'max:255',
            ],
            'nama_perusahaan_zh' => [
                'nullable',
                'string',
                'max:255',
            ],
            'website' => [
                'nullable',
                'string',
                'url',
                'max:255',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024',
            ],
            'aktif' => [
                'nullable',
                'boolean',
            ],
        ]);

        $oldLogo = $mitraPerusahaan->logo;
        $newLogoPath = null;

        try {
            /*
             * Upload logo baru terlebih dahulu.
             */
            if ($request->hasFile('logo')) {
                $newLogoPath = $request
                    ->file('logo')
                    ->store('mitra-perusahaan', 'public');
            }

            DB::transaction(function () use (
                $request,
                $validated,
                $mitraPerusahaan,
                $newLogoPath
            ): void {
                $mitraPerusahaan->nama_perusahaan =
                    trim($validated['nama_perusahaan']);

                $mitraPerusahaan->nama_perusahaan_en =
                    isset($validated['nama_perusahaan_en'])
                    ? trim($validated['nama_perusahaan_en'])
                    : null;

                $mitraPerusahaan->nama_perusahaan_zh =
                    isset($validated['nama_perusahaan_zh'])
                    ? trim($validated['nama_perusahaan_zh'])
                    : null;

                $mitraPerusahaan->website =
                    $validated['website'] ?? null;

                $mitraPerusahaan->aktif =
                    $request->boolean('aktif');

                /*
                 * Generate ulang slug hanya jika nama perusahaan Indonesia berubah.
                 */
                if ($mitraPerusahaan->isDirty('nama_perusahaan')) {
                    $mitraPerusahaan->slug =
                        MitraPerusahaan::generateUniqueSlug(
                            $validated['nama_perusahaan'],
                            $mitraPerusahaan->id
                        );
                }

                if ($newLogoPath) {
                    $mitraPerusahaan->logo = $newLogoPath;
                }

                $mitraPerusahaan->save();
            });

            /*
             * Hapus logo lama setelah database berhasil diperbarui.
             */
            if ($newLogoPath && $oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
        } catch (Throwable $e) {
            /*
             * Jika database gagal, hapus file baru agar tidak menjadi
             * orphan file di storage.
             */
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $e;
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Mitra perusahaan berhasil diperbarui.',
        ]);
    }

    /**
     * Remove the specified company partner.
     */
    public function destroy(
        MitraPerusahaan $mitraPerusahaan
    ): RedirectResponse {
        $deletedUrutan = $mitraPerusahaan->urutan;
        $logo = $mitraPerusahaan->logo;

        DB::transaction(function () use (
            $mitraPerusahaan,
            $deletedUrutan
        ): void {
            $mitraPerusahaan->delete();

            MitraPerusahaan::query()
                ->where('urutan', '>', $deletedUrutan)
                ->decrement('urutan');
        });

        /*
         * Hapus file setelah database berhasil dihapus.
         */
        if ($logo) {
            Storage::disk('public')->delete($logo);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Mitra perusahaan berhasil dihapus.',
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleAktif(
        MitraPerusahaan $mitraPerusahaan
    ): RedirectResponse {
        $mitraPerusahaan->update([
            'aktif' => ! $mitraPerusahaan->aktif,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => $mitraPerusahaan->aktif
                ? 'Mitra perusahaan berhasil diaktifkan.'
                : 'Mitra perusahaan berhasil dinonaktifkan.',
        ]);
    }

    /**
     * Move company partner up or down.
     */
    public function move(
        Request $request,
        MitraPerusahaan $mitraPerusahaan
    ): RedirectResponse {
        $validated = $request->validate([
            'direction' => [
                'required',
                Rule::in([
                    'up',
                    'down',
                ]),
            ],
        ]);

        $moved = false;

        DB::transaction(function () use (
            $validated,
            $mitraPerusahaan,
            &$moved
        ): void {
            $currentOrder = $mitraPerusahaan->urutan;

            if ($validated['direction'] === 'up') {
                $target = MitraPerusahaan::query()
                    ->where('urutan', '<', $currentOrder)
                    ->orderByDesc('urutan')
                    ->lockForUpdate()
                    ->first();
            } else {
                $target = MitraPerusahaan::query()
                    ->where('urutan', '>', $currentOrder)
                    ->orderBy('urutan')
                    ->lockForUpdate()
                    ->first();
            }

            if (! $target) {
                return;
            }

            $targetOrder = $target->urutan;

            $mitraPerusahaan->update([
                'urutan' => $targetOrder,
            ]);

            $target->update([
                'urutan' => $currentOrder,
            ]);

            $moved = true;
        });

        if (! $moved) {
            return back()->with('toast', [
                'type' => 'info',
                'message' => $validated['direction'] === 'up'
                    ? 'Mitra perusahaan sudah berada di urutan paling atas.'
                    : 'Mitra perusahaan sudah berada di urutan paling bawah.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Urutan mitra perusahaan berhasil diperbarui.',
        ]);
    }

    /**
     * Reorder company partner relative to another item.
     */
    public function reorder(
        Request $request,
        MitraPerusahaan $mitraPerusahaan
    ): RedirectResponse {
        $validated = $request->validate([
            'target_id' => [
                'required',
                'integer',
                'exists:mitra_perusahaans,id',
            ],
        ]);

        if (
            (int) $validated['target_id'] ===
            (int) $mitraPerusahaan->id
        ) {
            return back()->with('toast', [
                'type' => 'info',
                'message' => 'Mitra perusahaan sudah berada pada posisi tersebut.',
            ]);
        }

        DB::transaction(function () use (
            $validated,
            $mitraPerusahaan
        ): void {
            $target = MitraPerusahaan::query()
                ->whereKey($validated['target_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $oldOrder = $mitraPerusahaan->urutan;
            $newOrder = $target->urutan;

            if ($oldOrder < $newOrder) {
                MitraPerusahaan::query()
                    ->whereBetween(
                        'urutan',
                        [
                            $oldOrder + 1,
                            $newOrder,
                        ]
                    )
                    ->decrement('urutan');
            } else {
                MitraPerusahaan::query()
                    ->whereBetween(
                        'urutan',
                        [
                            $newOrder,
                            $oldOrder - 1,
                        ]
                    )
                    ->increment('urutan');
            }

            $mitraPerusahaan->update([
                'urutan' => $newOrder,
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Urutan mitra perusahaan berhasil diperbarui.',
        ]);
    }
}
