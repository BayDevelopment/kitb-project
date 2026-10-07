<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeluangInvestasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PeluangInvestasiController extends Controller
{
    /**
     * Display a listing of investment opportunities.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        $peluangInvestasi = PeluangInvestasi::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        // Bahasa Indonesia
                        ->where('judul', 'like', "%{$search}%")
                        ->orWhere('sektor_industri', 'like', "%{$search}%")
                        ->orWhere('lokasi', 'like', "%{$search}%")

                        // English
                        ->orWhere('judul_en', 'like', "%{$search}%")
                        ->orWhere('sektor_industri_en', 'like', "%{$search}%")
                        ->orWhere('lokasi_en', 'like', "%{$search}%")

                        // Chinese
                        ->orWhere('judul_zh', 'like', "%{$search}%")
                        ->orWhere('sektor_industri_zh', 'like', "%{$search}%")
                        ->orWhere('lokasi_zh', 'like', "%{$search}%");
                });
            })
            ->when(
                is_string($status) && $status !== '',
                fn($query) => $query->where('status', $status)
            )
            ->orderBy('urutan')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/HubunganInvestor/PeluangInvestasi', [
            'peluangInvestasi' => $peluangInvestasi,

            'filters' => [
                'search' => $search,
                'status' => $status,
            ],

            'statuses' => [
                [
                    'value' => 'tersedia',
                    'label' => 'Tersedia',
                ],
                [
                    'value' => 'proses',
                    'label' => 'Dalam Proses',
                ],
                [
                    'value' => 'terisi',
                    'label' => 'Terisi',
                ],
                [
                    'value' => 'ditutup',
                    'label' => 'Ditutup',
                ],
            ],
        ]);
    }

    /**
     * Store a newly created investment opportunity.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            /*
             * =========================================================
             * Bahasa Indonesia
             * =========================================================
             */
            'judul' => [
                'required',
                'string',
                'max:255',
            ],

            'sektor_industri' => [
                'nullable',
                'string',
                'max:255',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'lokasi' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * =========================================================
             * English
             * =========================================================
             */
            'judul_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sektor_industri_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'deskripsi_en' => [
                'nullable',
                'string',
            ],

            'lokasi_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * =========================================================
             * Chinese
             * =========================================================
             */
            'judul_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sektor_industri_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            'deskripsi_zh' => [
                'nullable',
                'string',
            ],

            'lokasi_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * =========================================================
             * General
             * =========================================================
             */
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:peluang_investasis,slug',
            ],

            'luas_lahan' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],

            'satuan_luas' => [
                'nullable',
                'string',
                'max:20',
            ],

            'status' => [
                'required',
                'string',
                'in:tersedia,proses,terisi,ditutup',
            ],

            'nilai_investasi' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999999999999.99',
            ],

            'mata_uang' => [
                'nullable',
                'string',
                'max:10',
            ],

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024',
            ],

            'urutan' => [
                'nullable',
                'integer',
                'min:0',
                'max:4294967295',
            ],

            'aktif' => [
                'nullable',
                'boolean',
            ],
        ]);

        $gambarPath = null;

        try {
            $data = [
                /*
                 * Bahasa Indonesia
                 */
                'judul' => $validated['judul'],
                'sektor_industri' => $validated['sektor_industri'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'lokasi' => $validated['lokasi'] ?? null,

                /*
                 * English
                 */
                'judul_en' => $validated['judul_en'] ?? null,
                'sektor_industri_en' => $validated['sektor_industri_en'] ?? null,
                'deskripsi_en' => $validated['deskripsi_en'] ?? null,
                'lokasi_en' => $validated['lokasi_en'] ?? null,

                /*
                 * Chinese
                 */
                'judul_zh' => $validated['judul_zh'] ?? null,
                'sektor_industri_zh' => $validated['sektor_industri_zh'] ?? null,
                'deskripsi_zh' => $validated['deskripsi_zh'] ?? null,
                'lokasi_zh' => $validated['lokasi_zh'] ?? null,

                /*
                 * General
                 */
                'slug' => $this->generateUniqueSlug(
                    $validated['slug'] ?? $validated['judul']
                ),

                'luas_lahan' => $validated['luas_lahan'] ?? null,

                'satuan_luas' => $validated['satuan_luas'] ?? 'Ha',

                'status' => $validated['status'],

                'nilai_investasi' => $validated['nilai_investasi'] ?? null,

                'mata_uang' => strtoupper(
                    $validated['mata_uang'] ?? 'IDR'
                ),

                'urutan' => $validated['urutan'] ?? 0,

                'aktif' => $validated['aktif'] ?? true,
            ];

            /*
             * Upload gambar
             */
            if ($request->hasFile('gambar')) {
                $gambarPath = $request->file('gambar')
                    ->store('peluang-investasi', 'public');

                $data['gambar'] = $gambarPath;
            }

            PeluangInvestasi::create($data);

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Peluang investasi berhasil ditambahkan.',
            ]);
        } catch (\Throwable $e) {
            /*
             * Hapus file jika database gagal
             */
            if ($gambarPath !== null) {
                Storage::disk('public')->delete($gambarPath);
            }

            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Peluang investasi gagal ditambahkan.',
            ]);
        }
    }

    /**
     * Update the specified investment opportunity.
     */
    public function update(
        Request $request,
        PeluangInvestasi $peluangInvestasi
    ): RedirectResponse {
        $validated = $request->validate([
            /*
             * =========================================================
             * Bahasa Indonesia
             * =========================================================
             */
            'judul' => [
                'required',
                'string',
                'max:255',
            ],

            'sektor_industri' => [
                'nullable',
                'string',
                'max:255',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'lokasi' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * =========================================================
             * English
             * =========================================================
             */
            'judul_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sektor_industri_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            'deskripsi_en' => [
                'nullable',
                'string',
            ],

            'lokasi_en' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * =========================================================
             * Chinese
             * =========================================================
             */
            'judul_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sektor_industri_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            'deskripsi_zh' => [
                'nullable',
                'string',
            ],

            'lokasi_zh' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * =========================================================
             * General
             * =========================================================
             */
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:peluang_investasis,slug,' . $peluangInvestasi->id,
            ],

            'luas_lahan' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
            ],

            'satuan_luas' => [
                'nullable',
                'string',
                'max:20',
            ],

            'status' => [
                'required',
                'string',
                'in:tersedia,proses,terisi,ditutup',
            ],

            'nilai_investasi' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999999999999.99',
            ],

            'mata_uang' => [
                'nullable',
                'string',
                'max:10',
            ],

            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024',
            ],

            'hapus_gambar' => [
                'nullable',
                'boolean',
            ],

            'urutan' => [
                'nullable',
                'integer',
                'min:0',
                'max:4294967295',
            ],

            'aktif' => [
                'nullable',
                'boolean',
            ],
        ]);

        $newGambarPath = null;
        $oldGambarPath = $peluangInvestasi->gambar;

        try {
            $data = [
                /*
                 * Bahasa Indonesia
                 */
                'judul' => $validated['judul'],
                'sektor_industri' => $validated['sektor_industri'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'lokasi' => $validated['lokasi'] ?? null,

                /*
                 * English
                 */
                'judul_en' => $validated['judul_en'] ?? null,
                'sektor_industri_en' => $validated['sektor_industri_en'] ?? null,
                'deskripsi_en' => $validated['deskripsi_en'] ?? null,
                'lokasi_en' => $validated['lokasi_en'] ?? null,

                /*
                 * Chinese
                 */
                'judul_zh' => $validated['judul_zh'] ?? null,
                'sektor_industri_zh' => $validated['sektor_industri_zh'] ?? null,
                'deskripsi_zh' => $validated['deskripsi_zh'] ?? null,
                'lokasi_zh' => $validated['lokasi_zh'] ?? null,

                /*
                 * General
                 */
                'slug' => $this->generateUniqueSlug(
                    $validated['slug'] ?? $validated['judul'],
                    $peluangInvestasi->id
                ),

                'luas_lahan' => $validated['luas_lahan'] ?? null,

                'satuan_luas' => $validated['satuan_luas'] ?? 'Ha',

                'status' => $validated['status'],

                'nilai_investasi' => $validated['nilai_investasi'] ?? null,

                'mata_uang' => strtoupper(
                    $validated['mata_uang'] ?? 'IDR'
                ),

                'urutan' => $validated['urutan'] ?? 0,
            ];

            /*
             * Upload gambar baru
             */
            if ($request->hasFile('gambar')) {
                $newGambarPath = $request->file('gambar')
                    ->store('peluang-investasi', 'public');

                $data['gambar'] = $newGambarPath;
            }

            /*
             * Hapus gambar lama jika diminta
             * dan tidak ada gambar baru.
             */
            if (
                ! $request->hasFile('gambar') &&
                $request->boolean('hapus_gambar') &&
                $oldGambarPath !== null
            ) {
                $data['gambar'] = null;
            }

            /*
             * Aktif hanya diperbarui jika field dikirim.
             */
            if ($request->has('aktif')) {
                $data['aktif'] = $validated['aktif'];
            }

            $peluangInvestasi->update($data);

            /*
             * Hapus file lama setelah database berhasil diperbarui.
             */
            if (
                $oldGambarPath !== null &&
                (
                    $newGambarPath !== null ||
                    (
                        ! $request->hasFile('gambar') &&
                        $request->boolean('hapus_gambar')
                    )
                )
            ) {
                Storage::disk('public')->delete($oldGambarPath);
            }

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Peluang investasi berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            /*
             * Jika upload gambar baru sudah berhasil tetapi
             * proses database gagal, hapus gambar baru.
             */
            if ($newGambarPath !== null) {
                Storage::disk('public')->delete($newGambarPath);
            }

            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Peluang investasi gagal diperbarui.',
            ]);
        }
    }

    /**
     * Remove the specified investment opportunity.
     */
    public function destroy(
        PeluangInvestasi $peluangInvestasi
    ): RedirectResponse {
        try {
            $gambarPath = $peluangInvestasi->gambar;

            $peluangInvestasi->delete();

            if ($gambarPath !== null) {
                Storage::disk('public')->delete($gambarPath);
            }

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Peluang investasi berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Peluang investasi gagal dihapus.',
            ]);
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleAktif(
        PeluangInvestasi $peluangInvestasi
    ): RedirectResponse {
        try {
            $peluangInvestasi->update([
                'aktif' => ! $peluangInvestasi->aktif,
            ]);

            return back()->with('toast', [
                'type' => 'success',
                'message' => $peluangInvestasi->aktif
                    ? 'Peluang investasi berhasil diaktifkan.'
                    : 'Peluang investasi berhasil dinonaktifkan.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Status peluang investasi gagal diperbarui.',
            ]);
        }
    }

    /**
     * Move the investment opportunity up or down.
     */
    public function move(
        Request $request,
        PeluangInvestasi $peluangInvestasi
    ): RedirectResponse {
        $validated = $request->validate([
            'direction' => [
                'required',
                'string',
                'in:up,down',
            ],
        ]);

        try {
            $moved = false;

            DB::transaction(function () use (
                $peluangInvestasi,
                $validated,
                &$moved
            ) {
                $currentOrder = $peluangInvestasi->urutan;

                if ($validated['direction'] === 'up') {
                    $neighbor = PeluangInvestasi::query()
                        ->where('urutan', '<', $currentOrder)
                        ->orderByDesc('urutan')
                        ->orderByDesc('id')
                        ->first();
                } else {
                    $neighbor = PeluangInvestasi::query()
                        ->where('urutan', '>', $currentOrder)
                        ->orderBy('urutan')
                        ->orderBy('id')
                        ->first();
                }

                if (! $neighbor) {
                    return;
                }

                $neighborOrder = $neighbor->urutan;

                $peluangInvestasi->update([
                    'urutan' => $neighborOrder,
                ]);

                $neighbor->update([
                    'urutan' => $currentOrder,
                ]);

                $moved = true;
            });

            if (! $moved) {
                return back()->with('toast', [
                    'type' => 'error',
                    'message' => $validated['direction'] === 'up'
                        ? 'Peluang investasi sudah berada di urutan paling atas.'
                        : 'Peluang investasi sudah berada di urutan paling bawah.',
                ]);
            }

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Urutan peluang investasi berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Urutan peluang investasi gagal diperbarui.',
            ]);
        }
    }

    /**
     * Generate a unique slug.
     */
    private function generateUniqueSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'peluang-investasi';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            PeluangInvestasi::query()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn($query) => $query->whereKeyNot($ignoreId)
            )
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
