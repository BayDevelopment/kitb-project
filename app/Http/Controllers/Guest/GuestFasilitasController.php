<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestFasilitasController extends Controller
{
    /**
     * Menampilkan daftar fasilitas kawasan.
     */
    public function index(): Response
    {
        $fasilitas = Fasilitas::query()
            ->where('aktif', true)
            ->ordered()
            ->get();

        return Inertia::render('Kawasan/Fasilitas', [
            'fasilitas' => $fasilitas,
        ]);
    }

    /**
     * Menampilkan detail fasilitas berdasarkan slug.
     */
    public function show(string $slug): Response
    {
        $fasilitas = Fasilitas::query()
            ->where('aktif', true)
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Kawasan/FasilitasDetail', [
            'fasilitas' => $fasilitas,
        ]);
    }
}
