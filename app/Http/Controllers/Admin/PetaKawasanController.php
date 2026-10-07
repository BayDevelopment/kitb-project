<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PetaKawasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PetaKawasanController extends Controller
{
    /**
     * Display a listing of peta kawasan.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $petaKawasan = PetaKawasan::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhere('nama_en', 'like', "%{$search}%")
                        ->orWhere('deskripsi_en', 'like', "%{$search}%")
                        ->orWhere('nama_zh', 'like', "%{$search}%")
                        ->orWhere('deskripsi_zh', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/Kawasan/PetaKawasan', [
            'petaKawasans' => $petaKawasan,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Store a newly created peta kawasan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Bahasa Indonesia
            'nama' => [
                'required',
                'string',
                'max:150',
            ],
            'deskripsi' => [
                'nullable',
                'string',
            ],

            // English
            'nama_en' => [
                'nullable',
                'string',
                'max:150',
            ],
            'deskripsi_en' => [
                'nullable',
                'string',
            ],

            // Chinese
            'nama_zh' => [
                'nullable',
                'string',
                'max:150',
            ],
            'deskripsi_zh' => [
                'nullable',
                'string',
            ],

            // General
            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'urutan' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'aktif' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['nama'] = trim($validated['nama']);

        $validated['deskripsi'] = isset($validated['deskripsi'])
            ? trim($validated['deskripsi'])
            : null;

        $validated['nama_en'] = isset($validated['nama_en'])
            ? trim($validated['nama_en'])
            : null;

        $validated['deskripsi_en'] = isset($validated['deskripsi_en'])
            ? trim($validated['deskripsi_en'])
            : null;

        $validated['nama_zh'] = isset($validated['nama_zh'])
            ? trim($validated['nama_zh'])
            : null;

        $validated['deskripsi_zh'] = isset($validated['deskripsi_zh'])
            ? trim($validated['deskripsi_zh'])
            : null;

        // Slug selalu berdasarkan nama Bahasa Indonesia.
        $validated['slug'] = PetaKawasan::generateUniqueSlug(
            $validated['nama']
        );

        $validated['urutan'] = $validated['urutan'] ?? 0;
        $validated['aktif'] = $request->boolean('aktif');

        $uploadedPath = null;

        try {
            if ($request->hasFile('gambar')) {
                $uploadedPath = $request
                    ->file('gambar')
                    ->store('peta-kawasan', 'public');

                $validated['gambar'] = $uploadedPath;
            }

            DB::transaction(function () use ($validated): void {
                PetaKawasan::create($validated);
            });
        } catch (Throwable $e) {
            if ($uploadedPath !== null) {
                Storage::disk('public')->delete($uploadedPath);
            }

            report($e);

            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Peta kawasan gagal ditambahkan.',
                ]);
        }

        return redirect()
            ->route('kawasan.peta-kawasan')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Peta kawasan berhasil ditambahkan.',
            ]);
    }

    /**
     * Update the specified peta kawasan.
     */
    public function update(
        Request $request,
        PetaKawasan $petaKawasan
    ): RedirectResponse {
        $validated = $request->validate([
            // Bahasa Indonesia
            'nama' => [
                'required',
                'string',
                'max:150',
            ],
            'deskripsi' => [
                'nullable',
                'string',
            ],

            // English
            'nama_en' => [
                'nullable',
                'string',
                'max:150',
            ],
            'deskripsi_en' => [
                'nullable',
                'string',
            ],

            // Chinese
            'nama_zh' => [
                'nullable',
                'string',
                'max:150',
            ],
            'deskripsi_zh' => [
                'nullable',
                'string',
            ],

            // General
            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'urutan' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'aktif' => [
                'nullable',
                'boolean',
            ],
            'remove_gambar' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['nama'] = trim($validated['nama']);

        $validated['deskripsi'] = isset($validated['deskripsi'])
            ? trim($validated['deskripsi'])
            : null;

        $validated['nama_en'] = isset($validated['nama_en'])
            ? trim($validated['nama_en'])
            : null;

        $validated['deskripsi_en'] = isset($validated['deskripsi_en'])
            ? trim($validated['deskripsi_en'])
            : null;

        $validated['nama_zh'] = isset($validated['nama_zh'])
            ? trim($validated['nama_zh'])
            : null;

        $validated['deskripsi_zh'] = isset($validated['deskripsi_zh'])
            ? trim($validated['deskripsi_zh'])
            : null;

        // Regenerate slug jika nama Indonesia berubah.
        if ($petaKawasan->nama !== $validated['nama']) {
            $validated['slug'] = PetaKawasan::generateUniqueSlug(
                $validated['nama'],
                $petaKawasan->id
            );
        }

        $validated['urutan'] = $validated['urutan'] ?? 0;
        $validated['aktif'] = $request->boolean('aktif');

        /*
         * Vue mengirim remove_gambar.
         * Jangan gunakan hapus_gambar.
         */
        $removeGambar = $request->boolean('remove_gambar');

        $gambarLama = $petaKawasan->gambar;
        $gambarBaru = null;

        unset($validated['remove_gambar']);

        try {
            if ($request->hasFile('gambar')) {
                $gambarBaru = $request
                    ->file('gambar')
                    ->store('peta-kawasan', 'public');

                $validated['gambar'] = $gambarBaru;
            } elseif ($removeGambar && $gambarLama !== null) {
                $validated['gambar'] = null;
            }

            DB::transaction(function () use (
                $petaKawasan,
                $validated
            ): void {
                $petaKawasan->update($validated);
            });
        } catch (Throwable $e) {
            if ($gambarBaru !== null) {
                Storage::disk('public')->delete($gambarBaru);
            }

            report($e);

            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Peta kawasan gagal diperbarui.',
                ]);
        }

        /*
         * Hapus file lama hanya setelah database
         * berhasil diperbarui.
         */
        if (
            $gambarLama !== null &&
            (
                $gambarBaru !== null ||
                ($removeGambar && ($validated['gambar'] ?? null) === null)
            )
        ) {
            Storage::disk('public')->delete($gambarLama);
        }

        return redirect()
            ->route('kawasan.peta-kawasan')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Peta kawasan berhasil diperbarui.',
            ]);
    }

    /**
     * Remove the specified peta kawasan.
     */
    public function destroy(PetaKawasan $petaKawasan): RedirectResponse
    {
        $gambar = $petaKawasan->gambar;

        try {
            DB::transaction(function () use ($petaKawasan): void {
                $petaKawasan->delete();
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Peta kawasan gagal dihapus.',
            ]);
        }

        if ($gambar !== null) {
            Storage::disk('public')->delete($gambar);
        }

        return redirect()
            ->route('kawasan.peta-kawasan')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Peta kawasan berhasil dihapus.',
            ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleAktif(
        PetaKawasan $petaKawasan
    ): RedirectResponse {
        try {
            $petaKawasan->update([
                'aktif' => ! $petaKawasan->aktif,
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Status peta kawasan gagal diperbarui.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => $petaKawasan->aktif
                ? 'Peta kawasan berhasil diaktifkan.'
                : 'Peta kawasan berhasil dinonaktifkan.',
        ]);
    }

    /**
     * Move peta kawasan up.
     */
    public function moveUp(
        PetaKawasan $petaKawasan
    ): RedirectResponse {
        return $this->move($petaKawasan, 'up');
    }

    /**
     * Move peta kawasan down.
     */
    public function moveDown(
        PetaKawasan $petaKawasan
    ): RedirectResponse {
        return $this->move($petaKawasan, 'down');
    }

    /**
     * Change ordering.
     */
    private function move(
        PetaKawasan $petaKawasan,
        string $direction
    ): RedirectResponse {
        try {
            DB::transaction(function () use (
                $petaKawasan,
                $direction
            ): void {
                $currentOrder = $petaKawasan->urutan;

                if ($direction === 'up') {
                    $neighbor = PetaKawasan::query()
                        ->where('urutan', '<', $currentOrder)
                        ->orderByDesc('urutan')
                        ->first();
                } else {
                    $neighbor = PetaKawasan::query()
                        ->where('urutan', '>', $currentOrder)
                        ->orderBy('urutan')
                        ->first();
                }

                if (! $neighbor) {
                    return;
                }

                $temporaryOrder = -1 * $petaKawasan->id;

                $petaKawasan->update([
                    'urutan' => $temporaryOrder,
                ]);

                $neighborOrder = $neighbor->urutan;

                $neighbor->update([
                    'urutan' => $currentOrder,
                ]);

                $petaKawasan->update([
                    'urutan' => $neighborOrder,
                ]);
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Urutan peta kawasan gagal diperbarui.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Urutan peta kawasan berhasil diperbarui.',
        ]);
    }
}
