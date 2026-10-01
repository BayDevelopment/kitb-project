<?php

namespace App\Http\Controllers;

use App\Models\Lowongan;
use Inertia\Inertia;
use Inertia\Response;

class PublicLowonganKerjaController extends Controller
{
    /**
     * Menampilkan daftar lowongan kerja yang sedang dibuka.
     */
    public function index(): Response
    {
        $lowongans = Lowongan::query()
            ->where('status', 'published')
            ->where(function ($query) {
                $query
                    ->whereNull('tanggal_mulai')
                    ->orWhereDate('tanggal_mulai', '<=', now()->toDateString());
            })
            ->where(function ($query) {
                $query
                    ->whereNull('tanggal_tutup')
                    ->orWhereDate('tanggal_tutup', '>=', now()->toDateString());
            })
            ->orderByDesc('unggulan')
            ->orderBy('urutan')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('Public/Karier/Index', [
            'lowongans' => $lowongans,
        ]);
    }

    /**
     * Menampilkan detail lowongan kerja.
     */
    public function show(string $slug): Response
    {
        $lowongan = Lowongan::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where(function ($query) {
                $query
                    ->whereNull('tanggal_mulai')
                    ->orWhereDate('tanggal_mulai', '<=', now()->toDateString());
            })
            ->where(function ($query) {
                $query
                    ->whereNull('tanggal_tutup')
                    ->orWhereDate('tanggal_tutup', '>=', now()->toDateString());
            })
            ->firstOrFail();

        return Inertia::render('Public/Karier/Detail', [
            'lowongan' => $lowongan,
        ]);
    }
}
