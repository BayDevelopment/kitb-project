<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Kawasan;

use App\Http\Controllers\Controller;
use App\Models\Infrastruktur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class InfrastrukturController extends Controller
{
    /**
     * Menampilkan daftar infrastruktur.
     */
    public function index(Request $request): Response
    {
        $infrastrukturs = Infrastruktur::query()
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/Kawasan/Infrastruktur', [
            'infrastrukturs' => $infrastrukturs,

            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    /**
     * Menyimpan infrastruktur baru.
     */
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
                'max:10000',
            ],

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
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

        try {
            DB::transaction(function () use ($request, $validated): void {
                $gambar = null;

                if ($request->hasFile('gambar')) {
                    $gambar = $request
                        ->file('gambar')
                        ->store('infrastruktur', 'public');
                }

                Infrastruktur::create([
                    'nama' => trim($validated['nama']),

                    'deskripsi' => isset($validated['deskripsi'])
                        ? trim($validated['deskripsi'])
                        : null,

                    'gambar' => $gambar,

                    /*
                     * Jika urutan 0, Model dapat menangani
                     * penentuan posisi berikutnya.
                     */
                    'urutan' => $validated['urutan'] ?? 0,

                    'aktif' => $validated['aktif'] ?? true,
                ]);
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Infrastruktur gagal ditambahkan.',
                ]);
        }

        return to_route('kawasan.infrastruktur')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Infrastruktur berhasil ditambahkan.',
            ]);
    }

    /**
     * Memperbarui infrastruktur.
     */
    public function update(
        Request $request,
        Infrastruktur $infrastruktur
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
                'max:10000',
            ],

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
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

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                $infrastruktur
            ): void {
                $oldGambar = $infrastruktur->gambar;
                $newGambar = $oldGambar;

                /*
                 * Upload gambar baru jika ada.
                 */
                if ($request->hasFile('gambar')) {
                    $newGambar = $request
                        ->file('gambar')
                        ->store('infrastruktur', 'public');
                }

                /*
                 * Hapus gambar jika diminta
                 * dan tidak ada gambar baru.
                 */
                if (
                    $request->boolean('remove_gambar')
                    && !$request->hasFile('gambar')
                ) {
                    $newGambar = null;
                }

                $infrastruktur->update([
                    'nama' => trim($validated['nama']),

                    'deskripsi' => isset($validated['deskripsi'])
                        ? trim($validated['deskripsi'])
                        : null,

                    'gambar' => $newGambar,

                    'urutan' => $validated['urutan'] ?? 0,

                    'aktif' => $validated['aktif'] ?? false,
                ]);

                /*
                 * Hapus gambar lama jika gambar diganti
                 * atau dihapus.
                 */
                if (
                    $oldGambar !== null
                    && $oldGambar !== $newGambar
                ) {
                    Storage::disk('public')->delete($oldGambar);
                }
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Infrastruktur gagal diperbarui.',
                ]);
        }

        return to_route('kawasan.infrastruktur')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Infrastruktur berhasil diperbarui.',
            ]);
    }

    /**
     * Menghapus infrastruktur.
     */
    public function destroy(
        Infrastruktur $infrastruktur
    ): RedirectResponse {
        try {
            DB::transaction(function () use ($infrastruktur): void {
                $gambar = $infrastruktur->gambar;
                $urutan = $infrastruktur->urutan;

                /*
                 * Hapus record.
                 */
                $infrastruktur->delete();

                /*
                 * Rapikan urutan setelah record dihapus.
                 */
                Infrastruktur::query()
                    ->where('urutan', '>', $urutan)
                    ->decrement('urutan');

                /*
                 * Hapus gambar dari storage.
                 */
                if ($gambar !== null) {
                    Storage::disk('public')->delete($gambar);
                }
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Infrastruktur gagal dihapus.',
            ]);
        }

        return to_route('kawasan.infrastruktur')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Infrastruktur berhasil dihapus.',
            ]);
    }

    /**
     * Mengaktifkan / menonaktifkan infrastruktur.
     */
    public function toggleAktif(
        Infrastruktur $infrastruktur
    ): RedirectResponse {
        try {
            $infrastruktur->update([
                'aktif' => !$infrastruktur->aktif,
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Status infrastruktur gagal diperbarui.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => $infrastruktur->aktif
                ? 'Infrastruktur berhasil diaktifkan.'
                : 'Infrastruktur berhasil dinonaktifkan.',
        ]);
    }

    /**
     * Memindahkan posisi ke atas.
     */
    public function moveUp(
        Infrastruktur $infrastruktur
    ): RedirectResponse {
        return $this->move($infrastruktur, 'up');
    }

    /**
     * Memindahkan posisi ke bawah.
     */
    public function moveDown(
        Infrastruktur $infrastruktur
    ): RedirectResponse {
        return $this->move($infrastruktur, 'down');
    }

    /**
     * Menukar urutan dengan item terdekat.
     */
    private function move(
        Infrastruktur $infrastruktur,
        string $direction
    ): RedirectResponse {
        try {
            $moved = false;

            DB::transaction(function () use (
                $infrastruktur,
                $direction,
                &$moved
            ): void {
                $operator = $direction === 'up'
                    ? '<'
                    : '>';

                $neighbor = Infrastruktur::query()
                    ->where(
                        'urutan',
                        $operator,
                        $infrastruktur->urutan
                    )
                    ->orderBy(
                        'urutan',
                        $direction === 'up'
                            ? 'desc'
                            : 'asc'
                    )
                    ->first();

                /*
                 * Tidak ada item di arah tersebut.
                 */
                if (!$neighbor) {
                    return;
                }

                $currentOrder = $infrastruktur->urutan;
                $neighborOrder = $neighbor->urutan;

                /*
                 * Tukar urutan.
                 */
                $infrastruktur->update([
                    'urutan' => $neighborOrder,
                ]);

                $neighbor->update([
                    'urutan' => $currentOrder,
                ]);

                $moved = true;
            });

            if (!$moved) {
                return back()->with('toast', [
                    'type' => 'info',
                    'message' => $direction === 'up'
                        ? 'Infrastruktur sudah berada di posisi paling atas.'
                        : 'Infrastruktur sudah berada di posisi paling bawah.',
                ]);
            }
        } catch (Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Urutan infrastruktur gagal diperbarui.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => $direction === 'up'
                ? 'Infrastruktur berhasil dipindahkan ke atas.'
                : 'Infrastruktur berhasil dipindahkan ke bawah.',
        ]);
    }
}
