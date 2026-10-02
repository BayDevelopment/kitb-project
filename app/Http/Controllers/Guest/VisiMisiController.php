<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Visi;
use Inertia\Inertia;
use Inertia\Response;

class VisiMisiController extends Controller
{
    public function index(): Response
    {
        $visi = Visi::query()
            ->with([
                'misis' => fn($query) => $query
                    ->orderBy('urutan')
                    ->orderBy('id'),
            ])
            ->first();

        return Inertia::render('ProfilPerusahaan/VisiMisi', [
            'visi' => $visi,
        ]);
    }
}
