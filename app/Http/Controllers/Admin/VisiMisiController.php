<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Misi;
use App\Models\Visi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VisiMisiController extends Controller
{
    /**
     * Menampilkan halaman Visi dan Misi.
     */
    public function index(): Response
    {
        $visi = Visi::query()
            ->with([
                'misis' => function ($query) {
                    $query
                        ->orderBy('urutan')
                        ->orderBy('id');
                },
            ])
            ->first();

        return Inertia::render(
            'admin/ProfilPerusahaan/VisiMisi',
            [
                'visi' => $visi,
            ]
        );
    }

    /**
     * Menyimpan Visi baru.
     *
     * Sistem hanya menggunakan satu data Visi aktif.
     */
    public function storeVisi(Request $request): RedirectResponse
    {
        $validated = $this->validateVisi($request);

        DB::transaction(function () use ($validated) {
            if (Visi::query()->exists()) {
                throw ValidationException::withMessages([
                    'isi' => 'Data visi sudah tersedia. Silakan gunakan fitur edit untuk memperbaruinya.',
                ]);
            }

            Visi::create([
                'isi' => $validated['isi'],
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Visi berhasil ditambahkan.',
        ]);
    }

    /**
     * Memperbarui Visi.
     */
    public function updateVisi(
        Request $request,
        Visi $visi
    ): RedirectResponse {
        $validated = $this->validateVisi($request);

        DB::transaction(function () use (
            $visi,
            $validated
        ) {
            $visi->update([
                'isi' => $validated['isi'],
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Visi berhasil diperbarui.',
        ]);
    }

    /**
     * Menyimpan Misi baru.
     */
    public function storeMisi(
        Request $request,
        Visi $visi
    ): RedirectResponse {
        $validated = $this->validateMisi($request);

        DB::transaction(function () use (
            $visi,
            $validated
        ) {
            $nextUrutan = (
                (int) $visi->misis()->max('urutan')
            ) + 1;

            $visi->misis()->create([
                'isi' => $validated['isi'],
                'urutan' => $nextUrutan,
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Misi berhasil ditambahkan.',
        ]);
    }

    /**
     * Memperbarui Misi.
     */
    public function updateMisi(
        Request $request,
        Visi $visi,
        Misi $misi
    ): RedirectResponse {
        $this->ensureMisiBelongsToVisi(
            $visi,
            $misi
        );

        $validated = $this->validateMisi($request);

        DB::transaction(function () use (
            $misi,
            $validated
        ) {
            $misi->update([
                'isi' => $validated['isi'],
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Misi berhasil diperbarui.',
        ]);
    }

    /**
     * Menghapus Misi.
     */
    public function destroyMisi(
        Visi $visi,
        Misi $misi
    ): RedirectResponse {
        $this->ensureMisiBelongsToVisi(
            $visi,
            $misi
        );

        DB::transaction(function () use (
            $visi,
            $misi
        ) {
            $misi->delete();

            $this->normalizeMisiOrder($visi);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Misi berhasil dihapus.',
        ]);
    }

    /**
     * Memindahkan posisi Misi ke atas atau ke bawah.
     */
    public function moveMisi(
        Request $request,
        Visi $visi,
        Misi $misi
    ): RedirectResponse {
        $this->ensureMisiBelongsToVisi(
            $visi,
            $misi
        );

        $validated = $request->validate([
            'direction' => [
                'required',
                'in:up,down',
            ],
        ]);

        DB::transaction(function () use (
            $visi,
            $misi,
            $validated
        ) {
            $misis = $visi->misis()
                ->orderBy('urutan')
                ->orderBy('id')
                ->get()
                ->values();

            $currentIndex = $misis->search(
                fn (Misi $item): bool =>
                    $item->id === $misi->id
            );

            if ($currentIndex === false) {
                return;
            }

            $targetIndex = $validated['direction'] === 'up'
                ? $currentIndex - 1
                : $currentIndex + 1;

            /*
             * Jika sudah berada di posisi paling atas
             * atau paling bawah, tidak perlu melakukan apa pun.
             */
            if (
                $targetIndex < 0 ||
                $targetIndex >= $misis->count()
            ) {
                return;
            }

            $currentMisi = $misis[$currentIndex];
            $targetMisi = $misis[$targetIndex];

            $currentUrutan = $currentMisi->urutan;
            $targetUrutan = $targetMisi->urutan;

            $currentMisi->update([
                'urutan' => $targetUrutan,
            ]);

            $targetMisi->update([
                'urutan' => $currentUrutan,
            ]);

            /*
             * Pastikan urutan kembali konsisten 1, 2, 3, ...
             */
            $this->normalizeMisiOrder($visi);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Urutan misi berhasil diperbarui.',
        ]);
    }

    /**
     * Memastikan Misi benar-benar milik Visi
     * yang sedang diproses.
     */
    private function ensureMisiBelongsToVisi(
        Visi $visi,
        Misi $misi
    ): void {
        if ($misi->visi_id !== $visi->id) {
            abort(404);
        }
    }

    /**
     * Menormalisasi urutan Misi.
     *
     * Hasil akhirnya selalu:
     * 1, 2, 3, 4, ...
     */
    private function normalizeMisiOrder(Visi $visi): void
    {
        $misis = $visi->misis()
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        foreach ($misis as $index => $misi) {
            $urutan = $index + 1;

            if ($misi->urutan !== $urutan) {
                $misi->updateQuietly([
                    'urutan' => $urutan,
                ]);
            }
        }
    }

    /**
     * Validasi data Visi.
     */
    private function validateVisi(
        Request $request
    ): array {
        return $request->validate([
            'isi' => [
                'required',
                'string',
                'max:10000',
            ],
        ]);
    }

    /**
     * Validasi data Misi.
     */
    private function validateMisi(
        Request $request
    ): array {
        return $request->validate([
            'isi' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);
    }
}
