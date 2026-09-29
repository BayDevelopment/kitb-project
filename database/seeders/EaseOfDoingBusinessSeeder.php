<?php

namespace Database\Seeders;

use App\Models\EaseOfDoingBusiness;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EaseOfDoingBusinessSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'judul' => 'Perizinan dan Legalitas',
                'ringkasan' => 'Proses perizinan dan legalitas usaha yang jelas dan terintegrasi.',
                'deskripsi' => 'KITB mendukung proses perizinan dan legalitas kegiatan usaha melalui layanan yang terarah, transparan, dan terintegrasi sesuai dengan ketentuan yang berlaku.',
                'ikon' => 'file-check',
                'urutan' => 1,
                'aktif' => true,
            ],
            [
                'judul' => 'Infrastruktur Kawasan',
                'ringkasan' => 'Dukungan infrastruktur kawasan untuk menunjang kegiatan investasi.',
                'deskripsi' => 'Kawasan menyediakan dukungan infrastruktur yang dirancang untuk menunjang kegiatan industri dan investasi, termasuk akses kawasan, utilitas, serta fasilitas pendukung lainnya.',
                'ikon' => 'building-2',
                'urutan' => 2,
                'aktif' => true,
            ],
            [
                'judul' => 'Lokasi Strategis',
                'ringkasan' => 'Lokasi kawasan yang mendukung akses dan konektivitas kegiatan usaha.',
                'deskripsi' => 'Lokasi strategis memberikan kemudahan akses menuju kawasan dan mendukung konektivitas kegiatan usaha dengan berbagai wilayah di sekitarnya.',
                'ikon' => 'map-pinned',
                'urutan' => 3,
                'aktif' => true,
            ],
            [
                'judul' => 'Layanan Investasi',
                'ringkasan' => 'Layanan yang membantu investor dalam menjalankan kegiatan usahanya.',
                'deskripsi' => 'Investor mendapatkan dukungan layanan informasi dan fasilitasi yang membantu proses persiapan maupun pelaksanaan kegiatan investasi di kawasan.',
                'ikon' => 'handshake',
                'urutan' => 4,
                'aktif' => true,
            ],
            [
                'judul' => 'Kepastian Berusaha',
                'ringkasan' => 'Informasi dan proses usaha yang mendukung kepastian bagi investor.',
                'deskripsi' => 'KITB berkomitmen memberikan informasi yang jelas mengenai kawasan, fasilitas, layanan, dan proses yang berkaitan dengan kegiatan investasi.',
                'ikon' => 'shield-check',
                'urutan' => 5,
                'aktif' => true,
            ],
            [
                'judul' => 'Dukungan Pemerintah',
                'ringkasan' => 'Fasilitasi dan dukungan untuk memperlancar kegiatan investasi.',
                'deskripsi' => 'Kegiatan investasi didukung melalui koordinasi dan fasilitasi dengan pihak terkait untuk membantu menciptakan lingkungan usaha yang kondusif.',
                'ikon' => 'landmark',
                'urutan' => 6,
                'aktif' => true,
            ],
        ];

        foreach ($data as $item) {
            EaseOfDoingBusiness::updateOrCreate(
                [
                    'slug' => Str::slug($item['judul']),
                ],
                [
                    'judul' => $item['judul'],
                    'ringkasan' => $item['ringkasan'],
                    'deskripsi' => $item['deskripsi'],
                    'ikon' => $item['ikon'],
                    'urutan' => $item['urutan'],
                    'aktif' => $item['aktif'],
                ]
            );
        }
    }
}
