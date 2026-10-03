<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\AnakUsaha;
use Illuminate\Http\Request;
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
                'nama',
                'logo',
                'deskripsi',
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
