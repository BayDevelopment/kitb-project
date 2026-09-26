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
                 * Upload gambar terlebih dahulu.
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
                 * Simpan data ke database.
                 */
                StrukturPerusahaan::create([
                    'nama' => $validated['nama'],
                    'jabatan' => $validated['jabatan'],
                    'gambar' => $gambarPath,
                    'urutan' => $nextUrutan,
                    'aktif' => $validated['aktif'] ?? true,
                ]);
            });
        } catch (\Throwable $e) {
            /*
             * Jika database gagal, hapus gambar
             * yang sudah terlanjur di-upload.
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
                 * Jika user upload gambar baru,
                 * upload gambar baru terlebih dahulu.
                 */
                if ($request->hasFile('gambar')) {
                    $newGambar = $request
                        ->file('gambar')
                        ->store('struktur-perusahaan', 'public');
                }

                /*
                 * Update database.
                 *
                 * Jika tidak ada gambar baru:
                 * gunakan gambar lama.
                 *
                 * Jika ada gambar baru:
                 * gunakan gambar baru.
                 */
                $strukturPerusahaan->update([
                    'nama' => $validated['nama'],
                    'jabatan' => $validated['jabatan'],
                    'gambar' => $newGambar
                        ?? $strukturPerusahaan->gambar,
                    'aktif' => $validated['aktif']
                        ?? $strukturPerusahaan->aktif,
                ]);
            });
        } catch (\Throwable $e) {
            /*
             * Jika update database gagal,
             * hapus gambar baru agar tidak menjadi
             * file yang tidak terpakai.
             */
            if ($newGambar) {
                Storage::disk('public')->delete($newGambar);
            }

            throw $e;
        }

        /*
         * DATABASE SUDAH BERHASIL DIUPDATE.
         *
         * Baru hapus gambar lama.
         */
        if ($newGambar && $oldGambar) {
            Storage::disk('public')->delete($oldGambar);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Struktur perusahaan berhasil diperbarui.',
        ]);
    }

    public function destroy(
        StrukturPerusahaan $strukturPerusahaan
    ): RedirectResponse {
        /*
         * Simpan path gambar sebelum data dihapus.
         */
        $gambar = $strukturPerusahaan->gambar;

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
         * Rapikan kembali nomor urutan.
         */
        $this->normalizeOrder();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Struktur perusahaan berhasil dihapus.',
        ]);
    }

    public function toggleAktif(
        StrukturPerusahaan $strukturPerusahaan
    ): RedirectResponse {
        $strukturPerusahaan->update([
            'aktif' => ! $strukturPerusahaan->aktif,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => $strukturPerusahaan->aktif
                ? 'Struktur perusahaan diaktifkan.'
                : 'Struktur perusahaan dinonaktifkan.',
        ]);
    }

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

        DB::transaction(function () use (
            $strukturPerusahaan,
            $validated
        ) {
            $items = StrukturPerusahaan::query()
                ->orderBy('urutan')
                ->orderBy('id')
                ->get()
                ->values();

            $currentIndex = $items->search(
                fn(StrukturPerusahaan $item): bool =>
                $item->id === $strukturPerusahaan->id
            );

            if ($currentIndex === false) {
                return;
            }

            $targetIndex = $validated['direction'] === 'up'
                ? $currentIndex - 1
                : $currentIndex + 1;

            if (
                $targetIndex < 0 ||
                $targetIndex >= $items->count()
            ) {
                return;
            }

            $current = $items[$currentIndex];
            $target = $items[$targetIndex];

            $currentUrutan = $current->urutan;
            $targetUrutan = $target->urutan;

            $current->update([
                'urutan' => $targetUrutan,
            ]);

            $target->update([
                'urutan' => $currentUrutan,
            ]);

            $this->normalizeOrder();
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Urutan struktur perusahaan berhasil diperbarui.',
        ]);
    }

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
