<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\StrukturPerusahaan;
use Inertia\Inertia;
use Inertia\Response;

class GuestStrukturPerusahaanController extends Controller
{
    public function index(): Response
    {
        $strukturPerusahaans = StrukturPerusahaan::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get([
                'id',
                'nama',
                'nama_en',
                'nama_zh',
                'jabatan',
                'jabatan_en',
                'jabatan_zh',
                'gambar',
                'urutan',
            ]);

        return Inertia::render(
            'ProfilPerusahaan/StrukturPerusahaan',
            [
                'strukturPerusahaans' => $strukturPerusahaans,
            ]
        );
    }
}
