<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Visi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestVisiMisiController extends Controller
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
