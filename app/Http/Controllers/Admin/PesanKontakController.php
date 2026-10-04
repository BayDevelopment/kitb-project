<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PesanKontak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PesanKontakController extends Controller
{
    /**
     * Daftar pesan masuk.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $pesans = PesanKontak::query()
            ->cari($search ?: null)
            ->status($status ?: null)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = PesanKontak::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('admin/Kontak/PesanMasuk', [
            'pesans' => $pesans,

            'counts' => [
                'semua' => (int) $counts->sum(),
                'baru' => (int) ($counts[PesanKontak::STATUS_BARU] ?? 0),
                'dibaca' => (int) ($counts[PesanKontak::STATUS_DIBACA] ?? 0),
                'dibalas' => (int) ($counts[PesanKontak::STATUS_DIBALAS] ?? 0),
            ],

            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Update status pesan.
     */
    public function updateStatus(
        Request $request,
        PesanKontak $pesan
    ): RedirectResponse {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    PesanKontak::STATUS_BARU,
                    PesanKontak::STATUS_DIBACA,
                ]),
            ],
        ]);

        // Pesan yang sudah dibalas tidak boleh diubah statusnya.
        if (filled($pesan->balasan)) {
            return back();
        }

        $pesan->status = $data['status'];

        if ($data['status'] === PesanKontak::STATUS_BARU) {
            $pesan->dibaca_at = null;
        } elseif ($pesan->dibaca_at === null) {
            $pesan->dibaca_at = now();
        }

        $pesan->save();

        return back();
    }

    /**
     * Kirim balasan pesan melalui email HTML KITB.
     */
    public function reply(
        Request $request,
        PesanKontak $pesan
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'balasan' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ], [
            'balasan.required' => 'Balasan wajib diisi.',
            'balasan.min' => 'Balasan minimal 10 karakter.',
            'balasan.max' => 'Balasan maksimal 5.000 karakter.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cegah balasan ganda
        |--------------------------------------------------------------------------
        */

        if (
            filled($pesan->balasan) ||
            $pesan->dibalas_at !== null
        ) {
            return back()->withErrors([
                'balasan' => 'Pesan ini sudah pernah dibalas.',
            ]);
        }

        $balasan = trim($data['balasan']);

        /*
        |--------------------------------------------------------------------------
        | Subject email
        |--------------------------------------------------------------------------
        */

        $subjek = trim($pesan->subjek);

        $subject = str_starts_with(
            strtolower($subjek),
            're:'
        )
            ? $subjek
            : 'Re: ' . $subjek;

        /*
        |--------------------------------------------------------------------------
        | Kirim email HTML langsung dari Blade
        |--------------------------------------------------------------------------
        |
        | Tidak menggunakan Mailable.
        | Blade:
        | resources/views/emails/pesan-kontak-balasan.blade.php
        |
        */

        try {
            Mail::send(
                'emails.pesan-kontak-balasan',
                [
                    'pesanKontak' => $pesan,
                    'balasan' => $balasan,
                ],
                function ($message) use ($pesan, $subject) {
                    $message
                        ->to($pesan->email, $pesan->nama)
                        ->subject($subject);
                }
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'balasan' => 'Email gagal dikirim ke '
                    . $pesan->email
                    . '. Balasan belum disimpan, silakan coba lagi.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan balasan setelah email berhasil
        |--------------------------------------------------------------------------
        */

        $pesan->forceFill([
            'balasan' => $balasan,
            'status' => PesanKontak::STATUS_DIBALAS,
            'dibaca_at' => $pesan->dibaca_at ?? now(),
            'dibalas_at' => now(),
        ])->save();

        /*
        |--------------------------------------------------------------------------
        | Response sukses
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            'Balasan berhasil dikirim ke ' . $pesan->email . '.'
        );
    }

    /**
     * Hapus pesan.
     */
    public function destroy(
        PesanKontak $pesan
    ): RedirectResponse {
        $pesan->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Pesan berhasil dihapus.',
        ]);
    }
}
