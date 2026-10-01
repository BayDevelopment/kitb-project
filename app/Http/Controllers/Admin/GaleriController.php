<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GaleriController extends Controller
{
    /**
     * Menampilkan daftar galeri.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $kategori = trim((string) $request->input('kategori', ''));
        $status = $request->input('status', '');

        $galeris = Galeri::query()
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('judul', 'like', "%{$search}%")
                            ->orWhere('deskripsi', 'like', "%{$search}%")
                            ->orWhere('kategori', 'like', "%{$search}%")
                            ->orWhere('alt_text', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $kategori !== '',
                fn($query) => $query->where('kategori', $kategori)
            )
            ->when(
                $status !== '',
                fn($query) => $query->where(
                    'status',
                    filter_var($status, FILTER_VALIDATE_BOOLEAN)
                )
            )
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        $kategoriOptions = Galeri::query()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori')
            ->values();

        return Inertia::render('admin/PusatInformasi/Galeri', [
            'galeris' => $galeris,

            'filters' => [
                'search' => $search,
                'kategori' => $kategori,
                'status' => $status,
            ],

            'kategoriOptions' => $kategoriOptions,
        ]);
    }

    /**
     * Menyimpan galeri baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:galeris,slug',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'kategori' => [
                'nullable',
                'string',
                'max:100',
            ],

            'gambar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'alt_text' => [
                'nullable',
                'string',
                'max:255',
            ],

            'tanggal' => [
                'nullable',
                'date',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            'urutan' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $gambarPath = null;

        try {
            $gambarPath = $request
                ->file('gambar')
                ->store('galeri', 'public');

            Galeri::create([
                'judul' => $validated['judul'],

                'slug' => $this->generateUniqueSlug(
                    $validated['slug'] ?? $validated['judul']
                ),

                'deskripsi' => $validated['deskripsi'] ?? null,

                'kategori' => $validated['kategori'] ?? null,

                'gambar' => $gambarPath,

                'alt_text' => $validated['alt_text']
                    ?? $validated['judul'],

                'tanggal' => $validated['tanggal'] ?? null,

                'status' => $validated['status'] ?? true,

                'urutan' => $validated['urutan'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            if ($gambarPath) {
                Storage::disk('public')->delete($gambarPath);
            }

            report($e);

            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Galeri gagal ditambahkan.',
                ]);
        }

        return redirect()
            ->route('pusat-informasi.galeri.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Galeri berhasil ditambahkan.',
            ]);
    }

    /**
     * Memperbarui galeri.
     */
    public function update(
        Request $request,
        Galeri $galeri
    ): RedirectResponse {
        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('galeris', 'slug')
                    ->ignore($galeri->id),
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'kategori' => [
                'nullable',
                'string',
                'max:100',
            ],

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'alt_text' => [
                'nullable',
                'string',
                'max:255',
            ],

            'tanggal' => [
                'nullable',
                'date',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            'urutan' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $oldImage = $galeri->gambar;
        $newImage = null;

        try {
            /*
             * Upload gambar baru jika ada.
             */
            if ($request->hasFile('gambar')) {
                $newImage = $request
                    ->file('gambar')
                    ->store('galeri', 'public');
            }

            $galeri->update([
                'judul' => $validated['judul'],

                'slug' => $this->generateUniqueSlug(
                    $validated['slug'] ?? $validated['judul'],
                    $galeri->id
                ),

                'deskripsi' => $validated['deskripsi'] ?? null,

                'kategori' => $validated['kategori'] ?? null,

                'alt_text' => $validated['alt_text']
                    ?? $validated['judul'],

                'tanggal' => $validated['tanggal'] ?? null,

                'status' => $validated['status']
                    ?? $galeri->status,

                'urutan' => $validated['urutan']
                    ?? $galeri->urutan,

                'gambar' => $newImage ?? $galeri->gambar,
            ]);

            /*
             * Hapus gambar lama setelah update database berhasil.
             */
            if ($newImage && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        } catch (\Throwable $e) {
            /*
             * Jika update gagal, hapus gambar baru
             * agar tidak meninggalkan file yatim.
             */
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            report($e);

            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Galeri gagal diperbarui.',
                ]);
        }

        return redirect()
            ->route('pusat-informasi.galeri.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Galeri berhasil diperbarui.',
            ]);
    }

    /**
     * Menghapus galeri.
     */
    public function destroy(
        Galeri $galeri
    ): RedirectResponse {
        try {
            $gambar = $galeri->gambar;

            DB::transaction(function () use ($galeri) {
                $urutan = $galeri->urutan;

                $galeri->delete();

                /*
                 * Merapikan urutan data setelah penghapusan.
                 */
                Galeri::query()
                    ->where('urutan', '>', $urutan)
                    ->decrement('urutan');
            });

            /*
             * Hapus file gambar setelah database berhasil.
             */
            if ($gambar) {
                Storage::disk('public')->delete($gambar);
            }
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Galeri gagal dihapus.',
            ]);
        }

        return redirect()
            ->route('pusat-informasi.galeri.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Galeri berhasil dihapus.',
            ]);
    }

    /**
     * Mengubah status aktif/nonaktif.
     */
    public function toggleAktif(
        Galeri $galeri
    ): RedirectResponse {
        try {
            $galeri->update([
                'status' => ! $galeri->status,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Status galeri gagal diperbarui.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => $galeri->status
                ? 'Galeri berhasil diaktifkan.'
                : 'Galeri berhasil dinonaktifkan.',
        ]);
    }

    /**
     * Memindahkan galeri ke atas/bawah.
     */
    public function move(
        Request $request,
        Galeri $galeri
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

        try {
            DB::transaction(function () use (
                $galeri,
                $validated
            ) {
                $operator = $validated['direction'] === 'up'
                    ? '<'
                    : '>';

                $orderDirection = $validated['direction'] === 'up'
                    ? 'desc'
                    : 'asc';

                $neighbor = Galeri::query()
                    ->where('id', '!=', $galeri->id)
                    ->where(
                        'urutan',
                        $operator,
                        $galeri->urutan
                    )
                    ->orderBy(
                        'urutan',
                        $orderDirection
                    )
                    ->first();

                /*
                 * Tidak ada item di posisi berikutnya.
                 */
                if (! $neighbor) {
                    return;
                }

                $currentOrder = $galeri->urutan;

                $galeri->update([
                    'urutan' => $neighbor->urutan,
                ]);

                $neighbor->update([
                    'urutan' => $currentOrder,
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Urutan galeri gagal diperbarui.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => $validated['direction'] === 'up'
                ? 'Galeri berhasil dipindahkan ke atas.'
                : 'Galeri berhasil dipindahkan ke bawah.',
        ]);
    }

    /**
     * Membuat slug unik.
     */
    private function generateUniqueSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $slug = Str::slug($value);

        if ($slug === '') {
            $slug = 'galeri';
        }

        $originalSlug = $slug;
        $counter = 1;

        while (
            Galeri::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->where(
                    'id',
                    '!=',
                    $ignoreId
                )
            )
            ->exists()
        ) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
