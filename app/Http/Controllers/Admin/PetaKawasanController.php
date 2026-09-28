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
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        $petaKawasan = PetaKawasan::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:150',
            ],
            'deskripsi' => [
                'nullable',
                'string',
            ],
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

    public function update(
        Request $request,
        PetaKawasan $petaKawasan
    ): RedirectResponse {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:150',
            ],
            'deskripsi' => [
                'nullable',
                'string',
            ],
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
         * Jangan gunakan hapus_gambar lagi.
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
         * Hapus file lama hanya setelah database berhasil diperbarui.
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

    public function destroy(PetaKawasan $petaKawasan): RedirectResponse
    {
        $gambar = $petaKawasan->gambar;

        try {
            DB::transaction(function () use ($petaKawasan): void {
                $petaKawasan->delete();
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->with('toast', [
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

    public function moveUp(
        PetaKawasan $petaKawasan
    ): RedirectResponse {
        return $this->move($petaKawasan, 'up');
    }

    public function moveDown(
        PetaKawasan $petaKawasan
    ): RedirectResponse {
        return $this->move($petaKawasan, 'down');
    }

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

                $neighbor->update([
                    'urutan' => $currentOrder,
                ]);

                $petaKawasan->update([
                    'urutan' => $neighbor->getOriginal('urutan'),
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
