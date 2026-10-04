<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\ProfilKawasan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestPetaKawasanController extends Controller
{
    public function index(Request $request): Response
    {
        $kawasans = ProfilKawasan::query()
            ->where('status', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('tahun_berdiri')
            ->orderByDesc('id')
            ->get([
                'id',
                'judul',
                'slug',
                'lokasi',
                'luas_kawasan',
                'tahun_berdiri',
                'gambar',
                'latitude',
                'longitude',
                'batas_kawasan',
            ]);

        return Inertia::render('Kawasan/PetaKawasan', [
            'kawasans' => $kawasans,
            'selectedSlug' => $request->query('kawasan'),
        ]);
    }
}
