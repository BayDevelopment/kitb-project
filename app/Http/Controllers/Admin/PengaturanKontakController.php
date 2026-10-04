<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanKontak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PengaturanKontakController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Kontak/PengaturanKontak', [
            'pengaturan' => PengaturanKontak::instance(),
        ]);
    }

    public function edit(): Response
    {
        return Inertia::render('admin/Kontak/PengaturanKontak', [
            'pengaturan' => PengaturanKontak::instance(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_perusahaan' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:5000'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'email_investor' => ['nullable', 'email', 'max:150'],
            'jam_operasional' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'maps_embed_url' => ['nullable', 'string', 'max:5000'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
        ]);

        $pengaturan = PengaturanKontak::instance();

        $pengaturan->update($data);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Pengaturan kontak berhasil diperbarui.',
        ]);
    }
}
