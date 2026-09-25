<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfilPerusahaanController extends Controller
{
    /**
     * Menampilkan daftar profil perusahaan.
     */
    public function tentangKami(Request $request): Response
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:0,1'],
        ]);

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? '';

        $profiles = CompanyProfile::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_perusahaan', 'like', "%{$search}%")
                        ->orWhere('moto', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('aktif', (bool) $status);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'admin/ProfilPerusahaan/TentangKami',
            [
                'profiles' => $profiles,
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                ],
            ]
        );
    }

    /**
     * Menyimpan profil perusahaan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'website' => $this->normalizeWebsite(
                $request->input('website')
            ),
        ]);

        $validated = $this->validateProfile($request);

        $logoPath = null;

        try {
            DB::transaction(function () use (
                $request,
                &$validated,
                &$logoPath
            ) {
                if ($request->hasFile('logo')) {
                    $logoPath = $request
                        ->file('logo')
                        ->store('company/logos', 'public');

                    $validated['logo'] = $logoPath;
                }

                CompanyProfile::create($validated);
            });
        } catch (\Throwable $e) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }

            throw $e;
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Profil perusahaan berhasil ditambahkan.',
        ]);
    }

    /**
     * Memperbarui profil perusahaan.
     */
    public function update(
        Request $request,
        CompanyProfile $companyProfile
    ): RedirectResponse {
        $request->merge([
            'website' => $this->normalizeWebsite(
                $request->input('website')
            ),
        ]);

        $validated = $this->validateProfile($request);

        $oldLogo = $companyProfile->logo;
        $newLogo = null;

        try {
            DB::transaction(function () use (
                $request,
                $companyProfile,
                &$validated,
                &$newLogo
            ) {
                if ($request->hasFile('logo')) {
                    $newLogo = $request
                        ->file('logo')
                        ->store('company/logos', 'public');

                    $validated['logo'] = $newLogo;
                } else {
                    // Jangan menghapus logo lama hanya karena
                    // form edit tidak mengirim file baru.
                    unset($validated['logo']);
                }

                $companyProfile->update($validated);
            });
        } catch (\Throwable $e) {
            // Kalau database gagal, file baru jangan ditinggalkan.
            if ($newLogo) {
                Storage::disk('public')->delete($newLogo);
            }

            throw $e;
        }

        // Hapus logo lama setelah database berhasil diperbarui.
        if ($newLogo && $oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Profil perusahaan berhasil diperbarui.',
        ]);
    }

    /**
     * Menghapus profil perusahaan.
     */
    public function destroy(
        CompanyProfile $companyProfile
    ): RedirectResponse {
        $logo = $companyProfile->logo;

        DB::transaction(function () use ($companyProfile) {
            $companyProfile->delete();
        });

        if ($logo) {
            Storage::disk('public')->delete($logo);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Profil perusahaan berhasil dihapus.',
        ]);
    }

    /**
     * Normalisasi URL website.
     *
     * Contoh:
     * kitb.co.id       -> https://kitb.co.id
     * www.kitb.co.id   -> https://www.kitb.co.id
     * https://kitb.co.id -> tetap
     * kosong            -> null
     */
    private function normalizeWebsite(?string $website): ?string
    {
        $website = trim((string) $website);

        if ($website === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $website)) {
            $website = 'https://' . $website;
        }

        return $website;
    }

    /**
     * Validasi data profil perusahaan.
     */
    private function validateProfile(Request $request): array
    {
        return $request->validate([
            'nama_perusahaan' => [
                'required',
                'string',
                'max:255',
            ],

            'tentang_kami' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'latar_belakang' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'moto' => [
                'nullable',
                'string',
                'max:255',
            ],

            'alamat' => [
                'nullable',
                'string',
                'max:500',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'telepon' => [
                'nullable',
                'string',
                'max:50',
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'logo' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],

            'aktif' => [
                'sometimes',
                'boolean',
            ],
        ]);
    }
}
