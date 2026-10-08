<?php

namespace App\Http\Controllers;

use App\Models\Lamaran;
use App\Models\Lowongan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PublicLamaranController extends Controller
{
    /**
     * Menampilkan formulir lamaran untuk lowongan yang dipublikasikan.
     */
    public function create(Lowongan $lowongan): Response
    {
        abort_unless($lowongan->status === 'published', 404);

        return Inertia::render('Karier/Lamaran', [
            'lowongan' => $lowongan,
        ]);
    }

    /**
     * Menyimpan lamaran dari halaman publik.
     */
    public function store(
        Request $request,
        Lowongan $lowongan
    ): RedirectResponse {
        abort_unless($lowongan->status === 'published', 404);

        $validated = $request->validate([
            'nama_lengkap' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'no_hp' => [
                'required',
                'string',
                'max:30',
            ],

            'cv' => [
                'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:1024',
            ],

            'surat_lamaran' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:1024',
            ],

            'linkedin' => [
                'nullable',
                'url',
                'max:255',
            ],

            'portfolio' => [
                'nullable',
                'url',
                'max:255',
            ],

            // Pesan lamaran dalam tiga bahasa.
            'pesan_id' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'pesan_en' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'pesan_zh' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'nama_lengkap.required' =>
            'Nama lengkap wajib diisi.',
            'nama_lengkap.min' =>
            'Nama lengkap minimal 3 karakter.',
            'nama_lengkap.max' =>
            'Nama lengkap maksimal 255 karakter.',

            'email.required' =>
            'Email wajib diisi.',
            'email.email' =>
            'Format email tidak valid.',
            'email.max' =>
            'Email maksimal 255 karakter.',

            'no_hp.required' =>
            'Nomor HP wajib diisi.',
            'no_hp.max' =>
            'Nomor HP maksimal 30 karakter.',

            'cv.required' =>
            'CV wajib diunggah.',
            'cv.file' =>
            'CV yang diunggah tidak valid.',
            'cv.mimes' =>
            'CV harus berupa PDF, DOC, atau DOCX.',
            'cv.max' =>
            'Ukuran CV maksimal 1 MB.',

            'surat_lamaran.file' =>
            'Surat lamaran yang diunggah tidak valid.',
            'surat_lamaran.mimes' =>
            'Surat lamaran harus berupa PDF, DOC, atau DOCX.',
            'surat_lamaran.max' =>
            'Ukuran surat lamaran maksimal 1 MB.',

            'linkedin.url' =>
            'Format URL LinkedIn tidak valid.',
            'portfolio.url' =>
            'Format URL portfolio tidak valid.',

            'pesan_id.max' =>
            'Pesan Bahasa Indonesia maksimal 2000 karakter.',
            'pesan_en.max' =>
            'Pesan Bahasa Inggris maksimal 2000 karakter.',
            'pesan_zh.max' =>
            'Pesan Bahasa Mandarin maksimal 2000 karakter.',
        ]);

        /*
         * Normalisasi email agar perbedaan huruf besar/kecil
         * tidak menyebabkan email yang sama lolos dari pemeriksaan.
         */
        $email = mb_strtolower(trim($validated['email']));

        /*
         * Cegah email yang sama melamar lowongan yang sama.
         */
        $alreadyApplied = Lamaran::query()
            ->where('lowongan_id', $lowongan->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();

        if ($alreadyApplied) {
            return back()
                ->withErrors([
                    'email' =>
                    'Email ini sudah digunakan untuk melamar posisi ini.',
                ])
                ->withInput();
        }

        $cvPath = null;
        $suratLamaranPath = null;

        try {
            /*
             * Simpan CV wajib.
             */
            $cvPath = $request
                ->file('cv')
                ->store('lamaran/cv', 'public');

            if (!$cvPath) {
                throw new \RuntimeException(
                    'CV gagal disimpan ke penyimpanan.'
                );
            }

            /*
             * Simpan surat lamaran jika diunggah.
             */
            if ($request->hasFile('surat_lamaran')) {
                $suratLamaranPath = $request
                    ->file('surat_lamaran')
                    ->store('lamaran/surat', 'public');

                if (!$suratLamaranPath) {
                    throw new \RuntimeException(
                        'Surat lamaran gagal disimpan ke penyimpanan.'
                    );
                }
            }

            /*
             * Simpan data lamaran ke database.
             */
            DB::transaction(function () use (
                $lowongan,
                $validated,
                $email,
                $cvPath,
                $suratLamaranPath
            ): void {
                Lamaran::create([
                    'lowongan_id' => $lowongan->id,

                    'nama_lengkap' => trim(
                        $validated['nama_lengkap']
                    ),

                    'email' => $email,

                    'no_hp' => trim($validated['no_hp']),

                    'cv' => $cvPath,

                    'surat_lamaran' => $suratLamaranPath,

                    'linkedin' => $validated['linkedin'] ?? null,

                    'portfolio' => $validated['portfolio'] ?? null,

                    // Pesan lamaran dalam tiga bahasa.
                    'pesan_id' => $validated['pesan_id'] ?? null,

                    'pesan_en' => $validated['pesan_en'] ?? null,

                    'pesan_zh' => $validated['pesan_zh'] ?? null,

                    'status' => 'submitted',

                    'submitted_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            /*
             * Hapus file yang sudah terunggah jika proses
             * penyimpanan lamaran gagal.
             */
            if (is_string($cvPath) && $cvPath !== '') {
                Storage::disk('public')->delete($cvPath);
            }

            if (
                is_string($suratLamaranPath)
                && $suratLamaranPath !== ''
            ) {
                Storage::disk('public')->delete(
                    $suratLamaranPath
                );
            }

            report($e);

            return back()
                ->with(
                    'error',
                    'Lamaran gagal dikirim. Silakan coba kembali.'
                )
                ->withInput();
        }

        return back()->with(
            'success',
            'Lamaran berhasil dikirim. Tim rekrutmen akan meninjau data dan dokumen Anda lebih lanjut.'
        );
    }
}
