<?php

namespace App\Http\Controllers;

use App\Models\Lamaran;
use App\Models\Lowongan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicLowonganKerjaController extends Controller
{
    /**
     * Daftar lowongan pekerjaan.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $departemen = (string) $request->input('departemen', '');
        $tipe = (string) $request->input('tipe', '');

        /*
        |--------------------------------------------------------------------------
        | Query dasar
        |--------------------------------------------------------------------------
        */

        $base = Lowongan::query()->active();

        /*
        |--------------------------------------------------------------------------
        | Daftar departemen
        |--------------------------------------------------------------------------
        |
        | Database menggunakan departemen_id, bukan departemen.
        |
        */

        $departments = (clone $base)
            ->whereNotNull('departemen_id')
            ->where('departemen_id', '!=', '')
            ->distinct()
            ->orderBy('departemen_id')
            ->pluck('departemen_id');

        /*
        |--------------------------------------------------------------------------
        | Daftar tipe pekerjaan
        |--------------------------------------------------------------------------
        */

        $types = (clone $base)
            ->whereNotNull('tipe_pekerjaan')
            ->where('tipe_pekerjaan', '!=', '')
            ->distinct()
            ->orderBy('tipe_pekerjaan')
            ->pluck('tipe_pekerjaan');

        /*
        |--------------------------------------------------------------------------
        | Daftar lowongan
        |--------------------------------------------------------------------------
        */

        $lowongans = (clone $base)
            ->search($search)
            ->when(
                $departemen !== '',
                fn($q) => $q->where(
                    'departemen_id',
                    $departemen
                )
            )
            ->when(
                $tipe !== '',
                fn($q) => $q->where(
                    'tipe_pekerjaan',
                    $tipe
                )
            )
            ->orderByDesc('unggulan')
            ->orderBy('urutan')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString()
            ->through(
                fn(Lowongan $l) => [
                    'id' => $l->id,

                    /*
                    |--------------------------------------------------------------------------
                    | Judul multilingual
                    |--------------------------------------------------------------------------
                    */

                    'judul_id' => $l->judul_id,
                    'judul_en' => $l->judul_en,
                    'judul_zh' => $l->judul_zh,

                    'slug' => $l->slug,

                    /*
                    |--------------------------------------------------------------------------
                    | Deskripsi multilingual
                    |--------------------------------------------------------------------------
                    */

                    'deskripsi_id' => $l->deskripsi_id,
                    'deskripsi_en' => $l->deskripsi_en,
                    'deskripsi_zh' => $l->deskripsi_zh,

                    /*
                    |--------------------------------------------------------------------------
                    | Departemen multilingual
                    |--------------------------------------------------------------------------
                    */

                    'departemen_id' => $l->departemen_id,
                    'departemen_en' => $l->departemen_en,
                    'departemen_zh' => $l->departemen_zh,

                    /*
                    |--------------------------------------------------------------------------
                    | Informasi pekerjaan
                    |--------------------------------------------------------------------------
                    */

                    'tipe_pekerjaan' => $l->tipe_pekerjaan,

                    /*
                    |--------------------------------------------------------------------------
                    | Lokasi multilingual
                    |--------------------------------------------------------------------------
                    */

                    'lokasi_id' => $l->lokasi_id,
                    'lokasi_en' => $l->lokasi_en,
                    'lokasi_zh' => $l->lokasi_zh,

                    /*
                    |--------------------------------------------------------------------------
                    | Tanggal
                    |--------------------------------------------------------------------------
                    */

                    'tanggal_mulai' =>
                    $l->tanggal_mulai?->toDateString(),

                    'tanggal_tutup' =>
                    $l->tanggal_tutup?->toDateString(),
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Response Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Karier/Index', [
            'lowongans' => $lowongans,

            'departments' => $departments,

            'types' => $types,

            'filters' => [
                'search' => $search,
                'departemen' => $departemen,
                'tipe' => $tipe,
            ],
        ]);
    }

    /**
     * Detail lowongan.
     */
    public function show(string $slug): Response
    {
        $lowongan = Lowongan::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Karier/Detail', [
            'lowongan' => $lowongan,
        ]);
    }

    /**
     * Form lamaran.
     */
    public function apply(string $slug): Response
    {
        $lowongan = Lowongan::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Karier/Lamaran', [
            'lowongan' => $lowongan,
        ]);
    }

    /**
     * Simpan lamaran.
     */
    public function store(
        Request $request,
        string $slug
    ): RedirectResponse {
        $lowongan = Lowongan::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Validasi
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate(
            [
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
            ],
            [
                'cv.required' =>
                'CV wajib diunggah.',

                'cv.mimes' =>
                'CV harus berupa PDF, DOC, atau DOCX.',

                'cv.max' =>
                'Ukuran CV maksimal 1 MB.',

                'surat_lamaran.mimes' =>
                'Surat lamaran harus berupa PDF, DOC, atau DOCX.',

                'surat_lamaran.max' =>
                'Ukuran surat lamaran maksimal 1 MB.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Cek email yang sudah pernah melamar
        |--------------------------------------------------------------------------
        */

        $alreadyApplied = Lamaran::query()
            ->where('lowongan_id', $lowongan->id)
            ->where('email', $validated['email'])
            ->exists();

        if ($alreadyApplied) {
            return back()
                ->withErrors([
                    'email' =>
                    'Email ini sudah digunakan untuk melamar posisi tersebut.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Upload dokumen
        |--------------------------------------------------------------------------
        */

        $cvPath = null;
        $suratPath = null;

        try {
            $cvPath = $request
                ->file('cv')
                ->store('lamaran/cv', 'public');

            if ($request->hasFile('surat_lamaran')) {
                $suratPath = $request
                    ->file('surat_lamaran')
                    ->store('lamaran/surat', 'public');
            }

            /*
            |--------------------------------------------------------------------------
            | Simpan lamaran
            |--------------------------------------------------------------------------
            */

            Lamaran::create([
                'lowongan_id' => $lowongan->id,

                'nama_lengkap' =>
                $validated['nama_lengkap'],

                'email' =>
                $validated['email'],

                'no_hp' =>
                $validated['no_hp'],

                'linkedin' =>
                $validated['linkedin'] ?? null,

                'portfolio' =>
                $validated['portfolio'] ?? null,

                'pesan' =>
                $validated['pesan'] ?? null,

                'cv' => $cvPath,

                'surat_lamaran' => $suratPath,

                'status' => 'submitted',

                'submitted_at' => now(),
            ]);
        } catch (\Throwable $e) {
            /*
            |--------------------------------------------------------------------------
            | Hapus file jika penyimpanan database gagal
            |--------------------------------------------------------------------------
            */

            Storage::disk('public')->delete(
                array_filter([
                    $cvPath,
                    $suratPath,
                ])
            );

            report($e);

            return back()
                ->with('toast', [
                    'type' => 'error',
                    'message' =>
                    'Lamaran gagal dikirim. Silakan coba kembali.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Berhasil
        |--------------------------------------------------------------------------
        */

        return back()
            ->with('toast', [
                'type' => 'success',
                'message' =>
                'Lamaran berhasil dikirim. Tim rekrutmen akan meninjau data dan dokumen Anda.',
            ]);
    }
}
