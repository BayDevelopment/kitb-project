<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\PeluangInvestasi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestPeluangInvestasiController extends Controller
{
    /**
     * Menampilkan daftar peluang investasi yang aktif.
     */
    public function index(Request $request): Response
    {
        $peluangInvestasis = PeluangInvestasi::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('HubunganInvestor/PeluangInvestasi', [
            'peluangInvestasis' => $peluangInvestasis,
        ]);
    }
}
