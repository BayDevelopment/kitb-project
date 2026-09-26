<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnakUsaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AnakUsahaController extends Controller
{
    /**
     * Display a listing of anak usaha.
     */
    public function index(Request $request)
    {
        $anakUsaha = AnakUsaha::query()
            ->orderBy('urutan')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'admin/ProfilPerusahaan/AnakUsaha',
            [
                'anakUsaha' => $anakUsaha,
            ]
        );
    }

    /**
     * Store a newly created anak usaha.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'website' => [
                'nullable',
                'string',
                'max:255',
                'url',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024',
            ],

            'aktif' => [
                'required',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Tentukan urutan berikutnya
        |--------------------------------------------------------------------------
        */

        $validated['urutan'] =
            (AnakUsaha::max('urutan') ?? 0) + 1;

        /*
        |--------------------------------------------------------------------------
        | Upload Logo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request
                ->file('logo')
                ->store('anak-usaha', 'public');
        }

        AnakUsaha::create($validated);

        return back()->with(
            'success',
            'Anak usaha berhasil ditambahkan.'
        );
    }

    /**
     * Update the specified anak usaha.
     */
    public function update(
        Request $request,
        AnakUsaha $anakUsaha
    ) {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'website' => [
                'nullable',
                'string',
                'max:255',
                'url',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:1024',
            ],

            'aktif' => [
                'required',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Upload Logo Baru
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {
            // Hapus logo lama
            if (
                $anakUsaha->logo &&
                Storage::disk('public')->exists(
                    $anakUsaha->logo
                )
            ) {
                Storage::disk('public')->delete(
                    $anakUsaha->logo
                );
            }

            // Simpan logo baru
            $validated['logo'] = $request
                ->file('logo')
                ->store('anak-usaha', 'public');
        } else {
            // Pertahankan logo lama
            unset($validated['logo']);
        }

        $anakUsaha->update($validated);

        return back()->with(
            'success',
            'Anak usaha berhasil diperbarui.'
        );
    }

    /**
     * Remove the specified anak usaha.
     */
    public function destroy(AnakUsaha $anakUsaha)
    {
        /*
        |--------------------------------------------------------------------------
        | Hapus Logo
        |--------------------------------------------------------------------------
        */

        if (
            $anakUsaha->logo &&
            Storage::disk('public')->exists(
                $anakUsaha->logo
            )
        ) {
            Storage::disk('public')->delete(
                $anakUsaha->logo
            );
        }

        $deletedOrder = $anakUsaha->urutan;

        $anakUsaha->delete();

        /*
        |--------------------------------------------------------------------------
        | Rapikan urutan setelah data dihapus
        |--------------------------------------------------------------------------
        */

        AnakUsaha::where(
            'urutan',
            '>',
            $deletedOrder
        )->decrement('urutan');

        return back()->with(
            'success',
            'Anak usaha berhasil dihapus.'
        );
    }

    /**
     * Toggle active status.
     */
    public function toggleAktif(
        AnakUsaha $anakUsaha
    ) {
        $anakUsaha->update([
            'aktif' => !$anakUsaha->aktif,
        ]);

        $message = $anakUsaha->aktif
            ? 'Anak usaha berhasil diaktifkan.'
            : 'Anak usaha berhasil dinonaktifkan.';

        return back()->with(
            'success',
            $message
        );
    }

    /**
     * Move anak usaha order.
     */
    public function move(
        Request $request,
        AnakUsaha $anakUsaha
    ) {
        $validated = $request->validate([
            'direction' => [
                'required',
                'in:up,down',
            ],
        ]);

        $direction = $validated['direction'];

        /*
        |--------------------------------------------------------------------------
        | Cari Item Tetangga
        |--------------------------------------------------------------------------
        */

        if ($direction === 'up') {
            $neighbor = AnakUsaha::where(
                'urutan',
                '<',
                $anakUsaha->urutan
            )
                ->orderByDesc('urutan')
                ->first();
        } else {
            $neighbor = AnakUsaha::where(
                'urutan',
                '>',
                $anakUsaha->urutan
            )
                ->orderBy('urutan')
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Jika Tidak Ada Tetangga
        |--------------------------------------------------------------------------
        */

        if (!$neighbor) {
            return back()->with(
                'error',
                $direction === 'up'
                    ? 'Anak usaha sudah berada di urutan paling atas.'
                    : 'Anak usaha sudah berada di urutan paling bawah.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Tukar Urutan
        |--------------------------------------------------------------------------
        */

        $currentOrder = $anakUsaha->urutan;

        $anakUsaha->update([
            'urutan' => $neighbor->urutan,
        ]);

        $neighbor->update([
            'urutan' => $currentOrder,
        ]);

        return back()->with(
            'success',
            'Urutan anak usaha berhasil diperbarui.'
        );
    }
}
