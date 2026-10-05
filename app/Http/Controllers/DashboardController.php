<?php

namespace App\Http\Controllers;

use App\Models\KunjunganLahan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $count = static fn(string $table): int => Schema::hasTable($table)
            ? DB::table($table)->count()
            : 0;

        $groups = [
            [
                'title' => 'Profil Perusahaan',
                'items' => [
                    [
                        'key' => 'tentang',
                        'label' => 'Tentang Kami',
                        'count' => $count('company_profiles'),
                        'href' => '/profil-perusahaan/tentang-kami',
                    ],
                    [
                        'key' => 'visi_misi',
                        'label' => 'Visi & Misi',
                        'count' => $count('visis') + $count('misis'),
                        'href' => '/profil-perusahaan/visi-misi',
                    ],
                    [
                        'key' => 'struktur',
                        'label' => 'Struktur Perusahaan',
                        'count' => $count('struktur_perusahaans'),
                        'href' => '/profil-perusahaan/struktur-perusahaan',
                    ],
                    [
                        'key' => 'anak_usaha',
                        'label' => 'Anak Usaha',
                        'count' => $count('anak_usahas'),
                        'href' => '/profil-perusahaan/anak-usaha',
                    ],
                ],
            ],
            [
                'title' => 'Kawasan',
                'items' => [
                    [
                        'key' => 'profil_kawasan',
                        'label' => 'Profil Kawasan',
                        'count' => $count('profil_kawasans'),
                        'href' => '/kawasan/profil-kawasan',
                    ],
                    [
                        'key' => 'infrastruktur',
                        'label' => 'Infrastruktur',
                        'count' => $count('infrastrukturs'),
                        'href' => '/kawasan/infrastruktur',
                    ],
                    [
                        'key' => 'fasilitas',
                        'label' => 'Fasilitas',
                        'count' => $count('fasilitas'),
                        'href' => '/kawasan/fasilitas',
                    ],
                    [
                        'key' => 'peta',
                        'label' => 'Peta Kawasan',
                        'count' => $count('peta_kawasan'),
                        'href' => '/kawasan/peta-kawasan',
                    ],
                ],
            ],
            [
                'title' => 'Hubungan Investor',
                'items' => [
                    [
                        'key' => 'peluang',
                        'label' => 'Peluang Investasi',
                        'count' => $count('peluang_investasis'),
                        'href' => '/hubungan-investor/peluang-investasi',
                    ],
                    [
                        'key' => 'ease',
                        'label' => 'Ease of Doing Business',
                        'count' => $count('ease_of_doing_businesses'),
                        'href' => '/hubungan-investor/ease-of-doing-business',
                    ],
                    [
                        'key' => 'kunjungan',
                        'label' => 'Kunjungan Lahan',
                        'count' => KunjunganLahan::query()->count(),
                        'href' => '/hubungan-investor/kunjungan-lahan',
                    ],
                    [
                        'key' => 'rute',
                        'label' => 'Rute Pelayaran & Lokasi',
                        'count' => $count('rutes'),
                        'href' => '/hubungan-investor/rute-pelayaran-lokasi',
                    ],
                ],
            ],
        ];

        $totalKonten = collect($groups)
            ->flatMap(static fn(array $group) => $group['items'])
            ->sum('count')
            + $count('lahans')
            + $count('pembangunans')
            + $count('master_plans')
            + $count('kawasan_zones');

        $kunjunganTotal = KunjunganLahan::query()->count();

        $kunjunganBulanIni = KunjunganLahan::query()
            ->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->count();

        $kunjunganTerbaru = KunjunganLahan::query()
            ->where('status', KunjunganLahan::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->values()
            ->all();

        $zones = [];

        if (Schema::hasTable('kawasan_zones')) {
            $zones = DB::table('kawasan_zones')
                ->get()
                ->map(static fn($zone): array => [
                    'label' => $zone->label ?? $zone->nama ?? '-',
                    'luas' => (float) ($zone->luas ?? 0),
                    'warna' => $zone->warna ?? '#2563eb',
                ])
                ->values()
                ->all();
        }

        return Inertia::render('Dashboard', [
            'summary' => [
                'kunjungan_total' => $kunjunganTotal,
                'kunjungan_bulan_ini' => $kunjunganBulanIni,
                'total_konten' => $totalKonten,
                'pengguna' => $count('users'),
                'peluang_investasi' => $count('peluang_investasis'),
            ],

            'groups' => $groups,

            'kunjunganTerbaru' => $kunjunganTerbaru,

            'zones' => $zones,
        ]);
    }
}
