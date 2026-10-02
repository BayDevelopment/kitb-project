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
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $departemen = (string) $request->input('departemen', '');
        $tipe = (string) $request->input('tipe', '');

        $base = Lowongan::query()->active();

        $departments = (clone $base)
            ->whereNotNull('departemen')
            ->where('departemen', '!=', '')
            ->distinct()
            ->orderBy('departemen')
            ->pluck('departemen');

        $types = (clone $base)
            ->whereNotNull('tipe_pekerjaan')
            ->where('tipe_pekerjaan', '!=', '')
            ->distinct()
            ->orderBy('tipe_pekerjaan')
            ->pluck('tipe_pekerjaan');

        $lowongans = (clone $base)
            ->search($search)
            ->when($departemen !== '', fn($q) => $q->where('departemen', $departemen))
            ->when($tipe !== '', fn($q) => $q->where('tipe_pekerjaan', $tipe))
            ->orderByDesc('unggulan')
            ->orderBy('urutan')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString()
            ->through(fn(Lowongan $l) => [
                'id' => $l->id,
                'judul' => $l->judul,
                'slug' => $l->slug,
                'deskripsi' => str(strip_tags((string) $l->deskripsi))->squish()->limit(160)->toString(),
                'departemen' => $l->departemen,
                'tipe_pekerjaan' => $l->tipe_pekerjaan,
                'lokasi' => $l->lokasi,
                'tanggal_mulai' => $l->tanggal_mulai?->toDateString(),
                'tanggal_tutup' => $l->tanggal_tutup?->toDateString(),
            ]);

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

    public function show(string $slug): Response
    {
        $lowongan = Lowongan::query()->active()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Karier/Detail', [
            'lowongan' => $lowongan,
        ]);
    }

    public function apply(string $slug): Response
    {
        $lowongan = Lowongan::query()->active()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Karier/Lamaran', [
            'lowongan' => $lowongan,
        ]);
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $lowongan = Lowongan::query()->active()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'no_hp' => ['required', 'string', 'max:30'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'portfolio' => ['nullable', 'url', 'max:255'],
            'pesan' => ['nullable', 'string', 'max:2000'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:1024'],
            'surat_lamaran' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:1024'],
        ], [
            'cv.required' => 'CV wajib diunggah.',
            'cv.mimes' => 'CV harus berupa PDF, DOC, atau DOCX.',
            'cv.max' => 'Ukuran CV maksimal 1 MB.',
            'surat_lamaran.mimes' => 'Surat lamaran harus berupa PDF, DOC, atau DOCX.',
            'surat_lamaran.max' => 'Ukuran surat lamaran maksimal 1 MB.',
        ]);

        $alreadyApplied = Lamaran::query()
            ->where('lowongan_id', $lowongan->id)
            ->where('email', $validated['email'])
            ->exists();

        if ($alreadyApplied) {
            return back()->withErrors([
                'email' => 'Email ini sudah digunakan untuk melamar posisi tersebut.',
            ]);
        }

        $cvPath = $request->file('cv')->store('lamaran/cv', 'public');
        $suratPath = $request->hasFile('surat_lamaran')
            ? $request->file('surat_lamaran')->store('lamaran/surat', 'public')
            : null;

        try {
            Lamaran::create([
                'lowongan_id' => $lowongan->id,
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'],
                'linkedin' => $validated['linkedin'] ?? null,
                'portfolio' => $validated['portfolio'] ?? null,
                'pesan' => $validated['pesan'] ?? null,
                'cv' => $cvPath,
                'surat_lamaran' => $suratPath,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete(array_filter([$cvPath, $suratPath]));
            report($e);

            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Lamaran gagal dikirim. Silakan coba kembali.',
            ]);
        }

        return back();
    }
}
