<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RuteController extends Controller
{
    /**
     * Menampilkan daftar rute.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $aktif = $request->input('aktif');

        $rutes = Rute::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        // Bahasa Indonesia
                        ->where('nama_rute', 'like', "%{$search}%")
                        ->orWhere('jalur', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")

                        // Bahasa Inggris
                        ->orWhere('nama_rute_en', 'like', "%{$search}%")
                        ->orWhere('jalur_en', 'like', "%{$search}%")
                        ->orWhere('deskripsi_en', 'like', "%{$search}%")

                        // Bahasa Mandarin
                        ->orWhere('nama_rute_zh', 'like', "%{$search}%")
                        ->orWhere('jalur_zh', 'like', "%{$search}%")
                        ->orWhere('deskripsi_zh', 'like', "%{$search}%")

                        // Data umum
                        ->orWhere('asal', 'like', "%{$search}%")
                        ->orWhere('tujuan', 'like', "%{$search}%");
                });
            })
            ->when(
                $aktif !== null && $aktif !== '',
                function ($query) use ($aktif) {
                    $query->where(
                        'aktif',
                        filter_var(
                            $aktif,
                            FILTER_VALIDATE_BOOLEAN,
                            FILTER_NULL_ON_FAILURE
                        ) ?? false
                    );
                }
            )
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/HubunganInvestor/Rute', [
            'rutes' => $rutes,

            'filters' => [
                'search' => $search,
                'aktif' => $aktif,
            ],
        ]);
    }

    /**
     * Menyimpan rute baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        $validated['geometry'] = $this->normalizeGeometry(
            $validated['geometry'] ?? null
        );

        // Jika urutan kosong, gunakan urutan berikutnya.
        if (
            !isset($validated['urutan']) ||
            $validated['urutan'] === null ||
            $validated['urutan'] === ''
        ) {
            $validated['urutan'] = $this->nextOrder();
        }

        // Default aktif.
        $validated['aktif'] = $validated['aktif'] ?? true;

        $imagePath = null;

        try {
            if ($request->hasFile('gambar')) {
                $imagePath = $request
                    ->file('gambar')
                    ->store('rutes', 'public');

                $validated['gambar'] = $imagePath;
            }

            Rute::create($validated);

            return $this->toast('success', 'Rute berhasil ditambahkan.');
        } catch (\Throwable $e) {
            // Jika database gagal setelah upload, hapus gambar yang sudah tersimpan.
            if ($imagePath) {
                $this->deleteImage($imagePath);
            }

            report($e);

            return $this->toast(
                'error',
                'Rute gagal ditambahkan. Silakan coba lagi.'
            );
        }
    }

    /**
     * Memperbarui rute.
     */
    public function update(Request $request, Rute $rute): RedirectResponse
    {
        $validated = $this->validateData($request);

        $validated['geometry'] = $this->normalizeGeometry(
            $validated['geometry'] ?? null
        );

        // Pertahankan urutan lama jika tidak dikirim.
        if (
            !array_key_exists('urutan', $validated) ||
            $validated['urutan'] === null ||
            $validated['urutan'] === ''
        ) {
            $validated['urutan'] = $rute->urutan;
        }

        // Pertahankan status aktif lama jika tidak dikirim.
        if (!array_key_exists('aktif', $validated)) {
            $validated['aktif'] = $rute->aktif;
        }

        $oldImage = $rute->gambar;
        $newImage = null;

        try {
            if ($request->hasFile('gambar')) {
                $newImage = $request
                    ->file('gambar')
                    ->store('rutes', 'public');

                $validated['gambar'] = $newImage;
            }

            $rute->update($validated);

            // Hapus gambar lama setelah update berhasil.
            if ($newImage && $oldImage) {
                $this->deleteImage($oldImage);
            }

            return $this->toast('success', 'Rute berhasil diperbarui.');
        } catch (\Throwable $e) {
            // Jika update gagal, hapus file baru.
            if ($newImage) {
                $this->deleteImage($newImage);
            }

            report($e);

            return $this->toast(
                'error',
                'Rute gagal diperbarui. Silakan coba lagi.'
            );
        }
    }

    /**
     * Menghapus rute.
     */
    public function destroy(Rute $rute): RedirectResponse
    {
        $image = $rute->gambar;

        try {
            DB::transaction(function () use ($rute) {
                $deletedOrder = $rute->urutan;

                $rute->delete();

                // Rapikan urutan setelah data dihapus.
                Rute::query()
                    ->where('urutan', '>', $deletedOrder)
                    ->decrement('urutan');
            });

            // Hapus gambar setelah transaksi database berhasil.
            $this->deleteImage($image);

            return $this->toast('success', 'Rute berhasil dihapus.');
        } catch (\Throwable $e) {
            report($e);

            return $this->toast(
                'error',
                'Rute gagal dihapus. Silakan coba lagi.'
            );
        }
    }

    /**
     * Mengubah status aktif/nonaktif.
     */
    public function toggleAktif(Rute $rute): RedirectResponse
    {
        $rute->update([
            'aktif' => !$rute->aktif,
        ]);

        return $this->toast(
            'success',
            $rute->aktif
                ? 'Rute berhasil diaktifkan.'
                : 'Rute berhasil dinonaktifkan.'
        );
    }

    /**
     * Memindahkan urutan rute.
     */
    public function move(Request $request, Rute $rute): RedirectResponse
    {
        $validated = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ]);

        $direction = $validated['direction'];

        // Cari tetangga berdasarkan urutan.
        if ($direction === 'up') {
            $neighbor = Rute::query()
                ->where('urutan', '<', $rute->urutan)
                ->orderByDesc('urutan')
                ->orderByDesc('id')
                ->first();
        } else {
            $neighbor = Rute::query()
                ->where('urutan', '>', $rute->urutan)
                ->orderBy('urutan')
                ->orderBy('id')
                ->first();
        }

        if (!$neighbor) {
            return $this->toast(
                'error',
                $direction === 'up'
                    ? 'Rute sudah berada di urutan paling atas.'
                    : 'Rute sudah berada di urutan paling bawah.'
            );
        }

        DB::transaction(function () use ($rute, $neighbor) {
            $currentOrder = $rute->urutan;
            $neighborOrder = $neighbor->urutan;

            // Pakai angka sementara supaya tidak bentrok.
            $temporaryOrder = ((int) Rute::query()->max('urutan')) + 1;

            $rute->update(['urutan' => $temporaryOrder]);
            $neighbor->update(['urutan' => $currentOrder]);
            $rute->update(['urutan' => $neighborOrder]);
        });

        return $this->toast('success', 'Urutan rute berhasil diperbarui.');
    }

    /**
     * Redirect kembali dengan flash toast.
     *
     * @param  'success'|'error'|'info'|'warning'  $type
     */
    private function toast(string $type, string $message): RedirectResponse
    {
        return back()->with('toast', [
            'type' => $type,
            'message' => $message,
        ]);
    }

    /**
     * Validasi data rute.
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            // Bahasa Indonesia
            'nama_rute' => ['required', 'string', 'max:200'],
            'jalur' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:10000'],

            // Bahasa Inggris
            'nama_rute_en' => ['nullable', 'string', 'max:200'],
            'jalur_en' => ['nullable', 'string', 'max:255'],
            'deskripsi_en' => ['nullable', 'string', 'max:10000'],

            // Bahasa Mandarin
            'nama_rute_zh' => ['nullable', 'string', 'max:200'],
            'jalur_zh' => ['nullable', 'string', 'max:255'],
            'deskripsi_zh' => ['nullable', 'string', 'max:10000'],

            // Data teknis
            'jarak' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'satuan_jarak' => ['required', 'string', 'max:20'],
            'waktu_tempuh' => ['required', 'string', 'max:100'],

            // Asal & tujuan
            'asal' => ['nullable', 'string', 'max:200'],
            'tujuan' => ['nullable', 'string', 'max:200'],

            // Koordinat
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Geometry (divalidasi manual di normalizeGeometry)
            'geometry' => ['nullable'],

            // Gambar
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Pengaturan
            'urutan' => ['nullable', 'integer', 'min:0'],
            'aktif' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Normalize dan validasi GeoJSON.
     */
    private function normalizeGeometry(mixed $geometry): ?array
    {
        if ($geometry === null || $geometry === '') {
            return null;
        }

        // Jika dikirim sebagai JSON string, decode terlebih dahulu.
        if (is_string($geometry)) {
            $decoded = json_decode($geometry, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                throw ValidationException::withMessages([
                    'geometry' => 'Format geometry GeoJSON tidak valid.',
                ]);
            }

            $geometry = $decoded;
        }

        if (!is_array($geometry)) {
            throw ValidationException::withMessages([
                'geometry' => 'Format geometry GeoJSON tidak valid.',
            ]);
        }

        if (
            !isset($geometry['type']) ||
            !array_key_exists('coordinates', $geometry)
        ) {
            throw ValidationException::withMessages([
                'geometry' => 'Geometry harus memiliki type dan coordinates.',
            ]);
        }

        $allowedTypes = ['Point', 'LineString', 'MultiLineString'];

        if (!in_array($geometry['type'], $allowedTypes, true)) {
            throw ValidationException::withMessages([
                'geometry' =>
                'Tipe geometry hanya Point, LineString, atau MultiLineString.',
            ]);
        }

        $this->validateCoordinates($geometry['coordinates'], $geometry['type']);

        return $geometry;
    }

    /**
     * Validasi koordinat GeoJSON ([longitude, latitude]).
     */
    private function validateCoordinates(mixed $coordinates, string $type): void
    {
        if (!is_array($coordinates)) {
            throw ValidationException::withMessages([
                'geometry' => 'Coordinates GeoJSON tidak valid.',
            ]);
        }

        if ($type === 'Point') {
            $this->validateCoordinatePair($coordinates);

            return;
        }

        if ($type === 'LineString') {
            if (count($coordinates) < 2) {
                throw ValidationException::withMessages([
                    'geometry' => 'LineString minimal harus memiliki dua titik.',
                ]);
            }

            foreach ($coordinates as $coordinate) {
                $this->validateCoordinatePair($coordinate);
            }

            return;
        }

        // MultiLineString
        if (count($coordinates) === 0) {
            throw ValidationException::withMessages([
                'geometry' => 'MultiLineString harus memiliki minimal satu garis.',
            ]);
        }

        foreach ($coordinates as $line) {
            if (!is_array($line) || count($line) < 2) {
                throw ValidationException::withMessages([
                    'geometry' =>
                    'Setiap garis harus memiliki minimal dua titik.',
                ]);
            }

            foreach ($line as $coordinate) {
                $this->validateCoordinatePair($coordinate);
            }
        }
    }

    /**
     * Validasi satu pasangan koordinat ([longitude, latitude]).
     */
    private function validateCoordinatePair(mixed $coordinate): void
    {
        if (!is_array($coordinate) || count($coordinate) < 2) {
            throw ValidationException::withMessages([
                'geometry' => 'Pasangan koordinat GeoJSON tidak valid.',
            ]);
        }

        [$longitude, $latitude] = $coordinate;

        if (!is_numeric($longitude) || !is_numeric($latitude)) {
            throw ValidationException::withMessages([
                'geometry' => 'Longitude dan latitude harus berupa angka.',
            ]);
        }

        $longitude = (float) $longitude;
        $latitude = (float) $latitude;

        if ($longitude < -180 || $longitude > 180) {
            throw ValidationException::withMessages([
                'geometry' => 'Longitude harus berada antara -180 sampai 180.',
            ]);
        }

        if ($latitude < -90 || $latitude > 90) {
            throw ValidationException::withMessages([
                'geometry' => 'Latitude harus berada antara -90 sampai 90.',
            ]);
        }
    }

    /**
     * Mengambil nomor urutan berikutnya.
     */
    private function nextOrder(): int
    {
        return ((int) Rute::query()->max('urutan')) + 1;
    }

    /**
     * Menghapus gambar dari public disk.
     */
    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
