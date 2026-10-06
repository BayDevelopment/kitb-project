<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\ProfilKawasan;
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
                'deskripsi_en',
                'deskripsi_zh',
                'luas_kawasan',
                'lokasi',
                'latitude',
                'longitude',
                'batas_kawasan',
                'tahun_berdiri',
                'status',
                'gambar',
            ]);

        return Inertia::render('Kawasan/ProfileKawasan', [
            'profilKawasans' => $profilKawasans,
        ]);
    }
}
