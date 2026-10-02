<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LamaranStatus;
use App\Http\Controllers\Controller;
use App\Models\Lamaran;
use App\Models\Lowongan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LamaranController extends Controller
{
    /**
     * Daftar seluruh lamaran.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $lowonganId = $request->input('lowongan_id');

        $lamarans = Lamaran::query()
            ->with([
                'lowongan:id,judul',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->search($search);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($lowonganId, function ($query) use ($lowonganId) {
                $query->where('lowongan_id', $lowonganId);
            })
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        /**
         * Statistik berdasarkan seluruh data,
         * bukan hanya data yang sedang tampil di pagination.
         */
        $statusStats = collect(LamaranStatus::cases())
            ->mapWithKeys(function (LamaranStatus $case) {
                return [
                    $case->value => Lamaran::query()
                        ->where('status', $case->value)
                        ->count(),
                ];
            });

        $stats = [
            'total' => Lamaran::query()->count(),
            'by_status' => $statusStats,
        ];

        /**
         * Daftar lowongan untuk filter dan form edit.
         */
        $lowongans = Lowongan::query()
            ->select([
                'id',
                'judul',
            ])
            ->orderBy('judul')
            ->get();

        /**
         * Daftar status untuk filter dan form edit.
         */
        $statuses = collect(LamaranStatus::cases())
            ->map(fn(LamaranStatus $case) => [
                'value' => $case->value,
                'label' => str($case->value)
                    ->replace(['_', '-'], ' ')
                    ->title()
                    ->toString(),
            ])
            ->values();

        return Inertia::render('admin/Recruitment/Lamaran', [
            'lamarans' => $lamarans,
            'lowongans' => $lowongans,
            'statuses' => $statuses,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'lowongan_id' => $lowonganId,
            ],
        ]);
    }

    /**
     * Update data pelamar.
     */
    public function update(
        Request $request,
        Lamaran $lamaran
    ): RedirectResponse {
        $validated = $request->validate([
            'lowongan_id' => [
                'required',
                'integer',
                'exists:lowongans,id',
            ],

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

            'status' => [
                'required',
                Rule::enum(LamaranStatus::class),
            ],

            'submitted_at' => [
                'nullable',
                'date',
            ],

            'cv' => [
                'nullable',
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
        ], [
            'cv.mimes' => 'CV harus berupa PDF, DOC, atau DOCX.',
            'cv.max' => 'Ukuran CV maksimal 1 MB.',

            'surat_lamaran.mimes' => 'Surat lamaran harus berupa PDF, DOC, atau DOCX.',
            'surat_lamaran.max' => 'Ukuran surat lamaran maksimal 1 MB.',

            'email.email' => 'Format email tidak valid.',

            'linkedin.url' => 'Format URL LinkedIn tidak valid.',
            'portfolio.url' => 'Format URL portfolio tidak valid.',
        ]);

        /**
         * Cegah email yang sama melamar lowongan yang sama,
         * kecuali data tersebut adalah lamaran yang sedang diedit.
         */
        $duplicate = Lamaran::query()
            ->where('id', '!=', $lamaran->id)
            ->where('lowongan_id', $validated['lowongan_id'])
            ->where('email', $validated['email'])
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors([
                    'email' => 'Email ini sudah digunakan untuk melamar posisi tersebut.',
                ])
                ->withInput();
        }

        $oldCv = $lamaran->cv;
        $oldSuratLamaran = $lamaran->surat_lamaran;

        $cvPath = $oldCv;
        $suratLamaranPath = $oldSuratLamaran;

        try {
            /**
             * Upload CV baru.
             */
            if ($request->hasFile('cv')) {
                $cvPath = $request
                    ->file('cv')
                    ->store('lamaran/cv', 'public');
            }

            /**
             * Upload surat lamaran baru.
             */
            if ($request->hasFile('surat_lamaran')) {
                $suratLamaranPath = $request
                    ->file('surat_lamaran')
                    ->store('lamaran/surat', 'public');
            }

            /**
             * Update database.
             */
            $lamaran->update([
                'lowongan_id' => $validated['lowongan_id'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'],
                'linkedin' => $validated['linkedin'] ?? null,
                'portfolio' => $validated['portfolio'] ?? null,
                'pesan' => $validated['pesan'] ?? null,
                'status' => $validated['status'],
                'submitted_at' => $validated['submitted_at']
                    ?? $lamaran->submitted_at,
                'cv' => $cvPath,
                'surat_lamaran' => $suratLamaranPath,
            ]);

            /**
             * Hapus CV lama setelah database berhasil diperbarui.
             */
            if (
                $request->hasFile('cv')
                && $oldCv
                && $oldCv !== $cvPath
            ) {
                Storage::disk('public')->delete($oldCv);
            }

            /**
             * Hapus surat lamaran lama setelah database berhasil diperbarui.
             */
            if (
                $request->hasFile('surat_lamaran')
                && $oldSuratLamaran
                && $oldSuratLamaran !== $suratLamaranPath
            ) {
                Storage::disk('public')->delete($oldSuratLamaran);
            }
        } catch (\Throwable $e) {
            /**
             * Jika file baru sudah tersimpan tetapi update gagal,
             * hapus file baru agar tidak menjadi orphan file.
             */
            if (
                $request->hasFile('cv')
                && $cvPath !== $oldCv
            ) {
                Storage::disk('public')->delete($cvPath);
            }

            if (
                $request->hasFile('surat_lamaran')
                && $suratLamaranPath !== $oldSuratLamaran
            ) {
                Storage::disk('public')->delete($suratLamaranPath);
            }

            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Data lamaran gagal diperbarui. Silakan coba kembali.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Data lamaran berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus lamaran.
     *
     * File CV dan surat lamaran akan otomatis
     * dihapus oleh booted()->deleting() pada model Lamaran.
     */
    public function destroy(Lamaran $lamaran): RedirectResponse
    {
        try {
            $lamaran->delete();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Lamaran gagal dihapus. Silakan coba kembali.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Data lamaran berhasil dihapus.',
        ]);
    }

    /**
     * Download CV pelamar.
     */
    public function downloadCv(Lamaran $lamaran)
    {
        abort_unless(
            $lamaran->cv
                && Storage::disk('public')->exists($lamaran->cv),
            404
        );

        $path = Storage::disk('public')->path($lamaran->cv);

        return response()->download(
            $path,
            'CV - '
                . $lamaran->nama_lengkap
                . '.'
                . pathinfo($lamaran->cv, PATHINFO_EXTENSION)
        );
    }

    /**
     * Download surat lamaran.
     */
    public function downloadSurat(Lamaran $lamaran)
    {
        abort_unless(
            $lamaran->surat_lamaran
                && Storage::disk('public')->exists($lamaran->surat_lamaran),
            404
        );

        $path = Storage::disk('public')->path($lamaran->surat_lamaran);

        return response()->download(
            $path,
            'Surat Lamaran - '
                . $lamaran->nama_lengkap
                . '.'
                . pathinfo(
                    $lamaran->surat_lamaran,
                    PATHINFO_EXTENSION
                )
        );
    }
}
