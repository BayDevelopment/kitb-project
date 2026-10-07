<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Kawasan;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class FasilitasController extends Controller
{
    private const DISK = 'public';

    private const DIRECTORY = 'fasilitas';

    /**
     * Menampilkan daftar fasilitas.
     */
    public function index(Request $request): Response
    {
        $search = $request
            ->string('search')
            ->trim()
            ->toString();

        // Escape wildcard LIKE agar "%" dan "_" dicari sebagai teks biasa.
        $keyword = addcslashes($search, '%_\\');

        $fasilitas = Fasilitas::query()
            ->when($search !== '', function ($query) use ($keyword): void {
                $query->where(function ($query) use ($keyword): void {
                    $query
                        ->where('nama', 'like', "%{$keyword}%")
                        ->orWhere('nama_en', 'like', "%{$keyword}%")
                        ->orWhere('nama_zh', 'like', "%{$keyword}%")
                        ->orWhere('deskripsi', 'like', "%{$keyword}%")
                        ->orWhere('deskripsi_en', 'like', "%{$keyword}%")
                        ->orWhere('deskripsi_zh', 'like', "%{$keyword}%");
                });
            })
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/Kawasan/Fasilitas', [
            'fasilitas' => $fasilitas,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Menyimpan fasilitas baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $newImage = null;

        try {
            DB::transaction(function () use ($request, $validated, &$newImage): void {
                $nama = trim($validated['nama']);

                if ($request->hasFile('gambar')) {
                    $newImage = $request
                        ->file('gambar')
                        ->store(self::DIRECTORY, self::DISK);
                }

                $max = $this->maxUrutan();
                $urutan = (int) ($validated['urutan'] ?? 0);

                if ($urutan <= 0 || $urutan > $max + 1) {
                    // Kosong / di luar jangkauan: letakkan di paling bawah.
                    $urutan = $max + 1;
                } else {
                    // Geser data pada posisi tersebut ke bawah.
                    Fasilitas::query()
                        ->where('urutan', '>=', $urutan)
                        ->increment('urutan');
                }

                Fasilitas::create([
                    'nama' => $nama,
                    'slug' => Fasilitas::generateUniqueSlug($nama),
                    'deskripsi' => $this->cleanText($validated['deskripsi'] ?? null),
                    ...$this->translatedFields($validated),
                    'gambar' => $newImage,
                    'urutan' => $urutan,
                    'aktif' => (bool) $validated['aktif'],
                ]);
            });
        } catch (Throwable $e) {
            // Transaksi gagal: buang file yang sudah terlanjur diupload.
            $this->deleteFiles([$newImage]);

            report($e);

            return back()
                ->withInput()
                ->with('toast', $this->toast('error', 'Fasilitas gagal ditambahkan.'));
        }

        return to_route('kawasan.fasilitas')
            ->with('toast', $this->toast('success', 'Fasilitas berhasil ditambahkan.'));
    }

    /**
     * Memperbarui fasilitas.
     */
    public function update(
        Request $request,
        Fasilitas $fasilitas,
    ): RedirectResponse {
        $validated = $request->validate(
            $this->rules() + [
                'remove_gambar' => ['nullable', 'boolean'],
            ],
        );

        $newImage = null;
        $filesToDelete = [];

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                $fasilitas,
                &$newImage,
                &$filesToDelete,
            ): void {
                // Ambil ulang dengan lock agar aman dari edit bersamaan.
                $current = Fasilitas::query()
                    ->lockForUpdate()
                    ->findOrFail($fasilitas->id);

                $nama = trim($validated['nama']);

                /*
                 * Urutan
                 */
                $oldUrutan = (int) $current->urutan;
                $max = $this->maxUrutan();
                $newUrutan = (int) ($validated['urutan'] ?? 0);

                if ($newUrutan <= 0) {
                    // Kosong / 0: pertahankan posisi lama.
                    $newUrutan = $oldUrutan > 0 ? $oldUrutan : $max + 1;
                }

                $newUrutan = min($newUrutan, max($max, 1));

                if ($newUrutan !== $oldUrutan && $oldUrutan > 0) {
                    if ($newUrutan < $oldUrutan) {
                        Fasilitas::query()
                            ->where('id', '!=', $current->id)
                            ->whereBetween('urutan', [$newUrutan, $oldUrutan - 1])
                            ->increment('urutan');
                    } else {
                        Fasilitas::query()
                            ->where('id', '!=', $current->id)
                            ->whereBetween('urutan', [$oldUrutan + 1, $newUrutan])
                            ->decrement('urutan');
                    }
                }

                $data = [
                    'nama' => $nama,
                    // Slug hanya dibuat ulang jika nama (Indonesia) berubah.
                    'slug' => $current->nama !== $nama
                        ? Fasilitas::generateUniqueSlug($nama, $current->id)
                        : $current->slug,
                    'deskripsi' => $this->cleanText($validated['deskripsi'] ?? null),
                    ...$this->translatedFields($validated),
                    'urutan' => $newUrutan,
                    'aktif' => (bool) $validated['aktif'],
                ];

                /*
                 * Gambar
                 */
                if ($request->hasFile('gambar')) {
                    $newImage = $request
                        ->file('gambar')
                        ->store(self::DIRECTORY, self::DISK);

                    if ($current->gambar) {
                        $filesToDelete[] = $current->gambar;
                    }

                    $data['gambar'] = $newImage;
                } elseif ((bool) ($validated['remove_gambar'] ?? false)) {
                    if ($current->gambar) {
                        $filesToDelete[] = $current->gambar;
                    }

                    $data['gambar'] = null;
                }

                $current->update($data);
            });
        } catch (Throwable $e) {
            $this->deleteFiles([$newImage]);

            report($e);

            return back()
                ->withInput()
                ->with('toast', $this->toast('error', 'Fasilitas gagal diperbarui.'));
        }

        // File lama baru dihapus setelah database benar-benar tersimpan.
        $this->deleteFiles($filesToDelete);

        return to_route('kawasan.fasilitas')
            ->with('toast', $this->toast('success', 'Fasilitas berhasil diperbarui.'));
    }

    /**
     * Menghapus fasilitas.
     */
    public function destroy(Fasilitas $fasilitas): RedirectResponse
    {
        $filesToDelete = [];

        try {
            DB::transaction(function () use ($fasilitas, &$filesToDelete): void {
                $current = Fasilitas::query()
                    ->lockForUpdate()
                    ->findOrFail($fasilitas->id);

                $deletedUrutan = (int) $current->urutan;

                if ($current->gambar) {
                    $filesToDelete[] = $current->gambar;
                }

                $current->delete();

                // Rapikan urutan setelah data dihapus.
                if ($deletedUrutan > 0) {
                    Fasilitas::query()
                        ->where('urutan', '>', $deletedUrutan)
                        ->decrement('urutan');
                }
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->with('toast', $this->toast('error', 'Fasilitas gagal dihapus.'));
        }

        $this->deleteFiles($filesToDelete);

        return to_route('kawasan.fasilitas')
            ->with('toast', $this->toast('success', 'Fasilitas berhasil dihapus.'));
    }

    /**
     * Toggle status aktif/nonaktif.
     */
    public function toggleAktif(Fasilitas $fasilitas): RedirectResponse
    {
        try {
            $fasilitas->update([
                'aktif' => ! $fasilitas->aktif,
            ]);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->with('toast', $this->toast('error', 'Status fasilitas gagal diperbarui.'));
        }

        return back()->with('toast', $this->toast(
            'success',
            $fasilitas->aktif
                ? 'Fasilitas berhasil diaktifkan.'
                : 'Fasilitas berhasil dinonaktifkan.',
        ));
    }

    /**
     * Memindahkan fasilitas satu tingkat ke atas.
     */
    public function moveUp(Fasilitas $fasilitas): RedirectResponse
    {
        return $this->move($fasilitas, 'up');
    }

    /**
     * Memindahkan fasilitas satu tingkat ke bawah.
     */
    public function moveDown(Fasilitas $fasilitas): RedirectResponse
    {
        return $this->move($fasilitas, 'down');
    }

    /**
     * Menukar urutan dengan tetangga terdekat.
     */
    private function move(Fasilitas $fasilitas, string $direction): RedirectResponse
    {
        $moved = false;

        try {
            DB::transaction(function () use ($fasilitas, $direction, &$moved): void {
                $current = Fasilitas::query()
                    ->lockForUpdate()
                    ->findOrFail($fasilitas->id);

                $neighbor = Fasilitas::query()
                    ->where('urutan', $direction === 'up' ? '<' : '>', $current->urutan)
                    ->orderBy('urutan', $direction === 'up' ? 'desc' : 'asc')
                    ->lockForUpdate()
                    ->first();

                if (! $neighbor) {
                    return;
                }

                $currentOrder = $current->urutan;
                $neighborOrder = $neighbor->urutan;

                $current->update(['urutan' => $neighborOrder]);
                $neighbor->update(['urutan' => $currentOrder]);

                $moved = true;
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->with('toast', $this->toast('error', 'Urutan fasilitas gagal diperbarui.'));
        }

        if (! $moved) {
            return back()->with('toast', $this->toast(
                'info',
                $direction === 'up'
                    ? 'Fasilitas sudah berada di posisi paling atas.'
                    : 'Fasilitas sudah berada di posisi paling bawah.',
            ));
        }

        return back()->with('toast', $this->toast(
            'success',
            'Urutan fasilitas berhasil diperbarui.',
        ));
    }

    /**
     * Aturan validasi bersama untuk store & update.
     * Hanya nama Indonesia yang wajib; EN & ZH opsional.
     *
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'nama_en' => ['nullable', 'string', 'max:150'],
            'nama_zh' => ['nullable', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:10000'],
            'deskripsi_en' => ['nullable', 'string', 'max:10000'],
            'deskripsi_zh' => ['nullable', 'string', 'max:10000'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            'aktif' => ['required', 'boolean'],
        ];
    }

    /**
     * Urutan terbesar saat ini (di dalam transaksi, dengan lock).
     */
    private function maxUrutan(): int
    {
        return (int) Fasilitas::query()->lockForUpdate()->max('urutan');
    }

    /**
     * Trim teks; string kosong menjadi null.
     */
    private function cleanText(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * Field terjemahan (EN & ZH) yang sudah dibersihkan.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, string|null>
     */
    private function translatedFields(array $validated): array
    {
        return [
            'nama_en' => $this->cleanText($validated['nama_en'] ?? null),
            'nama_zh' => $this->cleanText($validated['nama_zh'] ?? null),
            'deskripsi_en' => $this->cleanText($validated['deskripsi_en'] ?? null),
            'deskripsi_zh' => $this->cleanText($validated['deskripsi_zh'] ?? null),
        ];
    }

    /**
     * Hapus file dari storage tanpa menggagalkan request.
     *
     * @param  array<int, string|null>  $paths
     */
    private function deleteFiles(array $paths): void
    {
        $paths = array_values(array_filter($paths));

        if ($paths === []) {
            return;
        }

        try {
            Storage::disk(self::DISK)->delete($paths);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Format flash message (sama dengan InfrastrukturController).
     *
     * @return array{type: string, message: string}
     */
    private function toast(string $type, string $message): array
    {
        return [
            'type' => $type,
            'message' => $message,
        ];
    }
}
