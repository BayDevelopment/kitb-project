<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicProfilPerusahaanController extends Controller
{
    /**
     * Halaman Tentang Kami.
     */
    public function tentangKami(): Response
    {
        $profile = $this->activeProfile();

        return Inertia::render('ProfilPerusahaan/TentangKami', [
            'profile' => $profile ? [
                'nama_perusahaan' => $profile->nama_perusahaan,
                'tentang_kami' => $profile->tentang_kami,
                'moto' => $profile->moto,
                'alamat' => $profile->alamat,
                'email' => $profile->email,
                'telepon' => $profile->telepon,
                'website' => $profile->website,
                'logo_url' => $this->logoUrl($profile->logo),
            ] : null,
        ]);
    }

    /**
     * Halaman Latar Belakang.
     *
     * Datanya ada di tabel yang sama (company_profiles.latar_belakang).
     */
    public function latarBelakang(): Response
    {
        $profile = $this->activeProfile();

        return Inertia::render('ProfilPerusahaan/LatarBelakang', [
            'profile' => $profile ? [
                'nama_perusahaan' => $profile->nama_perusahaan,
                'latar_belakang' => $profile->latar_belakang,
                'moto' => $profile->moto,
                'logo_url' => $this->logoUrl($profile->logo),
            ] : null,
        ]);
    }

    /**
     * Profil perusahaan yang aktif.
     *
     * Tabel dipakai sebagai data tunggal. Jika ada beberapa baris aktif,
     * yang terbaru yang dipakai.
     */
    private function activeProfile(): ?CompanyProfile
    {
        return CompanyProfile::query()
            ->where('aktif', true)
            ->latest('id')
            ->first();
    }

    /**
     * Path logo di disk public diubah menjadi URL.
     */
    private function logoUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($path);
    }
}
