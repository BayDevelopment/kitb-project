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
     * Menampilkan galeri aktif untuk halaman publik.
     *
     * Pencarian mendukung:
     * - Bahasa Indonesia
     * - Bahasa Inggris
     * - Bahasa Mandarin
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
                            // Bahasa Indonesia
                            ->where('judul_id', 'like', "%{$search}%")
                            ->orWhere('deskripsi_id', 'like', "%{$search}%")
                            ->orWhere('alt_text_id', 'like', "%{$search}%")

                            // Bahasa Inggris
                            ->orWhere('judul_en', 'like', "%{$search}%")
                            ->orWhere('deskripsi_en', 'like', "%{$search}%")
                            ->orWhere('alt_text_en', 'like', "%{$search}%")

                            // Bahasa Mandarin
                            ->orWhere('judul_zh', 'like', "%{$search}%")
                            ->orWhere('deskripsi_zh', 'like', "%{$search}%")
                            ->orWhere('alt_text_zh', 'like', "%{$search}%")

                            // Field umum
                            ->orWhere('kategori', 'like', "%{$search}%");
                    });
                }
            )
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        /**
         * Mengambil kategori hanya dari galeri aktif.
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
