<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StrukturPerusahaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StrukturPerusahaanController extends Controller
{
    /**
     * Display a listing of struktur perusahaan.
     */
    public function index(Request $request): Response
    {
        $struktur = StrukturPerusahaan::query()
            ->orderBy('urutan')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'admin/ProfilPerusahaan/StrukturPerusahaan',
            [
                'struktur' => $struktur,
            ]
        );
    }

    /**
     * Store a newly created struktur perusahaan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStruktur($request);

        $gambarPath = null;

        try {
            DB::transaction(function () use (
                $validated,
                $request,
                &$gambarPath
            ) {
                /*
                 * Upload gambar.
                 */
                if ($request->hasFile('gambar')) {
                    $gambarPath = $request
                        ->file('gambar')
                        ->store('struktur-perusahaan', 'public');
                }

                /*
                 * Tentukan urutan berikutnya.
                 */
                $nextUrutan = (
                    (int) StrukturPerusahaan::query()
                        ->max('urutan')
                ) + 1;

                /*
                 * Simpan data.
                 */
                StrukturPerusahaan::create([
                    'nama' => trim($validated['nama']),
                    'jabatan' => trim($validated['jabatan']),
                    'gambar' => $gambarPath,
                    'urutan' => $nextUrutan,
                    'aktif' => $validated['aktif'] ?? true,
                ]);
            });
        } catch (\Throwable $e) {
            /*
             * Jika database gagal, hapus gambar
             * yang sudah berhasil di-upload.
             */
            if ($gambarPath) {
                Storage::disk('public')->delete($gambarPath);
            }

            throw $e;
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Struktur perusahaan berhasil ditambahkan.',
        ]);
    }

    /**
     * Update the specified struktur perusahaan.
     */
    public function update(
        Request $request,
        StrukturPerusahaan $strukturPerusahaan
    ): RedirectResponse {
        $validated = $this->validateStruktur($request);

        /*
         * Simpan path gambar lama.
         */
        $oldGambar = $strukturPerusahaan->gambar;

        /*
         * Path gambar baru.
         */
        $newGambar = null;

        try {
            DB::transaction(function () use (
                $validated,
                $request,
                $strukturPerusahaan,
                &$newGambar
            ) {
                /*
                 * Upload gambar baru jika ada.
                 */
                if ($request->hasFile('gambar')) {
                    $newGambar = $request
                        ->file('gambar')
                        ->store('struktur-perusahaan', 'public');
                }

                /*
                 * Update database.
                 *
                 * Jika tidak ada gambar baru,
                 * gunakan gambar lama.
                 */
                $strukturPerusahaan->update([
                    'nama' => trim($validated['nama']),
                    'jabatan' => trim($validated['jabatan']),
                    'gambar' => $newGambar
                        ?? $strukturPerusahaan->gambar,
                    'aktif' => $validated['aktif']
                        ?? $strukturPerusahaan->aktif,
                ]);
            });
        } catch (\Throwable $e) {
            /*
             * Jika database gagal,
             * hapus gambar baru agar tidak menjadi
             * file yang tidak terpakai.
             */
            if ($newGambar) {
                Storage::disk('public')->delete($newGambar);
            }

            throw $e;
        }

        /*
         * Database sudah berhasil di-update.
         *
         * Baru hapus gambar lama agar tidak terjadi
         * broken image jika proses update gagal.
         */
        if ($newGambar && $oldGambar) {
            Storage::disk('public')->delete($oldGambar);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Struktur perusahaan berhasil diperbarui.',
        ]);
    }

    /**
     * Remove the specified struktur perusahaan.
     */
    public function destroy(
        StrukturPerusahaan $strukturPerusahaan
    ): RedirectResponse {
        /*
         * Simpan path gambar sebelum data dihapus.
         */
        $gambar = $strukturPerusahaan->gambar;

        /*
         * Simpan urutan sebelum data dihapus.
         */
        $deletedOrder = $strukturPerusahaan->urutan;

        DB::transaction(function () use ($strukturPerusahaan) {
            $strukturPerusahaan->delete();
        });

        /*
         * Hapus file gambar setelah database berhasil.
         */
        if ($gambar) {
            Storage::disk('public')->delete($gambar);
        }

        /*
         * Kurangi urutan setelah data yang bersangkutan.
         */
        StrukturPerusahaan::query()
            ->where('urutan', '>', $deletedOrder)
            ->decrement('urutan');

        /*
         * Pastikan urutan tetap rapi.
         */
        $this->normalizeOrder();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Struktur perusahaan berhasil dihapus.',
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleAktif(
        StrukturPerusahaan $strukturPerusahaan
    ): RedirectResponse {
        $strukturPerusahaan->update([
            'aktif' => ! $strukturPerusahaan->aktif,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => $strukturPerusahaan->aktif
                ? 'Struktur perusahaan berhasil diaktifkan.'
                : 'Struktur perusahaan berhasil dinonaktifkan.',
        ]);
    }

    /**
     * Move struktur perusahaan order.
     */
    public function move(
        Request $request,
        StrukturPerusahaan $strukturPerusahaan
    ): RedirectResponse {
        $validated = $request->validate([
            'direction' => [
                'required',
                'in:up,down',
            ],
        ]);

        $direction = $validated['direction'];

        /*
         * Cari item tetangga.
         */
        if ($direction === 'up') {
            $neighbor = StrukturPerusahaan::query()
                ->where('urutan', '<', $strukturPerusahaan->urutan)
                ->orderByDesc('urutan')
                ->orderByDesc('id')
                ->first();
        } else {
            $neighbor = StrukturPerusahaan::query()
                ->where('urutan', '>', $strukturPerusahaan->urutan)
                ->orderBy('urutan')
                ->orderBy('id')
                ->first();
        }

        /*
         * Jika tidak ada tetangga.
         */
        if (! $neighbor) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => $direction === 'up'
                    ? 'Struktur perusahaan sudah berada di urutan paling atas.'
                    : 'Struktur perusahaan sudah berada di urutan paling bawah.',
            ]);
        }

        /*
         * Tukar urutan.
         */
        DB::transaction(function () use (
            $strukturPerusahaan,
            $neighbor
        ) {
            $currentOrder = $strukturPerusahaan->urutan;

            $strukturPerusahaan->update([
                'urutan' => $neighbor->urutan,
            ]);

            $neighbor->update([
                'urutan' => $currentOrder,
            ]);

            /*
             * Pastikan urutan tetap 1, 2, 3, dst.
             */
            $this->normalizeOrder();
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Urutan struktur perusahaan berhasil diperbarui.',
        ]);
    }

    /**
     * Normalize struktur perusahaan order.
     */
    private function normalizeOrder(): void
    {
        $items = StrukturPerusahaan::query()
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        foreach ($items as $index => $item) {
            $urutan = $index + 1;

            if ($item->urutan !== $urutan) {
                $item->updateQuietly([
                    'urutan' => $urutan,
                ]);
            }
        }
    }

    /**
     * Validate struktur perusahaan request.
     */
    private function validateStruktur(Request $request): array
    {
        return $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
            ],

            'jabatan' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * Maksimal 1 MB.
             *
             * Laravel menggunakan satuan KB:
             * 1024 KB = 1 MB.
             */
            'gambar' => [
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
    }
}
