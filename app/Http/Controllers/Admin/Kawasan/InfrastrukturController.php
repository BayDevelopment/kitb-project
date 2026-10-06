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
        $search = trim($request->string('search')->toString());

        $infrastrukturs = Infrastruktur::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('nama_en', 'like', "%{$search}%")
                        ->orWhere('nama_zh', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhere('deskripsi_en', 'like', "%{$search}%")
                        ->orWhere('deskripsi_zh', 'like', "%{$search}%");
                });
            })
            ->orderBy('urutan')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/Kawasan/Infrastruktur', [
            'infrastrukturs' => $infrastrukturs,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Menyimpan infrastruktur baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateInfrastruktur($request);

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
                    'nama_en' => isset($validated['nama_en'])
                        ? trim($validated['nama_en'])
                        : null,
                    'nama_zh' => isset($validated['nama_zh'])
                        ? trim($validated['nama_zh'])
                        : null,

                    'slug' => Infrastruktur::generateUniqueSlug(
                        trim($validated['nama'])
                    ),

                    'deskripsi' => isset($validated['deskripsi'])
                        ? trim($validated['deskripsi'])
                        : null,
                    'deskripsi_en' => isset($validated['deskripsi_en'])
                        ? trim($validated['deskripsi_en'])
                        : null,
                    'deskripsi_zh' => isset($validated['deskripsi_zh'])
                        ? trim($validated['deskripsi_zh'])
                        : null,

                    'gambar' => $gambar,
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
        $validated = $this->validateInfrastruktur($request);

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                $infrastruktur
            ): void {
                $oldGambar = $infrastruktur->gambar;
                $newGambar = $oldGambar;

                /*
                 * Generate slug baru jika nama berubah.
                 */
                $nama = trim($validated['nama']);

                $slug = $infrastruktur->slug;

                if ($infrastruktur->nama !== $nama) {
                    $slug = Infrastruktur::generateUniqueSlug(
                        $nama,
                        $infrastruktur->id
                    );
                }

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
                    'nama' => $nama,
                    'nama_en' => isset($validated['nama_en'])
                        ? trim($validated['nama_en'])
                        : null,
                    'nama_zh' => isset($validated['nama_zh'])
                        ? trim($validated['nama_zh'])
                        : null,

                    'slug' => $slug,

                    'deskripsi' => isset($validated['deskripsi'])
                        ? trim($validated['deskripsi'])
                        : null,
                    'deskripsi_en' => isset($validated['deskripsi_en'])
                        ? trim($validated['deskripsi_en'])
                        : null,
                    'deskripsi_zh' => isset($validated['deskripsi_zh'])
                        ? trim($validated['deskripsi_zh'])
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
                    ->orderBy('id', $direction === 'up' ? 'desc' : 'asc')
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

    /**
     * Validasi data infrastruktur.
     */
    private function validateInfrastruktur(Request $request): array
    {
        return $request->validate([
            // Indonesia
            'nama' => [
                'required',
                'string',
                'max:150',
            ],

            // English
            'nama_en' => [
                'nullable',
                'string',
                'max:150',
            ],

            // Chinese
            'nama_zh' => [
                'nullable',
                'string',
                'max:150',
            ],

            // Indonesia
            'deskripsi' => [
                'nullable',
                'string',
                'max:10000',
            ],

            // English
            'deskripsi_en' => [
                'nullable',
                'string',
                'max:10000',
            ],

            // Chinese
            'deskripsi_zh' => [
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
    }
}
