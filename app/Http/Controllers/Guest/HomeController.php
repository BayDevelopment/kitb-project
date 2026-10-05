<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\AnakUsaha;
use App\Models\Berita;
use App\Models\CompanyProfile;
use App\Models\Lowongan;
use App\Models\MitraPerusahaan;
use App\Models\PeluangInvestasi;
use App\Models\PetaKawasan;
use App\Models\ProfilKawasan;
use App\Models\Rute;
use App\Models\SambutanDirektur;
use App\Models\Visi;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $companyProfile = CompanyProfile::query()
            ->where('aktif', true)
            ->first();

        $visi = Visi::query()
            ->with([
                'misis' => fn($query) => $query
                    ->orderBy('urutan')
                    ->orderBy('id'),
            ])
            ->first();

        $anakUsahas = AnakUsaha::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $profilKawasan = ProfilKawasan::query()
            ->where('status', true)
            ->orderBy('id')
            ->first();

        $petaKawasan = PetaKawasan::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->first();

        $peluangInvestasi = PeluangInvestasi::query()
            ->where('aktif', true)
            ->where('status', 'tersedia')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $rutes = Rute::query()
            ->where('aktif', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $beritas = Berita::query()
            ->where('status', 'published')
            ->where(function ($query) {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $mitraPerusahaans = MitraPerusahaan::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $lowongans = Lowongan::query()
            ->where('status', 'published')
            ->where(function ($query) {
                $query
                    ->whereNull('tanggal_mulai')
                    ->orWhereDate('tanggal_mulai', '<=', today());
            })
            ->where(function ($query) {
                $query
                    ->whereNull('tanggal_tutup')
                    ->orWhereDate('tanggal_tutup', '>=', today());
            })
            ->orderByDesc('unggulan')
            ->orderBy('urutan')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Sambutan Direktur
        |--------------------------------------------------------------------------
        | Hanya data dengan status aktif yang dikirim ke halaman publik.
        | Jika status false atau data belum tersedia, hasilnya null.
        */
        $sambutanDirektur = SambutanDirektur::query()
            ->where('status', true)
            ->first();

        return Inertia::render('Index', [
            'companyProfile' => $companyProfile,
            'visi' => $visi,
            'anakUsahas' => $anakUsahas,
            'mitraPerusahaans' => $mitraPerusahaans,
            'profilKawasan' => $profilKawasan,
            'petaKawasan' => $petaKawasan,
            'peluangInvestasi' => $peluangInvestasi,
            'rutes' => $rutes,
            'beritas' => $beritas,
            'lowongans' => $lowongans,
            'sambutanDirektur' => $sambutanDirektur,
        ]);
    }
}
