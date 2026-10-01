<?php

namespace App\Http\Controllers;

use App\Models\Lamaran;
use App\Models\Lowongan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicLamaranController extends Controller
{
    public function create(Lowongan $lowongan): Response
    {
        abort_unless($lowongan->status === 'published', 404);

        return Inertia::render('Karier/Lamaran', [
            'lowongan' => $lowongan,
        ]);
    }

    public function store(
        Request $request,
        Lowongan $lowongan
    ): RedirectResponse {
        abort_unless($lowongan->status === 'published', 404);

        $validated = $request->validate([
            'nama_lengkap' => [
                'required',
                'string',
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

            'pesan' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'cv.required' => 'CV wajib diunggah.',
            'cv.mimes' => 'CV harus berupa PDF, DOC, atau DOCX.',
            'cv.max' => 'Ukuran CV maksimal 1 MB.',

            'surat_lamaran.mimes' =>
            'Surat lamaran harus berupa PDF, DOC, atau DOCX.',

            'surat_lamaran.max' =>
            'Ukuran surat lamaran maksimal 1 MB.',

            'email.email' =>
            'Format email tidak valid.',

            'linkedin.url' =>
            'Format URL LinkedIn tidak valid.',

            'portfolio.url' =>
            'Format URL portfolio tidak valid.',
        ]);

        $alreadyApplied = Lamaran::query()
            ->where('lowongan_id', $lowongan->id)
            ->where('email', $validated['email'])
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
            $cvPath = $request
                ->file('cv')
                ->store('lamaran/cv', 'public');

            if ($request->hasFile('surat_lamaran')) {
                $suratLamaranPath = $request
                    ->file('surat_lamaran')
                    ->store('lamaran/surat', 'public');
            }

            Lamaran::create([
                'lowongan_id' => $lowongan->id,
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'],
                'cv' => $cvPath,
                'surat_lamaran' => $suratLamaranPath,
                'linkedin' => $validated['linkedin'] ?? null,
                'portfolio' => $validated['portfolio'] ?? null,
                'pesan' => $validated['pesan'] ?? null,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        } catch (\Throwable $e) {
            if ($cvPath) {
                Storage::disk('public')->delete($cvPath);
            }

            if ($suratLamaranPath) {
                Storage::disk('public')->delete($suratLamaranPath);
            }

            report($e);

            return back()->with(
                'error',
                'Lamaran gagal dikirim. Silakan coba kembali.'
            );
        }

        return back()->with(
            'success',
            'Lamaran berhasil dikirim. Tim rekrutmen akan meninjau data dan dokumen Anda lebih lanjut.'
        );
    }
}
