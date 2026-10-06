<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfilKawasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfilKawasanController extends Controller
{
    /**
     * Menampilkan daftar profil kawasan.
     */
    public function index(Request $request): Response
    {
        $profilKawasans = ProfilKawasan::query()
            ->when(
                $request->filled('search'),
                function ($query) use ($request): void {
                    $search = trim((string) $request->input('search'));

                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('judul', 'like', "%{$search}%")
                            ->orWhere('lokasi', 'like', "%{$search}%")
                            ->orWhere('deskripsi', 'like', "%{$search}%")
                            ->orWhere('deskripsi_en', 'like', "%{$search}%")
                            ->orWhere('deskripsi_zh', 'like', "%{$search}%");
                    });
                }
            )
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'admin/Kawasan/ProfilKawasan',
            [
                'profilKawasans' => $profilKawasans,

                'filters' => [
                    'search' => $request->input('search', ''),
                ],
            ]
        );
    }

    /**
     * Menyimpan profil kawasan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProfilKawasan($request);

        DB::transaction(function () use ($request, &$validated): void {
            /*
             * Generate slug otomatis dari judul.
             */
            $validated['slug'] = ProfilKawasan::generateUniqueSlug(
                $validated['judul']
            );

            /*
             * Status default aktif.
             */
            $validated['status'] = $request->boolean(
                'status',
                true
            );

            /*
             * Upload gambar.
             */
            if ($request->hasFile('gambar')) {
                $validated['gambar'] = $request
                    ->file('gambar')
                    ->store('profil-kawasan', 'public');
            }

            ProfilKawasan::create($validated);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Profil kawasan berhasil ditambahkan.',
        ]);
    }

    /**
     * Memperbarui profil kawasan.
     */
    public function update(
        Request $request,
        ProfilKawasan $profilKawasan
    ): RedirectResponse {
        $validated = $this->validateProfilKawasan($request);

        DB::transaction(function () use (
            $request,
            $profilKawasan,
            &$validated
        ): void {
            /*
             * Slug hanya dibuat ulang jika judul berubah.
             */
            if ($profilKawasan->judul !== $validated['judul']) {
                $validated['slug'] = ProfilKawasan::generateUniqueSlug(
                    $validated['judul'],
                    $profilKawasan->id
                );
            }

            /*
             * Status dari checkbox frontend.
             */
            $validated['status'] = $request->boolean('status');

            /*
             * Upload gambar baru.
             */
            if ($request->hasFile('gambar')) {
                $oldImage = $profilKawasan->gambar;

                $validated['gambar'] = $request
                    ->file('gambar')
                    ->store('profil-kawasan', 'public');

                /*
                 * Hapus gambar lama setelah gambar baru
                 * berhasil disimpan.
                 */
                if (
                    $oldImage &&
                    Storage::disk('public')->exists($oldImage)
                ) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            /*
             * Hapus gambar lama jika diminta frontend
             * tanpa upload gambar baru.
             */
            if (
                $request->boolean('remove_gambar') &&
                ! $request->hasFile('gambar')
            ) {
                if (
                    $profilKawasan->gambar &&
                    Storage::disk('public')->exists(
                        $profilKawasan->gambar
                    )
                ) {
                    Storage::disk('public')->delete(
                        $profilKawasan->gambar
                    );
                }

                $validated['gambar'] = null;
            }

            $profilKawasan->update($validated);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Profil kawasan berhasil diperbarui.',
        ]);
    }

    /**
     * Menghapus profil kawasan.
     */
    public function destroy(
        ProfilKawasan $profilKawasan
    ): RedirectResponse {
        DB::transaction(function () use ($profilKawasan): void {
            /*
             * Hapus gambar dari storage sebelum record
             * database dihapus.
             */
            if (
                $profilKawasan->gambar &&
                Storage::disk('public')->exists(
                    $profilKawasan->gambar
                )
            ) {
                Storage::disk('public')->delete(
                    $profilKawasan->gambar
                );
            }

            $profilKawasan->delete();
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Profil kawasan berhasil dihapus.',
        ]);
    }

    /**
     * Mengubah status aktif/nonaktif.
     */
    public function toggleAktif(
        ProfilKawasan $profilKawasan
    ): RedirectResponse {
        $profilKawasan->update([
            'status' => ! $profilKawasan->status,
        ]);

        $status = $profilKawasan->status
            ? 'diaktifkan'
            : 'dinonaktifkan';

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Profil kawasan berhasil {$status}.",
        ]);
    }

    /**
     * Validasi data profil kawasan.
     */
    private function validateProfilKawasan(
        Request $request
    ): array {
        return $request->validate([
            /*
             * =========================================================
             * INFORMASI UTAMA
             * =========================================================
             */

            'judul' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * =========================================================
             * DESKRIPSI MULTILINGUAL
             * =========================================================
             */

            'deskripsi' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'deskripsi_en' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'deskripsi_zh' => [
                'nullable',
                'string',
                'max:10000',
            ],

            /*
             * =========================================================
             * INFORMASI KAWASAN
             * =========================================================
             */

            'luas_kawasan' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],

            'lokasi' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * =========================================================
             * KOORDINAT KAWASAN
             * =========================================================
             */

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            /*
             * =========================================================
             * BATAS KAWASAN
             *
             * Disimpan dalam kolom JSON.
             * Frontend mengirim GeoJSON dalam bentuk JSON string.
             * =========================================================
             */

            'batas_kawasan' => [
                'nullable',
                'json',
            ],

            /*
             * =========================================================
             * TAHUN & STATUS
             * =========================================================
             */

            'tahun_berdiri' => [
                'nullable',
                'integer',
                'min:1800',
                'max:9999',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            /*
             * =========================================================
             * GAMBAR
             * =========================================================
             */

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024',
            ],

            /*
             * =========================================================
             * HAPUS GAMBAR
             * =========================================================
             */

            'remove_gambar' => [
                'nullable',
                'boolean',
            ],
        ]);
    }
}
