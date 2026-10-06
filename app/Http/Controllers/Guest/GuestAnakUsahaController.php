<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\AnakUsaha;
use Inertia\Inertia;
use Inertia\Response;

class GuestAnakUsahaController extends Controller
{
    public function index(): Response
    {
        $anakUsahas = AnakUsaha::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get([
                'id',

                // Nama multilingual
                'nama',
                'nama_en',
                'nama_zh',

                // Logo
                'logo',

                // Deskripsi multilingual
                'deskripsi',
                'deskripsi_en',
                'deskripsi_zh',

                // Informasi lainnya
                'website',
                'urutan',
            ]);

        return Inertia::render(
            'ProfilPerusahaan/AnakUsaha',
            [
                'anakUsahas' => $anakUsahas,
            ]
        );
    }
}
