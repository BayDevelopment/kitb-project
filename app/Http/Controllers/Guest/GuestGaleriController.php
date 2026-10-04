<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestGaleriController extends Controller
{
    /**
     * Menampilkan galeri yang aktif untuk halaman publik.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $kategori = $request->string('kategori')->trim()->toString();

        $galeris = Galeri::query()
            ->aktif()
            ->kategori($kategori ?: null)
            ->when(
                $search,
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
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        /**
         * Ambil kategori hanya dari galeri aktif.
         */
        $kategoris = Galeri::query()
            ->aktif()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori')
            ->values();

        return Inertia::render('Galeri/Index', [
            'galeris' => $galeris,
            'kategoris' => $kategoris,
            'filters' => [
                'search' => $search,
                'kategori' => $kategori,
            ],
        ]);
    }
}
