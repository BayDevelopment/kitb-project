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

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateLowongan($request);

        try {
            DB::transaction(function () use ($validated): void {
                $requestedOrder = (int) ($validated['urutan'] ?? 0);

                $maxOrder = (int) Lowongan::query()->max('urutan');

                if ($requestedOrder <= 0 || $requestedOrder > $maxOrder + 1) {
                    $requestedOrder = $maxOrder + 1;
                }

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

                $validated = $this->sanitizeTranslatedContent($validated);

                $validated['unggulan'] = (bool) (
                    $validated['unggulan'] ?? false
                );

                unset($validated['slug']);

                $lowongan = new Lowongan();
                $lowongan->fill($validated);

                // Slug selalu dibuat server-side dari judul Indonesia.
                $lowongan->slug = $this->generateUniqueSlug(
                    $validated['judul_id']
                );

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
                    ->where('id', '!=', $lowongan->id)
                    ->max('urutan');

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

                // Judul Indonesia menjadi sumber slug.
                $judulChanged =
                    $validated['judul_id'] !== $lowongan->judul_id;

                if ($requestedOrder !== $oldOrder) {
                    if ($requestedOrder < $oldOrder) {
                        Lowongan::query()
                            ->where('id', '!=', $lowongan->id)
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
                        Lowongan::query()
                            ->where('id', '!=', $lowongan->id)
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

                $validated = $this->sanitizeTranslatedContent($validated);

                $validated['unggulan'] = (bool) (
                    $validated['unggulan'] ?? false
                );

                // Slug tidak pernah diterima dari frontend.
                unset($validated['slug']);

                $lowongan->fill($validated);

                if ($judulChanged) {
                    $lowongan->slug = $this->generateUniqueSlug(
                        $validated['judul_id'],
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

    public function destroy(
        Lowongan $lowongan
    ): RedirectResponse {
        try {
            DB::transaction(function () use ($lowongan): void {
                $deletedOrder = (int) $lowongan->urutan;

                $lowongan->delete();

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
                'published' => 'Lowongan berhasil dipublikasikan.',
                'closed' => 'Lowongan berhasil ditutup.',
                'draft' => 'Lowongan berhasil dikembalikan ke draft.',
                default => 'Status lowongan berhasil diperbarui.',
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

    private function validateLowongan(
        Request $request,
        ?Lowongan $lowongan = null
    ): array {
        return $request->validate([
            'judul_id' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'judul_en' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'judul_zh' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],

            'departemen_id' => [
                'nullable',
                'string',
                'max:150',
            ],

            'departemen_en' => [
                'nullable',
                'string',
                'max:150',
            ],

            'departemen_zh' => [
                'nullable',
                'string',
                'max:150',
            ],

            'lokasi_id' => [
                'nullable',
                'string',
                'max:150',
            ],

            'lokasi_en' => [
                'nullable',
                'string',
                'max:150',
            ],

            'lokasi_zh' => [
                'nullable',
                'string',
                'max:150',
            ],

            'tipe_pekerjaan' => [
                'nullable',
                'string',
                'max:50',
            ],

            'deskripsi_id' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'deskripsi_en' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'deskripsi_zh' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'tanggung_jawab_id' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'tanggung_jawab_en' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'tanggung_jawab_zh' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'kualifikasi_id' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'kualifikasi_en' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'kualifikasi_zh' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'benefit_id' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'benefit_en' => [
                'nullable',
                'string',
                'max:50000',
            ],

            'benefit_zh' => [
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
            'judul_id.required' => 'Judul Indonesia wajib diisi.',
            'judul_id.min' => 'Judul Indonesia minimal 3 karakter.',
            'judul_id.max' => 'Judul Indonesia maksimal 255 karakter.',

            'judul_en.required' => 'Judul English wajib diisi.',
            'judul_en.min' => 'Judul English minimal 3 karakter.',
            'judul_en.max' => 'Judul English maksimal 255 karakter.',

            'judul_zh.required' => 'Judul 中文 wajib diisi.',
            'judul_zh.min' => 'Judul 中文 minimal 1 karakter.',
            'judul_zh.max' => 'Judul 中文 maksimal 255 karakter.',

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

    private function sanitizeTranslatedContent(array $validated): array
    {
        $fields = [
            'deskripsi_id',
            'deskripsi_en',
            'deskripsi_zh',

            'tanggung_jawab_id',
            'tanggung_jawab_en',
            'tanggung_jawab_zh',

            'kualifikasi_id',
            'kualifikasi_en',
            'kualifikasi_zh',

            'benefit_id',
            'benefit_en',
            'benefit_zh',
        ];

        foreach ($fields as $field) {
            $validated[$field] = $this->sanitizeHtml(
                $validated[$field] ?? null
            );
        }

        return $validated;
    }

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

    private function sanitizeHtml(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $html = preg_replace(
            [
                '/<script\b[^>]*>.*?<\/script>/is',
                '/<style\b[^>]*>.*?<\/style>/is',
            ],
            '',
            $html
        );

        $html = preg_replace(
            [
                '/\son[a-z]+\s*=\s*(["\']).*?\1/is',
                '/\son[a-z]+\s*=\s*[^\s>]+/is',
            ],
            '',
            (string) $html
        );

        $html = preg_replace(
            [
                '/javascript\s*:/i',
                '/vbscript\s*:/i',
                '/data\s*:/i',
            ],
            '',
            (string) $html
        );

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

        $html = preg_replace(
            "/(\r\n|\r|\n){3,}/",
            "\n\n",
            $html
        );

        $html = trim((string) $html);

        return $html !== '' ? $html : null;
    }
}
