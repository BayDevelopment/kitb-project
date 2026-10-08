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
                    // Escape karakter wildcard LIKE agar "%" dan "_" dicari apa adanya.
                    $keyword = addcslashes(
                        $request->string('search')->trim()->toString(),
                        '%_\\'
                    );

                    $like = "%{$keyword}%";

                    $query->where(function ($query) use ($like) {
                        $query
                            // Bahasa Indonesia
                            ->where('judul_id', 'like', $like)
                            ->orWhere('excerpt_id', 'like', $like)
                            ->orWhere('konten_id', 'like', $like)

                            // English
                            ->orWhere('judul_en', 'like', $like)
                            ->orWhere('excerpt_en', 'like', $like)
                            ->orWhere('konten_en', 'like', $like)

                            // 中文
                            ->orWhere('judul_zh', 'like', $like)
                            ->orWhere('excerpt_zh', 'like', $like)
                            ->orWhere('konten_zh', 'like', $like)

                            // Informasi umum
                            ->orWhere('kategori', 'like', $like)
                            ->orWhere('penulis', 'like', $like);
                    });
                }
            )
            ->when(
                $request->filled('kategori'),
                fn($query) => $query->where(
                    'kategori',
                    $request->string('kategori')->trim()->toString()
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

        return Inertia::render('Berita/Index', [
            'beritas' => $beritas,
            'kategoris' => $kategoris,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'kategori' => $request->string('kategori')->toString(),
            ],
        ]);
    }

    /**
     * Menampilkan detail berita.
     *
     * Route harus memakai slug:
     *   Route::get('/berita/{berita:slug}', [GuestBeritaController::class, 'show']);
     */
    public function show(Request $request, Berita $berita): Response
    {
        abort_unless(
            Berita::query()
                ->published()
                ->whereKey($berita->getKey())
                ->exists(),
            404
        );

        // Hitung views sekali per sesi, tanpa mengubah updated_at.
        $sessionKey = "berita_viewed_{$berita->getKey()}";

        if (! $request->session()->has($sessionKey)) {
            Berita::query()
                ->whereKey($berita->getKey())
                ->toBase()
                ->increment('views');

            $berita->setAttribute('views', $berita->views + 1);

            $request->session()->put($sessionKey, true);
        }

        return Inertia::render('Berita/Show', [
            'berita' => $berita,
            'terkait' => $this->relatedNews($berita),
        ]);
    }

    /**
     * Berita terkait: kategori yang sama dulu, sisanya diisi berita terbaru.
     */
    private function relatedNews(Berita $berita, int $limit = 3)
    {
        $terkait = Berita::query()
            ->published()
            ->whereKeyNot($berita->getKey())
            ->when(
                $berita->kategori,
                fn($query, $kategori) => $query->where('kategori', $kategori)
            )
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();

        if ($terkait->count() < $limit) {
            $tambahan = Berita::query()
                ->published()
                ->whereKeyNot($berita->getKey())
                ->whereNotIn('id', $terkait->pluck('id'))
                ->orderByDesc('published_at')
                ->limit($limit - $terkait->count())
                ->get();

            $terkait = $terkait->concat($tambahan)->values();
        }

        return $terkait;
    }
}
