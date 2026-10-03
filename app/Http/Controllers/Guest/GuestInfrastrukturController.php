<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Infrastruktur;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestInfrastrukturController extends Controller
{
    public function index(): Response
    {
        $infrastrukturs = Infrastruktur::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get([
                'id',
                'nama',
                'slug',
                'deskripsi',
                'gambar',
                'urutan',
            ]);

        return Inertia::render(
            'Kawasan/Infrastruktur',
            [
                'infrastrukturs' => $infrastrukturs,
            ]
        );
    }
}
