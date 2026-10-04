<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestBeritaController extends Controller
{
    /**
     * Menampilkan daftar berita yang sudah dipublikasikan.
     */
    public function index(Request $request): Response
    {
        $beritas = Berita::query()
            ->published()
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->string('search')->trim();

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('judul', 'like', "%{$search}%")
                            ->orWhere('excerpt', 'like', "%{$search}%")
                            ->orWhere('kategori', 'like', "%{$search}%")
                            ->orWhere('penulis', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->filled('kategori'),
                fn($query) => $query->where(
                    'kategori',
                    $request->string('kategori')->trim()
                )
            )
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        $kategoris = Berita::query()
            ->published()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori')
            ->values();

        return Inertia::render('Berita/Index', [   // sebelumnya 'Galeri/Index'
            'beritas'   => $beritas,
            'kategoris' => $kategoris,
            'filters'   => [
                'search'   => $request->string('search')->toString(),
                'kategori' => $request->string('kategori')->toString(),
            ],
        ]);
    }

    /**
     * Detail berita (dipakai tombol "Baca Berita Lengkap").
     */
    public function show(Berita $berita): Response
    {
        abort_unless(
            Berita::query()->published()->whereKey($berita->getKey())->exists(),
            404
        );

        $berita->increment('views');

        return Inertia::render('Berita/Show', [
            'berita' => $berita,
        ]);
    }
}
