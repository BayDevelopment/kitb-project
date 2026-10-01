<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lowongan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class LowonganController extends Controller
{
    /**
     * Menampilkan daftar lowongan.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');

        $allowedStatuses = [
            'draft',
            'published',
            'closed',
        ];

        $status = in_array($status, $allowedStatuses, true)
            ? $status
            : null;

        $lowongans = Lowongan::query()
            ->search($search)
            ->status($status)
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'admin/PusatInformasi/Lowongan',
            [
                'lowongans' => $lowongans,

                'filters' => [
                    'search' => $search,
                    'status' => $status ?? '',
                ],

                'statusOptions' => [
                    [
                        'value' => 'draft',
                        'label' => 'Draft',
                    ],
                    [
                        'value' => 'published',
                        'label' => 'Published',
                    ],
                    [
                        'value' => 'closed',
                        'label' => 'Closed',
                    ],
                ],
            ]
        );
    }

    /**
     * Menyimpan lowongan baru.
     *
     * Nomor urut akan dinormalisasi agar tidak terjadi
     * dua lowongan dengan nomor urut yang sama.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateLowongan($request);

        try {
            DB::transaction(function () use ($validated): void {
                /*
                 * Urutan yang diminta admin.
                 *
                 * Jika kosong, lowongan ditempatkan
                 * setelah posisi terakhir.
                 */
                $requestedOrder = (int) ($validated['urutan'] ?? 0);

                $maxOrder = (int) Lowongan::query()
                    ->max('urutan');

                /*
                 * Untuk data baru:
                 *
                 * - urutan <= 0  => ditempatkan paling akhir
                 * - urutan > max  => ditempatkan paling akhir
                 * - urutan valid  => sisipkan pada posisi tersebut
                 */
                if ($requestedOrder <= 0 || $requestedOrder > $maxOrder + 1) {
                    $requestedOrder = $maxOrder + 1;
                }

                /*
                 * Geser semua data mulai dari posisi tersebut
                 * satu tingkat ke bawah.
                 *
                 * Contoh:
                 *
                 * A = 1
                 * B = 2
                 * C = 3
                 *
                 * Insert pada 1:
                 *
                 * A = 2
                 * B = 3
                 * C = 4
                 */
                if ($requestedOrder <= $maxOrder) {
                    Lowongan::query()
                        ->where('urutan', '>=', $requestedOrder)
                        ->orderByDesc('urutan')
                        ->lockForUpdate()
                        ->get()
                        ->each(function (Lowongan $item): void {
                            $item->update([
                                'urutan' => $item->urutan + 1,
                            ]);
                        });
                }

                /*
                 * Slug dibuat sepenuhnya oleh server.
                 *
                 * Request dari frontend tidak pernah dipercaya
                 * untuk menentukan slug.
                 */
                $slug = $this->generateUniqueSlug(
                    $validated['judul']
                );

                /*
                 * Sanitasi rich text.
                 */
                $validated['deskripsi'] = $this->sanitizeHtml(
                    $validated['deskripsi'] ?? null
                );

                $validated['tanggung_jawab'] = $this->sanitizeHtml(
                    $validated['tanggung_jawab'] ?? null
                );

                $validated['kualifikasi'] = $this->sanitizeHtml(
                    $validated['kualifikasi'] ?? null
                );

                $validated['benefit'] = $this->sanitizeHtml(
                    $validated['benefit'] ?? null
                );

                $validated['unggulan'] = (bool) (
                    $validated['unggulan'] ?? false
                );

                /*
                 * Jangan gunakan slug melalui mass assignment.
                 *
                 * Slug ditentukan secara eksplisit oleh server.
                 */
                $lowongan = new Lowongan();

                $lowongan->fill($validated);

                $lowongan->slug = $slug;
                $lowongan->urutan = $requestedOrder;

                $lowongan->save();
            });

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Lowongan berhasil ditambahkan.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Lowongan gagal ditambahkan.',
                ]);
        }
    }

    /**
     * Memperbarui lowongan.
     */
    public function update(
        Request $request,
        Lowongan $lowongan
    ): RedirectResponse {
        $validated = $this->validateLowongan(
            $request,
            $lowongan
        );

        try {
            DB::transaction(function () use (
                $validated,
                $lowongan
            ): void {
                $oldOrder = (int) $lowongan->urutan;

                $requestedOrder = (int) (
                    $validated['urutan'] ?? $oldOrder
                );

                $maxOrder = (int) Lowongan::query()
                    ->whereKeyNot($lowongan->id)
                    ->max('urutan');

                /*
                 * Karena data yang sedang diedit akan menempati
                 * salah satu posisi, maksimum posisi adalah jumlah
                 * data selain dirinya + 1.
                 */
                $maxAllowedOrder = max(
                    1,
                    $maxOrder + 1
                );

                $requestedOrder = max(
                    1,
                    min(
                        $requestedOrder,
                        $maxAllowedOrder
                    )
                );

                /*
                 * Cek apakah judul berubah sebelum melakukan fill().
                 */
                $judulChanged =
                    $validated['judul'] !== $lowongan->judul;

                /*
                 * Jika posisi berubah, rapikan posisi data lain.
                 */
                if ($requestedOrder !== $oldOrder) {
                    /*
                     * Pindah ke posisi lebih atas.
                     *
                     * Contoh:
                     *
                     * A = 1
                     * B = 2
                     * C = 3
                     * D = 4
                     *
                     * D pindah ke 2:
                     *
                     * A = 1
                     * D = 2
                     * B = 3
                     * C = 4
                     */
                    if ($requestedOrder < $oldOrder) {
                        Lowongan::query()
                            ->whereKeyNot($lowongan->id)
                            ->whereBetween(
                                'urutan',
                                [
                                    $requestedOrder,
                                    $oldOrder - 1,
                                ]
                            )
                            ->orderByDesc('urutan')
                            ->lockForUpdate()
                            ->get()
                            ->each(function (Lowongan $item): void {
                                $item->update([
                                    'urutan' => $item->urutan + 1,
                                ]);
                            });
                    } else {
                        /*
                         * Pindah ke posisi lebih bawah.
                         *
                         * Contoh:
                         *
                         * A = 1
                         * B = 2
                         * C = 3
                         * D = 4
                         *
                         * A pindah ke 3:
                         *
                         * B = 1
                         * C = 2
                         * A = 3
                         * D = 4
                         */
                        Lowongan::query()
                            ->whereKeyNot($lowongan->id)
                            ->whereBetween(
                                'urutan',
                                [
                                    $oldOrder + 1,
                                    $requestedOrder,
                                ]
                            )
                            ->orderBy('urutan')
                            ->lockForUpdate()
                            ->get()
                            ->each(function (Lowongan $item): void {
                                $item->update([
                                    'urutan' => $item->urutan - 1,
                                ]);
                            });
                    }
                }

                /*
                 * Sanitasi rich text.
                 */
                $validated['deskripsi'] = $this->sanitizeHtml(
                    $validated['deskripsi'] ?? null
                );

                $validated['tanggung_jawab'] = $this->sanitizeHtml(
                    $validated['tanggung_jawab'] ?? null
                );

                $validated['kualifikasi'] = $this->sanitizeHtml(
                    $validated['kualifikasi'] ?? null
                );

                $validated['benefit'] = $this->sanitizeHtml(
                    $validated['benefit'] ?? null
                );

                $validated['unggulan'] = (bool) (
                    $validated['unggulan'] ?? false
                );

                /*
                 * Jangan pernah menerima slug dari frontend.
                 *
                 * Jika judul berubah, server membuat slug baru.
                 */
                unset($validated['slug']);

                $lowongan->fill($validated);

                if ($judulChanged) {
                    $lowongan->slug = $this->generateUniqueSlug(
                        $validated['judul'],
                        $lowongan->id
                    );
                }

                $lowongan->urutan = $requestedOrder;

                $lowongan->save();
            });

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Lowongan berhasil diperbarui.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Lowongan gagal diperbarui.',
                ]);
        }
    }

    /**
     * Menghapus lowongan.
     */
    public function destroy(
        Lowongan $lowongan
    ): RedirectResponse {
        try {
            DB::transaction(function () use ($lowongan): void {
                $deletedOrder = (int) $lowongan->urutan;

                $lowongan->delete();

                /*
                 * Rapikan nomor urut setelah penghapusan.
                 *
                 * Contoh:
                 *
                 * A = 1
                 * B = 2
                 * C = 3
                 *
                 * B dihapus:
                 *
                 * A = 1
                 * C = 2
                 */
                Lowongan::query()
                    ->where('urutan', '>', $deletedOrder)
                    ->orderBy('urutan')
                    ->lockForUpdate()
                    ->get()
                    ->each(function (Lowongan $item): void {
                        $item->update([
                            'urutan' => $item->urutan - 1,
                        ]);
                    });
            });

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Lowongan berhasil dihapus.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Lowongan gagal dihapus.',
            ]);
        }
    }

    /**
     * Mengubah status lowongan.
     */
    public function toggleStatus(
        Request $request,
        Lowongan $lowongan
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in([
                    'draft',
                    'published',
                    'closed',
                ]),
            ],
        ]);

        try {
            $lowongan->update([
                'status' => $validated['status'],
            ]);

            $message = match ($validated['status']) {
                'published' =>
                'Lowongan berhasil dipublikasikan.',

                'closed' =>
                'Lowongan berhasil ditutup.',

                'draft' =>
                'Lowongan berhasil dikembalikan ke draft.',

                default =>
                'Status lowongan berhasil diperbarui.',
            };

            return back()->with('toast', [
                'type' => 'success',
                'message' => $message,
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Status lowongan gagal diperbarui.',
            ]);
        }
    }

    /**
     * Mengubah status unggulan.
     */
    public function toggleFeatured(
        Lowongan $lowongan
    ): RedirectResponse {
        try {
            $lowongan->update([
                'unggulan' => ! $lowongan->unggulan,
            ]);

            return back()->with('toast', [
                'type' => 'success',
                'message' => $lowongan->unggulan
                    ? 'Lowongan berhasil dijadikan unggulan.'
                    : 'Lowongan tidak lagi menjadi unggulan.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Status unggulan gagal diperbarui.',
            ]);
        }
    }

    /**
     * Memindahkan urutan lowongan.
     */
    public function move(
        Request $request,
        Lowongan $lowongan
    ): RedirectResponse {
        $validated = $request->validate([
            'direction' => [
                'required',
                'string',
                Rule::in([
                    'up',
                    'down',
                ]),
            ],
        ]);

        try {
            DB::transaction(function () use (
                $validated,
                $lowongan
            ): void {
                if ($validated['direction'] === 'up') {
                    $neighbor = Lowongan::query()
                        ->where(
                            'urutan',
                            '<',
                            $lowongan->urutan
                        )
                        ->orderByDesc('urutan')
                        ->orderByDesc('id')
                        ->lockForUpdate()
                        ->first();
                } else {
                    $neighbor = Lowongan::query()
                        ->where(
                            'urutan',
                            '>',
                            $lowongan->urutan
                        )
                        ->orderBy('urutan')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->first();
                }

                /*
                 * Tidak ada tetangga berarti sudah berada
                 * di posisi paling atas / bawah.
                 */
                if (! $neighbor) {
                    return;
                }

                $currentOrder = (int) $lowongan->urutan;
                $neighborOrder = (int) $neighbor->urutan;

                $lowongan->update([
                    'urutan' => $neighborOrder,
                ]);

                $neighbor->update([
                    'urutan' => $currentOrder,
                ]);
            });

            return back()->with('toast', [
                'type' => 'success',
                'message' => 'Urutan lowongan berhasil diperbarui.',
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Urutan lowongan gagal diperbarui.',
            ]);
        }
    }

    /**
     * Validasi data lowongan.
     *
     * Slug sengaja TIDAK divalidasi dari request.
     */
    private function validateLowongan(
        Request $request,
        ?Lowongan $lowongan = null
    ): array {
        return $request->validate([
            'judul' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'departemen' => [
                'nullable',
                'string',
                'max:150',
            ],

            'lokasi' => [
                'nullable',
                'string',
                'max:150',
            ],

            'tipe_pekerjaan' => [
                'nullable',
                'string',
                'max:50',
            ],

            'deskripsi' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'tanggung_jawab' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'kualifikasi' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'benefit' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'tanggal_mulai' => [
                'nullable',
                'date',
            ],

            'tanggal_tutup' => [
                'nullable',
                'date',
                'after_or_equal:tanggal_mulai',
            ],

            'status' => [
                'required',
                'string',
                Rule::in([
                    'draft',
                    'published',
                    'closed',
                ]),
            ],

            'unggulan' => [
                'sometimes',
                'boolean',
            ],

            'urutan' => [
                'sometimes',
                'integer',
                'min:1',
                'max:4294967295',
            ],
        ], [
            'judul.required' =>
            'Judul lowongan wajib diisi.',

            'judul.min' =>
            'Judul lowongan minimal 3 karakter.',

            'judul.max' =>
            'Judul lowongan maksimal 255 karakter.',

            'tanggal_tutup.after_or_equal' =>
            'Tanggal tutup harus sama atau setelah tanggal mulai.',

            'status.in' =>
            'Status lowongan tidak valid.',

            'unggulan.boolean' =>
            'Nilai unggulan tidak valid.',

            'urutan.integer' =>
            'Nomor urut harus berupa angka.',

            'urutan.min' =>
            'Nomor urut minimal adalah 1.',

            'urutan.max' =>
            'Nomor urut terlalu besar.',
        ]);
    }

    /**
     * Membuat slug unik secara server-side.
     */
    private function generateUniqueSlug(
        string $judul,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($judul);

        if ($baseSlug === '') {
            $baseSlug = 'lowongan';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Lowongan::query()
            ->when(
                $ignoreId !== null,
                function (Builder $query) use ($ignoreId): void {
                    $query->where(
                        $query->getModel()->getKeyName(),
                        '!=',
                        $ignoreId
                    );
                }
            )
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Sanitasi HTML rich text.
     */
    private function sanitizeHtml(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        /*
         * Hapus script dan style block.
         */
        $html = preg_replace(
            [
                '/<script\b[^>]*>(.*?)<\/script>/is',
                '/<style\b[^>]*>(.*?)<\/style>/is',
            ],
            '',
            $html
        );

        /*
         * Hapus seluruh inline event handler.
         *
         * Contoh:
         * onclick=""
         * onerror=""
         * onload=""
         */
        $html = preg_replace(
            [
                '/\son[a-z]+\s*=\s*(["\']).*?\1/is',
                '/\son[a-z]+\s*=\s*[^\s>]+/is',
            ],
            '',
            (string) $html
        );

        /*
         * Hapus javascript:, vbscript:, dan data:
         */
        $html = preg_replace(
            [
                '/javascript\s*:/i',
                '/vbscript\s*:/i',
                '/data\s*:/i',
            ],
            '',
            (string) $html
        );

        /*
         * Whitelist HTML yang diperbolehkan.
         */
        $allowedTags = [
            '<p>',
            '<br>',
            '<strong>',
            '<b>',
            '<em>',
            '<i>',
            '<u>',
            '<ul>',
            '<ol>',
            '<li>',
            '<h2>',
            '<h3>',
            '<h4>',
            '<blockquote>',
        ];

        $html = strip_tags(
            (string) $html,
            implode('', $allowedTags)
        );

        /*
         * Normalisasi newline berlebihan.
         */
        $html = preg_replace(
            "/(\r\n|\r|\n){3,}/",
            "\n\n",
            $html
        );

        $html = trim((string) $html);

        return $html !== ''
            ? $html
            : null;
    }
}
