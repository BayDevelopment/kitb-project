<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\PetaKawasan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestPetaKawasanController extends Controller
{
    public function index(Request $request): Response
    {
        $petaKawasans = PetaKawasan::query()
            ->where('aktif', true)
            ->ordered()
            ->get([
                'id',
                'nama',
                'nama_en',
                'nama_zh',
                'slug',
                'deskripsi',
                'deskripsi_en',
                'deskripsi_zh',
                'gambar',
                'urutan',
                'aktif',
            ]);

        return Inertia::render('Kawasan/PetaKawasan', [
            'petaKawasans' => $petaKawasans,
            'selectedSlug' => $request->query('kawasan'),
        ]);
    }
}
