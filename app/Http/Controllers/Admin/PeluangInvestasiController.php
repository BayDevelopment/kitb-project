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
                        ->where('judul', 'like', "%{$search}%")
                        ->orWhere('sektor_industri', 'like', "%{$search}%")
                        ->orWhere('lokasi', 'like', "%{$search}%");
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
            'judul' => [
                'required',
                'string',
                'max:255',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:peluang_investasis,slug',
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
            'lokasi' => [
                'nullable',
                'string',
                'max:255',
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
                'judul' => $validated['judul'],
                'slug' => $this->generateUniqueSlug(
                    $validated['slug'] ?? $validated['judul']
                ),
                'sektor_industri' => $validated['sektor_industri'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'luas_lahan' => $validated['luas_lahan'] ?? null,
                'satuan_luas' => $validated['satuan_luas'] ?? 'Ha',
                'lokasi' => $validated['lokasi'] ?? null,
                'status' => $validated['status'],
                'nilai_investasi' => $validated['nilai_investasi'] ?? null,
                'mata_uang' => strtoupper(
                    $validated['mata_uang'] ?? 'IDR'
                ),
                'urutan' => $validated['urutan'] ?? 0,
                'aktif' => $validated['aktif'] ?? true,
            ];

            if ($request->hasFile('gambar')) {
                $gambarPath = $request->file('gambar')
                    ->store('peluang-investasi', 'public');

                $data['gambar'] = $gambarPath;
            }

            PeluangInvestasi::create($data);

            return back()->with(
                'success',
                'Peluang investasi berhasil ditambahkan.'
            );
        } catch (\Throwable $e) {
            if ($gambarPath !== null) {
                Storage::disk('public')->delete($gambarPath);
            }

            report($e);

            return back()->with(
                'error',
                'Peluang investasi gagal ditambahkan.'
            );
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
            'judul' => [
                'required',
                'string',
                'max:255',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:peluang_investasis,slug,' . $peluangInvestasi->id,
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
            'lokasi' => [
                'nullable',
                'string',
                'max:255',
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

        $newGambarPath = null;
        $oldGambarPath = $peluangInvestasi->gambar;

        try {
            $data = [
                'judul' => $validated['judul'],
                'slug' => $this->generateUniqueSlug(
                    $validated['slug'] ?? $validated['judul'],
                    $peluangInvestasi->id
                ),
                'sektor_industri' => $validated['sektor_industri'] ?? null,
                'deskripsi' => $validated['deskripsi'] ?? null,
                'luas_lahan' => $validated['luas_lahan'] ?? null,
                'satuan_luas' => $validated['satuan_luas'] ?? 'Ha',
                'lokasi' => $validated['lokasi'] ?? null,
                'status' => $validated['status'],
                'nilai_investasi' => $validated['nilai_investasi'] ?? null,
                'mata_uang' => strtoupper(
                    $validated['mata_uang'] ?? 'IDR'
                ),
                'urutan' => $validated['urutan'] ?? 0,
            ];

            if ($request->hasFile('gambar')) {
                $newGambarPath = $request->file('gambar')
                    ->store('peluang-investasi', 'public');

                $data['gambar'] = $newGambarPath;
            }

            if ($request->has('aktif')) {
                $data['aktif'] = $validated['aktif'];
            }

            $peluangInvestasi->update($data);

            if (
                $newGambarPath !== null &&
                $oldGambarPath !== null &&
                $oldGambarPath !== $newGambarPath
            ) {
                Storage::disk('public')->delete($oldGambarPath);
            }

            return back()->with(
                'success',
                'Peluang investasi berhasil diperbarui.'
            );
        } catch (\Throwable $e) {
            if ($newGambarPath !== null) {
                Storage::disk('public')->delete($newGambarPath);
            }

            report($e);

            return back()->with(
                'error',
                'Peluang investasi gagal diperbarui.'
            );
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

            return back()->with(
                'success',
                'Peluang investasi berhasil dihapus.'
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Peluang investasi gagal dihapus.'
            );
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleAktif(
        PeluangInvestasi $peluangInvestasi
    ): RedirectResponse {
        $peluangInvestasi->update([
            'aktif' => ! $peluangInvestasi->aktif,
        ]);

        return back()->with(
            'success',
            $peluangInvestasi->aktif
                ? 'Peluang investasi berhasil diaktifkan.'
                : 'Peluang investasi berhasil dinonaktifkan.'
        );
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
            DB::transaction(function () use (
                $peluangInvestasi,
                $validated
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
            });

            return back()->with(
                'success',
                'Urutan peluang investasi berhasil diperbarui.'
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Urutan peluang investasi gagal diperbarui.'
            );
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
