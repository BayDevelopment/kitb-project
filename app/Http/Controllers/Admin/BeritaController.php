<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BeritaController extends Controller
{
    /**
     * Display a listing of berita.
     */
    public function index(Request $request): Response
    {
        $beritas = Berita::query()
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->search;

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('judul', 'like', "%{$search}%")
                            ->orWhere('kategori', 'like', "%{$search}%")
                            ->orWhere('penulis', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->filled('status') &&
                    $request->status !== 'all',
                fn($query) => $query->where(
                    'status',
                    $request->status
                )
            )
            ->when(
                $request->filled('kategori') &&
                    $request->kategori !== 'all',
                fn($query) => $query->where(
                    'kategori',
                    $request->kategori
                )
            )
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/PusatInformasi/Berita', [
            'beritas' => $beritas,

            'filters' => [
                'search' => $request->search,
                'status' => $request->status ?? 'all',
                'kategori' => $request->kategori ?? 'all',
            ],

            'categories' => Berita::query()
                ->whereNotNull('kategori')
                ->where('kategori', '!=', '')
                ->select('kategori')
                ->distinct()
                ->orderBy('kategori')
                ->pluck('kategori'),
        ]);
    }

    /**
     * Store a newly created berita.
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
                'unique:beritas,slug',
            ],

            'excerpt' => [
                'nullable',
                'string',
            ],

            'konten' => [
                'required',
                'string',
            ],

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024',
            ],

            'kategori' => [
                'nullable',
                'string',
                'max:100',
            ],

            'penulis' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'in:draft,published,archived',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'is_featured' => [
                'boolean',
            ],
        ]);

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? $validated['judul']
        );

        if (
            $validated['status'] === 'published' &&
            empty($validated['published_at'])
        ) {
            $validated['published_at'] = now();
        }

        if ($validated['status'] !== 'published') {
            $validated['published_at'] = null;
            $validated['is_featured'] = false;
        }

        $validated['is_featured'] = (
            $validated['status'] === 'published'
            && ($validated['is_featured'] ?? false)
        );

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request
                ->file('gambar')
                ->store('berita', 'public');
        }

        Berita::create($validated);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Berita berhasil ditambahkan.',
        ]);
    }

    /**
     * Update the specified berita.
     */
    public function update(
        Request $request,
        Berita $berita
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
                'unique:beritas,slug,' . $berita->id,
            ],

            'excerpt' => [
                'nullable',
                'string',
            ],

            'konten' => [
                'required',
                'string',
            ],

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024',
            ],

            'kategori' => [
                'nullable',
                'string',
                'max:100',
            ],

            'penulis' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'in:draft,published,archived',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],

            'is_featured' => [
                'boolean',
            ],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = $this->generateUniqueSlug(
                $validated['judul'],
                $berita->id
            );
        }

        if (
            $validated['status'] === 'published' &&
            empty($validated['published_at'])
        ) {
            $validated['published_at'] =
                $berita->published_at ?? now();
        }

        if ($validated['status'] !== 'published') {
            $validated['published_at'] = null;
            $validated['is_featured'] = false;
        }

        if ($request->hasFile('gambar')) {
            if (
                $berita->gambar &&
                Storage::disk('public')->exists($berita->gambar)
            ) {
                Storage::disk('public')->delete(
                    $berita->gambar
                );
            }

            $validated['gambar'] = $request
                ->file('gambar')
                ->store('berita', 'public');
        }

        $berita->update($validated);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Berita berhasil diperbarui.',
        ]);
    }

    /**
     * Remove the specified berita.
     */
    public function destroy(
        Berita $berita
    ): RedirectResponse {
        if (
            $berita->gambar &&
            Storage::disk('public')->exists($berita->gambar)
        ) {
            Storage::disk('public')->delete(
                $berita->gambar
            );
        }

        $berita->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Berita berhasil dihapus.',
        ]);
    }

    /**
     * Toggle status publish / draft.
     */
    public function toggleStatus(
        Berita $berita
    ): RedirectResponse {
        if ($berita->status === 'published') {
            $berita->update([
                'status' => 'draft',
                'published_at' => null,
                'is_featured' => false,
            ]);

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Berita berhasil diubah menjadi draft.',
            ]);
        }

        $berita->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Berita berhasil dipublikasikan.',
        ]);
    }

    /**
     * Toggle berita unggulan.
     */
    public function toggleFeatured(
        Berita $berita
    ): RedirectResponse {
        if ($berita->status !== 'published') {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Hanya berita yang sudah dipublikasikan yang dapat dijadikan berita unggulan.',
            ]);
        }

        $berita->update([
            'is_featured' => ! $berita->is_featured,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => $berita->is_featured
                ? 'Berita berhasil dijadikan unggulan.'
                : 'Berita berhasil dihapus dari berita unggulan.',
        ]);
    }

    /**
     * Generate unique slug.
     */
    private function generateUniqueSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $slug = Str::slug($value);
        $originalSlug = $slug;
        $counter = 1;

        while (
            Berita::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId,
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
