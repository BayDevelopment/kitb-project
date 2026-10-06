<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Inertia\Inertia;
use Inertia\Response;

class TentangKamiController extends Controller
{
    public function index(): Response
    {
        $companyProfile = CompanyProfile::query()
            ->where('aktif', true)
            ->select([
                'id',
                'nama_perusahaan',

                'tentang_kami',
                'tentang_kami_en',
                'tentang_kami_zh',

                'latar_belakang',
                'latar_belakang_en',
                'latar_belakang_zh',

                'moto',
                'moto_en',
                'moto_zh',

                'alamat',
                'email',
                'telepon',
                'website',
                'logo',
                'aktif',
            ])
            ->first();

        return Inertia::render('ProfilPerusahaan/TentangKami', [
            'companyProfile' => $companyProfile,
        ]);
    }
}
