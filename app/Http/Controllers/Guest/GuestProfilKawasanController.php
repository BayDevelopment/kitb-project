<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\ProfilKawasan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestProfilKawasanController extends Controller
{
    public function index(): Response
    {
        $profilKawasans = ProfilKawasan::query()
            ->where('status', true)
            ->orderByDesc('tahun_berdiri')
            ->orderByDesc('id')
            ->get([
                'id',
                'judul',
                'slug',
                'deskripsi',
                'luas_kawasan',
                'lokasi',
                'tahun_berdiri',
                'gambar',
            ]);

        return Inertia::render(
            'Kawasan/ProfileKawasan',
            [
                'profilKawasans' => $profilKawasans,
            ]
        );
    }
}
