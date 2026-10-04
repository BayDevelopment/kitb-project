<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\PengaturanKontak;
use App\Models\PesanKontak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class GuestKontakController extends Controller
{
    /**
     * Menampilkan halaman kontak publik.
     */
    public function index(): Response
    {
        return Inertia::render('Kontak/Index', [
            'kontak' => PengaturanKontak::instance()->only([
                'nama_perusahaan',
                'alamat',
                'telepon',
                'whatsapp',
                'email',
                'email_investor',
                'jam_operasional',
                'latitude',
                'longitude',
                'maps_embed_url',
                'facebook',
                'instagram',
                'linkedin',
                'youtube',
            ]),
        ]);
    }

    /**
     * Menyimpan pesan dari form kontak.
     */
    public function store(Request $request)
    {
        $pesanSukses = 'Pesan berhasil dikirim. Terima kasih telah menghubungi kami. Tim KITB akan membalas melalui email Anda.';

        // Honeypot: bot mengisi field tersembunyi. Abaikan diam-diam.
        if ($request->filled('website')) {
            return back()->with('success', $pesanSukses);
        }

        $data = $request->validate([
            'nama'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:150'],
            'telepon'    => ['nullable', 'string', 'max:30'],
            'perusahaan' => ['nullable', 'string', 'max:150'],
            'subjek'     => ['required', 'string', 'max:150'],
            'pesan'      => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        // Cegah duplikat: isi yang sama dalam 5 menit dianggap kiriman ganda.
        // Cache::add bersifat atomik, jadi aman walau dua request datang bersamaan.
        $fingerprint = 'kontak:' . sha1(
            strtolower($data['email']) . '|' . $data['subjek'] . '|' . $data['pesan']
        );

        if (! Cache::add($fingerprint, true, now()->addMinutes(5))) {
            return back()->with('success', $pesanSukses);
        }

        try {
            PesanKontak::create([
                'nama'       => $data['nama'],
                'email'      => $data['email'],
                'telepon'    => $data['telepon'] ?? null,
                'perusahaan' => $data['perusahaan'] ?? null,
                'subjek'     => $data['subjek'],
                'pesan'      => $data['pesan'],
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // Gagal simpan: lepas kunci supaya pengunjung bisa mencoba lagi.
            Cache::forget($fingerprint);

            throw $e;
        }

        return back()->with('success', $pesanSukses);
    }
}
