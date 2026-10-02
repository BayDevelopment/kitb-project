<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TentangKamiController extends Controller
{
    public function index(): Response
    {
        $companyProfile = CompanyProfile::query()
            ->where('aktif', true)
            ->first();

        return Inertia::render('ProfilPerusahaan/TentangKami', [
            'companyProfile' => $companyProfile,
        ]);
    }
}
