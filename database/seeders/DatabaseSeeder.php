<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();

            /*
            |--------------------------------------------------------------------------
            | 1. COMPANY PROFILE
            |--------------------------------------------------------------------------
            */
            DB::table('company_profiles')->updateOrInsert(
                ['id' => 1],
                [
                    'nama_perusahaan' => 'PT Kawasan Industri Tanjung Buton',
                    'tentang_kami' => 'PT Kawasan Industri Tanjung Buton (KITB) merupakan pengelola kawasan industri yang dikembangkan untuk mendukung pertumbuhan industri, investasi, logistik, dan kegiatan ekonomi strategis di Indonesia.',
                    'latar_belakang' => 'Kawasan Industri Tanjung Buton dikembangkan sebagai kawasan industri terpadu dengan dukungan infrastruktur, akses logistik, serta potensi konektivitas menuju jalur perdagangan regional dan nasional.',
                    'moto' => 'Membangun Kawasan Industri Berkelanjutan',
                    'alamat' => 'Kawasan Industri Tanjung Buton, Kabupaten Siak, Provinsi Riau, Indonesia',
                    'email' => 'info@tanjungbuton-industrial.co.id',
                    'telepon' => '+62 761 123456',
                    'website' => 'https://tanjungbuton-industrial.co.id',
                    'logo' => null,
                    'aktif' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 2. VISI
            |--------------------------------------------------------------------------
            */
            $visiId = DB::table('visis')->updateOrInsert(
                ['id' => 1],
                [
                    'isi' => 'Menjadi kawasan industri terpadu yang unggul, berkelanjutan, kompetitif, dan memberikan nilai tambah bagi industri, masyarakat, serta perekonomian nasional.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $visiId = DB::table('visis')
                ->where('id', 1)
                ->value('id');

            /*
            |--------------------------------------------------------------------------
            | 3. MISI
            |--------------------------------------------------------------------------
            */
            $misis = [
                [
                    'visi_id' => $visiId,
                    'isi' => 'Mengembangkan kawasan industri dengan infrastruktur yang andal dan terintegrasi.',
                    'urutan' => 1,
                ],
                [
                    'visi_id' => $visiId,
                    'isi' => 'Mendorong terciptanya iklim investasi yang aman, mudah, dan kompetitif.',
                    'urutan' => 2,
                ],
                [
                    'visi_id' => $visiId,
                    'isi' => 'Memberikan pelayanan profesional kepada investor dan pelaku industri.',
                    'urutan' => 3,
                ],
                [
                    'visi_id' => $visiId,
                    'isi' => 'Mendukung pertumbuhan ekonomi daerah dan menciptakan lapangan kerja berkelanjutan.',
                    'urutan' => 4,
                ],
                [
                    'visi_id' => $visiId,
                    'isi' => 'Menerapkan prinsip pembangunan kawasan industri yang berwawasan lingkungan.',
                    'urutan' => 5,
                ],
            ];

            foreach ($misis as $misi) {
                DB::table('misis')->updateOrInsert(
                    [
                        'visi_id' => $misi['visi_id'],
                        'urutan' => $misi['urutan'],
                    ],
                    [
                        'isi' => $misi['isi'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 4. STRUKTUR PERUSAHAAN
            |--------------------------------------------------------------------------
            */
            $struktur = [
                [
                    'nama' => 'Budi Santoso',
                    'jabatan' => 'Direktur Utama',
                    'gambar' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Andi Pratama',
                    'jabatan' => 'Direktur Operasional',
                    'gambar' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Rina Kurniawati',
                    'jabatan' => 'Direktur Keuangan',
                    'gambar' => null,
                    'urutan' => 3,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Dedi Firmansyah',
                    'jabatan' => 'Manajer Pengembangan Kawasan',
                    'gambar' => null,
                    'urutan' => 4,
                    'aktif' => true,
                ],
            ];

            foreach ($struktur as $item) {
                DB::table('struktur_perusahaans')->updateOrInsert(
                    [
                        'nama' => $item['nama'],
                        'jabatan' => $item['jabatan'],
                    ],
                    [
                        'gambar' => $item['gambar'],
                        'urutan' => $item['urutan'],
                        'aktif' => $item['aktif'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 5. ANAK USAHA
            |--------------------------------------------------------------------------
            */
            $anakUsaha = [
                [
                    'nama' => 'PT Tanjung Buton Logistik',
                    'logo' => null,
                    'deskripsi' => 'Perusahaan yang bergerak dalam pengembangan layanan logistik dan mendukung kebutuhan rantai pasok kawasan industri.',
                    'website' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],
                [
                    'nama' => 'PT Tanjung Buton Infrastruktur',
                    'logo' => null,
                    'deskripsi' => 'Perusahaan yang mendukung pembangunan dan pengelolaan infrastruktur kawasan industri.',
                    'website' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],
                [
                    'nama' => 'PT Tanjung Buton Properti',
                    'logo' => null,
                    'deskripsi' => 'Perusahaan yang bergerak dalam pengembangan aset dan properti pendukung kawasan industri.',
                    'website' => null,
                    'urutan' => 3,
                    'aktif' => true,
                ],
            ];

            foreach ($anakUsaha as $item) {
                DB::table('anak_usahas')->updateOrInsert(
                    ['nama' => $item['nama']],
                    [
                        'logo' => $item['logo'],
                        'deskripsi' => $item['deskripsi'],
                        'website' => $item['website'],
                        'urutan' => $item['urutan'],
                        'aktif' => $item['aktif'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 6. PROFIL KAWASAN
            |--------------------------------------------------------------------------
            */
            DB::table('profil_kawasans')->updateOrInsert(
                ['slug' => 'profil-kawasan-industri-tanjung-buton'],
                [
                    'judul' => 'Kawasan Industri Tanjung Buton',
                    'deskripsi' => 'Kawasan industri terpadu yang dirancang untuk mendukung kegiatan manufaktur, logistik, energi, dan industri strategis dengan dukungan konektivitas serta infrastruktur kawasan.',
                    'luas_kawasan' => 5000.00,
                    'lokasi' => 'Kabupaten Siak, Provinsi Riau',
                    'latitude' => 0.9321000,
                    'longitude' => 102.1425000,
                    'batas_kawasan' => json_encode([
                        'type' => 'Feature',
                        'properties' => [],
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [
                                [
                                    [102.1300000, 0.9200000],
                                    [102.1600000, 0.9200000],
                                    [102.1600000, 0.9500000],
                                    [102.1300000, 0.9500000],
                                    [102.1300000, 0.9200000],
                                ],
                            ],
                        ],
                    ]),
                    'tahun_berdiri' => 2012,
                    'status' => true,
                    'gambar' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 7. LAHAN
            |--------------------------------------------------------------------------
            */
            $lahan = [
                [
                    'judul' => 'Total Luas Kawasan',
                    'nilai' => 5000.0,
                    'satuan' => 'Ha',
                    'deskripsi' => 'Total area pengembangan Kawasan Industri Tanjung Buton.',
                    'urutan' => 1,
                ],
                [
                    'judul' => 'Lahan Industri',
                    'nilai' => 3000.0,
                    'satuan' => 'Ha',
                    'deskripsi' => 'Area yang diperuntukkan bagi kegiatan industri.',
                    'urutan' => 2,
                ],
                [
                    'judul' => 'Area Pendukung',
                    'nilai' => 1000.0,
                    'satuan' => 'Ha',
                    'deskripsi' => 'Area untuk fasilitas pendukung kawasan dan utilitas.',
                    'urutan' => 3,
                ],
                [
                    'judul' => 'Area Hijau',
                    'nilai' => 1000.0,
                    'satuan' => 'Ha',
                    'deskripsi' => 'Area ruang terbuka hijau dan kawasan penyangga.',
                    'urutan' => 4,
                ],
            ];

            foreach ($lahan as $item) {
                DB::table('lahans')->updateOrInsert(
                    ['judul' => $item['judul']],
                    [
                        'nilai' => $item['nilai'],
                        'satuan' => $item['satuan'],
                        'deskripsi' => $item['deskripsi'],
                        'urutan' => $item['urutan'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 8. PEMBANGUNAN
            |--------------------------------------------------------------------------
            */
            $pembangunan = [
                [
                    'tahap' => 'Tahap 1',
                    'luas' => 1000.00,
                    'satuan_luas' => 'Ha',
                    'judul' => 'Pengembangan Kawasan Inti',
                    'deskripsi' => 'Pengembangan tahap awal kawasan industri beserta infrastruktur dasar dan utilitas utama.',
                    'gambar' => null,
                    'urutan' => 1,
                ],
                [
                    'tahap' => 'Tahap 2',
                    'luas' => 1500.00,
                    'satuan_luas' => 'Ha',
                    'judul' => 'Ekspansi Area Industri',
                    'deskripsi' => 'Perluasan area industri dan pengembangan fasilitas pendukung untuk kebutuhan investor.',
                    'gambar' => null,
                    'urutan' => 2,
                ],
                [
                    'tahap' => 'Tahap 3',
                    'luas' => 2500.00,
                    'satuan_luas' => 'Ha',
                    'judul' => 'Pengembangan Terpadu',
                    'deskripsi' => 'Pengembangan lanjutan kawasan dengan integrasi industri, logistik, dan fasilitas pendukung.',
                    'gambar' => null,
                    'urutan' => 3,
                ],
            ];

            foreach ($pembangunan as $item) {
                DB::table('pembangunans')->updateOrInsert(
                    [
                        'tahap' => $item['tahap'],
                        'judul' => $item['judul'],
                    ],
                    [
                        'luas' => $item['luas'],
                        'satuan_luas' => $item['satuan_luas'],
                        'deskripsi' => $item['deskripsi'],
                        'gambar' => $item['gambar'],
                        'urutan' => $item['urutan'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 9. INFRASTRUKTUR
            |--------------------------------------------------------------------------
            */
            $infrastruktur = [
                [
                    'nama' => 'Jaringan Jalan Kawasan',
                    'slug' => 'jaringan-jalan-kawasan',
                    'deskripsi' => 'Jaringan jalan kawasan yang mendukung mobilitas kendaraan industri dan distribusi logistik.',
                    'gambar' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Pelabuhan dan Terminal Logistik',
                    'slug' => 'pelabuhan-terminal-logistik',
                    'deskripsi' => 'Infrastruktur pendukung kegiatan bongkar muat dan distribusi barang melalui jalur laut.',
                    'gambar' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Jaringan Listrik',
                    'slug' => 'jaringan-listrik',
                    'deskripsi' => 'Sistem kelistrikan untuk memenuhi kebutuhan operasional kawasan dan tenant.',
                    'gambar' => null,
                    'urutan' => 3,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Sistem Air Bersih',
                    'slug' => 'sistem-air-bersih',
                    'deskripsi' => 'Infrastruktur penyediaan air bersih untuk kebutuhan kawasan industri.',
                    'gambar' => null,
                    'urutan' => 4,
                    'aktif' => true,
                ],
            ];

            foreach ($infrastruktur as $item) {
                DB::table('infrastrukturs')->updateOrInsert(
                    ['slug' => $item['slug']],
                    [
                        'nama' => $item['nama'],
                        'deskripsi' => $item['deskripsi'],
                        'gambar' => $item['gambar'],
                        'urutan' => $item['urutan'],
                        'aktif' => $item['aktif'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 10. FASILITAS
            |--------------------------------------------------------------------------
            */
            $fasilitas = [
                [
                    'nama' => 'Gedung Pengelola Kawasan',
                    'slug' => 'gedung-pengelola-kawasan',
                    'deskripsi' => 'Pusat administrasi dan pelayanan pengelolaan kawasan industri.',
                    'gambar' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Area Komersial',
                    'slug' => 'area-komersial',
                    'deskripsi' => 'Area komersial yang mendukung kebutuhan tenant dan pekerja kawasan.',
                    'gambar' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Fasilitas Kesehatan',
                    'slug' => 'fasilitas-kesehatan',
                    'deskripsi' => 'Fasilitas pelayanan kesehatan untuk mendukung aktivitas kawasan.',
                    'gambar' => null,
                    'urutan' => 3,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Fasilitas Keamanan',
                    'slug' => 'fasilitas-keamanan',
                    'deskripsi' => 'Fasilitas keamanan dan pengawasan kawasan selama 24 jam.',
                    'gambar' => null,
                    'urutan' => 4,
                    'aktif' => true,
                ],
            ];

            foreach ($fasilitas as $item) {
                DB::table('fasilitas')->updateOrInsert(
                    ['slug' => $item['slug']],
                    [
                        'nama' => $item['nama'],
                        'deskripsi' => $item['deskripsi'],
                        'gambar' => $item['gambar'],
                        'urutan' => $item['urutan'],
                        'aktif' => $item['aktif'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 11. PETA KAWASAN
            |--------------------------------------------------------------------------
            */
            $petaKawasan = [
                [
                    'nama' => 'Peta Kawasan Industri Tanjung Buton',
                    'slug' => 'peta-kawasan-industri-tanjung-buton',
                    'deskripsi' => 'Peta umum kawasan industri beserta area pengembangan dan fasilitas pendukung.',
                    'gambar' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Peta Infrastruktur Kawasan',
                    'slug' => 'peta-infrastruktur-kawasan',
                    'deskripsi' => 'Peta jaringan infrastruktur utama yang tersedia di dalam kawasan.',
                    'gambar' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],
            ];

            foreach ($petaKawasan as $item) {
                DB::table('peta_kawasan')->updateOrInsert(
                    ['slug' => $item['slug']],
                    [
                        'nama' => $item['nama'],
                        'deskripsi' => $item['deskripsi'],
                        'gambar' => $item['gambar'],
                        'urutan' => $item['urutan'],
                        'aktif' => $item['aktif'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 12. RUTE
            |--------------------------------------------------------------------------
            */
            $rutes = [
                [
                    'nama_rute' => 'Rute Tanjung Buton - Pelabuhan',
                    'jalur' => 'Kawasan Industri Tanjung Buton → Pelabuhan Tanjung Buton',
                    'jarak' => 8.50,
                    'satuan_jarak' => 'km',
                    'waktu_tempuh' => '15 - 20 menit',
                    'deskripsi' => 'Rute utama menuju fasilitas pelabuhan untuk mendukung kegiatan logistik kawasan.',
                    'asal' => 'Kawasan Industri Tanjung Buton',
                    'tujuan' => 'Pelabuhan Tanjung Buton',
                    'latitude' => 0.9321000,
                    'longitude' => 102.1425000,
                    'geometry' => null,
                    'gambar' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],
                [
                    'nama_rute' => 'Rute Kawasan - Pusat Kabupaten',
                    'jalur' => 'Kawasan Industri Tanjung Buton → Pusat Kabupaten',
                    'jarak' => 35.00,
                    'satuan_jarak' => 'km',
                    'waktu_tempuh' => '45 - 60 menit',
                    'deskripsi' => 'Rute penghubung kawasan menuju pusat pemerintahan dan layanan kabupaten.',
                    'asal' => 'Kawasan Industri Tanjung Buton',
                    'tujuan' => 'Pusat Kabupaten',
                    'latitude' => 0.9321000,
                    'longitude' => 102.1425000,
                    'geometry' => null,
                    'gambar' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],
                [
                    'nama_rute' => 'Rute Kawasan - Kota Pekanbaru',
                    'jalur' => 'Kawasan Industri Tanjung Buton → Pekanbaru',
                    'jarak' => 180.00,
                    'satuan_jarak' => 'km',
                    'waktu_tempuh' => '4 - 5 jam',
                    'deskripsi' => 'Rute darat menuju pusat ekonomi Provinsi Riau.',
                    'asal' => 'Kawasan Industri Tanjung Buton',
                    'tujuan' => 'Pekanbaru',
                    'latitude' => 0.9321000,
                    'longitude' => 102.1425000,
                    'geometry' => null,
                    'gambar' => null,
                    'urutan' => 3,
                    'aktif' => true,
                ],
            ];

            foreach ($rutes as $item) {
                DB::table('rutes')->updateOrInsert(
                    ['nama_rute' => $item['nama_rute']],
                    [
                        'jalur' => $item['jalur'],
                        'jarak' => $item['jarak'],
                        'satuan_jarak' => $item['satuan_jarak'],
                        'waktu_tempuh' => $item['waktu_tempuh'],
                        'deskripsi' => $item['deskripsi'],
                        'asal' => $item['asal'],
                        'tujuan' => $item['tujuan'],
                        'latitude' => $item['latitude'],
                        'longitude' => $item['longitude'],
                        'geometry' => $item['geometry'],
                        'gambar' => $item['gambar'],
                        'urutan' => $item['urutan'],
                        'aktif' => $item['aktif'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 13. PELUANG INVESTASI
            |--------------------------------------------------------------------------
            */
            $investasi = [
                [
                    'judul' => 'Kawasan Industri Manufaktur',
                    'slug' => 'kawasan-industri-manufaktur',
                    'sektor_industri' => 'Manufaktur',
                    'deskripsi' => 'Peluang investasi untuk pengembangan industri manufaktur dan industri pendukung.',
                    'luas_lahan' => 500.00,
                    'satuan_luas' => 'Ha',
                    'lokasi' => 'Zona Industri Utama',
                    'status' => 'tersedia',
                    'nilai_investasi' => 2500000000000.00,
                    'mata_uang' => 'IDR',
                    'gambar' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],
                [
                    'judul' => 'Kawasan Logistik dan Pergudangan',
                    'slug' => 'kawasan-logistik-pergudangan',
                    'sektor_industri' => 'Logistik',
                    'deskripsi' => 'Area investasi untuk pergudangan, distribusi, dan kegiatan logistik terpadu.',
                    'luas_lahan' => 250.00,
                    'satuan_luas' => 'Ha',
                    'lokasi' => 'Zona Logistik',
                    'status' => 'tersedia',
                    'nilai_investasi' => 1250000000000.00,
                    'mata_uang' => 'IDR',
                    'gambar' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],
                [
                    'judul' => 'Industri Hilirisasi',
                    'slug' => 'industri-hilirisasi',
                    'sektor_industri' => 'Hilirisasi',
                    'deskripsi' => 'Peluang investasi bagi industri pengolahan dan hilirisasi komoditas strategis.',
                    'luas_lahan' => 750.00,
                    'satuan_luas' => 'Ha',
                    'lokasi' => 'Zona Industri Pengembangan',
                    'status' => 'tersedia',
                    'nilai_investasi' => 5000000000000.00,
                    'mata_uang' => 'IDR',
                    'gambar' => null,
                    'urutan' => 3,
                    'aktif' => true,
                ],
            ];

            foreach ($investasi as $item) {
                DB::table('peluang_investasis')->updateOrInsert(
                    ['slug' => $item['slug']],
                    [
                        'judul' => $item['judul'],
                        'sektor_industri' => $item['sektor_industri'],
                        'deskripsi' => $item['deskripsi'],
                        'luas_lahan' => $item['luas_lahan'],
                        'satuan_luas' => $item['satuan_luas'],
                        'lokasi' => $item['lokasi'],
                        'status' => $item['status'],
                        'nilai_investasi' => $item['nilai_investasi'],
                        'mata_uang' => $item['mata_uang'],
                        'gambar' => $item['gambar'],
                        'urutan' => $item['urutan'],
                        'aktif' => $item['aktif'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 14. EASE OF DOING BUSINESS
            |--------------------------------------------------------------------------
            */
            $ease = [
                [
                    'judul' => 'Perizinan Terintegrasi',
                    'slug' => 'perizinan-terintegrasi',
                    'ringkasan' => 'Proses perizinan yang mudah dan terkoordinasi.',
                    'deskripsi' => 'Investor mendapatkan dukungan informasi dan pendampingan dalam proses perizinan usaha.',
                    'ikon' => 'FileCheck2',
                    'urutan' => 1,
                    'aktif' => true,
                ],
                [
                    'judul' => 'Infrastruktur Terpadu',
                    'slug' => 'infrastruktur-terpadu',
                    'ringkasan' => 'Infrastruktur kawasan yang mendukung kegiatan industri.',
                    'deskripsi' => 'Kawasan dilengkapi dengan infrastruktur dasar dan fasilitas pendukung untuk kebutuhan tenant.',
                    'ikon' => 'Factory',
                    'urutan' => 2,
                    'aktif' => true,
                ],
                [
                    'judul' => 'Dukungan Investasi',
                    'slug' => 'dukungan-investasi',
                    'ringkasan' => 'Pendampingan bagi calon investor dan tenant.',
                    'deskripsi' => 'Tim pengelola kawasan membantu investor memahami potensi, fasilitas, serta proses pengembangan usaha.',
                    'ikon' => 'Handshake',
                    'urutan' => 3,
                    'aktif' => true,
                ],
                [
                    'judul' => 'Konektivitas Logistik',
                    'slug' => 'konektivitas-logistik',
                    'ringkasan' => 'Akses menuju jaringan transportasi dan pelabuhan.',
                    'deskripsi' => 'Konektivitas kawasan mendukung pergerakan bahan baku dan produk menuju pasar.',
                    'ikon' => 'Truck',
                    'urutan' => 4,
                    'aktif' => true,
                ],
            ];

            foreach ($ease as $item) {
                DB::table('ease_of_doing_businesses')->updateOrInsert(
                    ['slug' => $item['slug']],
                    [
                        'judul' => $item['judul'],
                        'ringkasan' => $item['ringkasan'],
                        'deskripsi' => $item['deskripsi'],
                        'ikon' => $item['ikon'],
                        'urutan' => $item['urutan'],
                        'aktif' => $item['aktif'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 15. BERITA
            |--------------------------------------------------------------------------
            */
            $berita = [
                [
                    'judul' => 'KITB Dorong Pengembangan Kawasan Industri Berkelanjutan',
                    'slug' => 'kitb-dorong-pengembangan-kawasan-industri-berkelanjutan',
                    'excerpt' => 'Pengembangan kawasan industri diarahkan untuk menciptakan ekosistem industri yang kompetitif dan berkelanjutan.',
                    'konten' => '<p>PT Kawasan Industri Tanjung Buton terus mendorong pengembangan kawasan industri yang terintegrasi, kompetitif, dan berkelanjutan.</p><p>Pengembangan kawasan dilakukan dengan memperhatikan kebutuhan industri, infrastruktur, lingkungan, dan masyarakat sekitar.</p>',
                    'gambar' => null,
                    'kategori' => 'Kawasan',
                    'penulis' => 'Admin KITB',
                    'status' => 'published',
                    'published_at' => now()->subDays(5),
                    'is_featured' => true,
                    'views' => 125,
                ],
                [
                    'judul' => 'Peluang Investasi Baru di Kawasan Industri Tanjung Buton',
                    'slug' => 'peluang-investasi-baru-di-kawasan-industri-tanjung-buton',
                    'excerpt' => 'KITB membuka peluang investasi bagi berbagai sektor industri strategis.',
                    'konten' => '<p>Kawasan Industri Tanjung Buton menyediakan berbagai peluang investasi untuk sektor manufaktur, logistik, hilirisasi, dan industri pendukung.</p>',
                    'gambar' => null,
                    'kategori' => 'Investasi',
                    'penulis' => 'Admin KITB',
                    'status' => 'published',
                    'published_at' => now()->subDays(10),
                    'is_featured' => false,
                    'views' => 86,
                ],
                [
                    'judul' => 'Penguatan Infrastruktur Pendukung Kawasan',
                    'slug' => 'penguatan-infrastruktur-pendukung-kawasan',
                    'excerpt' => 'Pengembangan infrastruktur menjadi bagian penting dalam meningkatkan daya saing kawasan.',
                    'konten' => '<p>Penguatan infrastruktur kawasan menjadi salah satu fokus utama dalam mendukung kebutuhan investor dan tenant.</p>',
                    'gambar' => null,
                    'kategori' => 'Infrastruktur',
                    'penulis' => 'Admin KITB',
                    'status' => 'published',
                    'published_at' => now()->subDays(15),
                    'is_featured' => false,
                    'views' => 64,
                ],
            ];

            foreach ($berita as $item) {
                DB::table('beritas')->updateOrInsert(
                    ['slug' => $item['slug']],
                    [
                        'judul' => $item['judul'],
                        'excerpt' => $item['excerpt'],
                        'konten' => $item['konten'],
                        'gambar' => $item['gambar'],
                        'kategori' => $item['kategori'],
                        'penulis' => $item['penulis'],
                        'status' => $item['status'],
                        'published_at' => $item['published_at'],
                        'is_featured' => $item['is_featured'],
                        'views' => $item['views'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 16. LOWONGAN
            |--------------------------------------------------------------------------
            */
            $lowongan = [
                [
                    'judul' => 'Staff Administrasi',
                    'slug' => 'staff-administrasi',
                    'departemen' => 'Administrasi',
                    'lokasi' => 'Kawasan Industri Tanjung Buton',
                    'tipe_pekerjaan' => 'Full Time',
                    'deskripsi' => '<p>Bertanggung jawab terhadap kegiatan administrasi dan dokumentasi perusahaan.</p>',
                    'tanggung_jawab' => '<ul><li>Mengelola dokumen administrasi.</li><li>Menyusun laporan rutin.</li><li>Mendukung kegiatan operasional departemen.</li></ul>',
                    'kualifikasi' => '<ul><li>Minimal D3/S1.</li><li>Mampu menggunakan Microsoft Office.</li><li>Memiliki kemampuan komunikasi yang baik.</li></ul>',
                    'benefit' => '<ul><li>Lingkungan kerja profesional.</li><li>Pengembangan kompetensi.</li><li>Jenjang karier.</li></ul>',
                    'tanggal_mulai' => now()->toDateString(),
                    'tanggal_tutup' => now()->addDays(30)->toDateString(),
                    'status' => 'published',
                    'unggulan' => true,
                    'urutan' => 1,
                ],
                [
                    'judul' => 'Staff IT Support',
                    'slug' => 'staff-it-support',
                    'departemen' => 'Teknologi Informasi',
                    'lokasi' => 'Kawasan Industri Tanjung Buton',
                    'tipe_pekerjaan' => 'Full Time',
                    'deskripsi' => '<p>Mendukung operasional teknologi informasi dan sistem digital perusahaan.</p>',
                    'tanggung_jawab' => '<ul><li>Melakukan troubleshooting perangkat.</li><li>Mendukung sistem jaringan.</li><li>Mendukung pengguna internal.</li></ul>',
                    'kualifikasi' => '<ul><li>Minimal D3 Teknik Informatika/Sistem Informasi.</li><li>Memahami jaringan komputer.</li><li>Mampu melakukan troubleshooting.</li></ul>',
                    'benefit' => '<ul><li>Lingkungan kerja profesional.</li><li>Pengembangan kompetensi.</li><li>Jenjang karier.</li></ul>',
                    'tanggal_mulai' => now()->toDateString(),
                    'tanggal_tutup' => now()->addDays(45)->toDateString(),
                    'status' => 'published',
                    'unggulan' => false,
                    'urutan' => 2,
                ],
                [
                    'judul' => 'Staff Marketing & Business Development',
                    'slug' => 'staff-marketing-business-development',
                    'departemen' => 'Marketing',
                    'lokasi' => 'Kawasan Industri Tanjung Buton',
                    'tipe_pekerjaan' => 'Full Time',
                    'deskripsi' => '<p>Mendukung kegiatan pemasaran kawasan dan pengembangan hubungan dengan calon investor.</p>',
                    'tanggung_jawab' => '<ul><li>Mengelola komunikasi dengan calon investor.</li><li>Menyusun materi pemasaran.</li><li>Melakukan analisis peluang bisnis.</li></ul>',
                    'kualifikasi' => '<ul><li>Minimal S1.</li><li>Komunikatif dan memiliki kemampuan negosiasi.</li><li>Memahami konsep pemasaran dan bisnis.</li></ul>',
                    'benefit' => '<ul><li>Insentif kinerja.</li><li>Pengembangan kompetensi.</li><li>Jenjang karier.</li></ul>',
                    'tanggal_mulai' => now()->toDateString(),
                    'tanggal_tutup' => now()->addDays(30)->toDateString(),
                    'status' => 'published',
                    'unggulan' => true,
                    'urutan' => 3,
                ],
            ];

            foreach ($lowongan as $item) {
                DB::table('lowongans')->updateOrInsert(
                    ['slug' => $item['slug']],
                    [
                        'judul' => $item['judul'],
                        'departemen' => $item['departemen'],
                        'lokasi' => $item['lokasi'],
                        'tipe_pekerjaan' => $item['tipe_pekerjaan'],
                        'deskripsi' => $item['deskripsi'],
                        'tanggung_jawab' => $item['tanggung_jawab'],
                        'kualifikasi' => $item['kualifikasi'],
                        'benefit' => $item['benefit'],
                        'tanggal_mulai' => $item['tanggal_mulai'],
                        'tanggal_tutup' => $item['tanggal_tutup'],
                        'status' => $item['status'],
                        'unggulan' => $item['unggulan'],
                        'urutan' => $item['urutan'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 17. MITRA PERUSAHAAN
            |--------------------------------------------------------------------------
            */
            $mitra = [
                [
                    'nama_perusahaan' => 'PT Pelabuhan Indonesia',
                    'slug' => 'pt-pelabuhan-indonesia',
                    'logo' => null,
                    'website' => null,
                    'aktif' => true,
                    'urutan' => 1,
                ],
                [
                    'nama_perusahaan' => 'PT PLN (Persero)',
                    'slug' => 'pt-pln-persero',
                    'logo' => null,
                    'website' => 'https://www.pln.co.id',
                    'aktif' => true,
                    'urutan' => 2,
                ],
                [
                    'nama_perusahaan' => 'PT Pertamina',
                    'slug' => 'pt-pertamina',
                    'logo' => null,
                    'website' => 'https://www.pertamina.com',
                    'aktif' => true,
                    'urutan' => 3,
                ],
                [
                    'nama_perusahaan' => 'PT Telekomunikasi Indonesia',
                    'slug' => 'pt-telekomunikasi-indonesia',
                    'logo' => null,
                    'website' => 'https://www.telkom.co.id',
                    'aktif' => true,
                    'urutan' => 4,
                ],
            ];

            foreach ($mitra as $item) {
                DB::table('mitra_perusahaans')->updateOrInsert(
                    ['slug' => $item['slug']],
                    [
                        'nama_perusahaan' => $item['nama_perusahaan'],
                        'logo' => $item['logo'],
                        'website' => $item['website'],
                        'aktif' => $item['aktif'],
                        'urutan' => $item['urutan'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 18. PENGATURAN KONTAK
            |--------------------------------------------------------------------------
            */
            DB::table('pengaturan_kontaks')->updateOrInsert(
                ['id' => 1],
                [
                    'nama_perusahaan' => 'PT Kawasan Industri Tanjung Buton',
                    'alamat' => 'Kawasan Industri Tanjung Buton, Kabupaten Siak, Provinsi Riau, Indonesia',
                    'telepon' => '+62 761 123456',
                    'whatsapp' => '+62 812 3456 7890',
                    'email' => 'info@tanjungbuton-industrial.co.id',
                    'email_investor' => 'investor@tanjungbuton-industrial.co.id',
                    'jam_operasional' => 'Senin - Jumat, 08.00 - 16.00 WIB',
                    'latitude' => 0.9321000,
                    'longitude' => 102.1425000,
                    'maps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d10000!2d102.1425!3d0.9321',
                    'facebook' => 'https://www.facebook.com/',
                    'instagram' => 'https://www.instagram.com/',
                    'linkedin' => 'https://www.linkedin.com/',
                    'youtube' => 'https://www.youtube.com/',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 19. KUNJUNGAN LAHAN
            |--------------------------------------------------------------------------
            */
            $kunjungan = [
                [
                    'nomor_registrasi' => 'KITB-KL-2026-0001',
                    'nama' => 'Ahmad Fauzan',
                    'instansi' => 'PT Nusantara Industri',
                    'jabatan' => 'Business Development Manager',
                    'email' => 'ahmad.fauzan@example.com',
                    'telepon' => '081234567890',
                    'tanggal_kunjungan' => now()->addDays(7)->toDateString(),
                    'waktu_mulai' => '09:00:00',
                    'waktu_selesai' => '11:00:00',
                    'jumlah_peserta' => 5,
                    'area_lahan' => 'Zona Industri Utama',
                    'keperluan' => 'Survey lokasi dan pembahasan peluang investasi.',
                    'memerlukan_pendamping' => true,
                    'status' => 'pending',
                    'catatan_admin' => null,
                    'disetujui_at' => null,
                    'ditolak_at' => null,
                    'selesai_at' => null,
                ],
                [
                    'nomor_registrasi' => 'KITB-KL-2026-0002',
                    'nama' => 'Siti Rahma',
                    'instansi' => 'PT Maju Bersama',
                    'jabatan' => 'Direktur',
                    'email' => 'siti.rahma@example.com',
                    'telepon' => '081298765432',
                    'tanggal_kunjungan' => now()->addDays(10)->toDateString(),
                    'waktu_mulai' => '13:00:00',
                    'waktu_selesai' => '15:00:00',
                    'jumlah_peserta' => 8,
                    'area_lahan' => 'Zona Logistik',
                    'keperluan' => 'Kunjungan calon investor dan peninjauan lahan.',
                    'memerlukan_pendamping' => true,
                    'status' => 'disetujui',
                    'catatan_admin' => 'Kunjungan telah disetujui. Mohon hadir 15 menit sebelum jadwal.',
                    'disetujui_at' => now(),
                    'ditolak_at' => null,
                    'selesai_at' => null,
                ],
                [
                    'nomor_registrasi' => 'KITB-KL-2026-0003',
                    'nama' => 'Rizky Maulana',
                    'instansi' => 'Universitas Riau',
                    'jabatan' => 'Dosen',
                    'email' => 'rizky.maulana@example.com',
                    'telepon' => '081211223344',
                    'tanggal_kunjungan' => now()->subDays(10)->toDateString(),
                    'waktu_mulai' => '10:00:00',
                    'waktu_selesai' => '12:00:00',
                    'jumlah_peserta' => 20,
                    'area_lahan' => 'Kawasan Industri',
                    'keperluan' => 'Kunjungan akademik dan observasi kawasan industri.',
                    'memerlukan_pendamping' => true,
                    'status' => 'selesai',
                    'catatan_admin' => 'Kunjungan telah selesai dilaksanakan.',
                    'disetujui_at' => now()->subDays(15),
                    'ditolak_at' => null,
                    'selesai_at' => now()->subDays(9),
                ],
            ];

            foreach ($kunjungan as $item) {
                DB::table('kunjungan_lahan')->updateOrInsert(
                    ['nomor_registrasi' => $item['nomor_registrasi']],
                    [
                        'nama' => $item['nama'],
                        'instansi' => $item['instansi'],
                        'jabatan' => $item['jabatan'],
                        'email' => $item['email'],
                        'telepon' => $item['telepon'],
                        'tanggal_kunjungan' => $item['tanggal_kunjungan'],
                        'waktu_mulai' => $item['waktu_mulai'],
                        'waktu_selesai' => $item['waktu_selesai'],
                        'jumlah_peserta' => $item['jumlah_peserta'],
                        'area_lahan' => $item['area_lahan'],
                        'keperluan' => $item['keperluan'],
                        'memerlukan_pendamping' => $item['memerlukan_pendamping'],
                        'status' => $item['status'],
                        'catatan_admin' => $item['catatan_admin'],
                        'disetujui_at' => $item['disetujui_at'],
                        'ditolak_at' => $item['ditolak_at'],
                        'selesai_at' => $item['selesai_at'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 20. PESAN KONTAK
            |--------------------------------------------------------------------------
            */
            $pesanKontak = [
                [
                    'nama' => 'Fajar Nugraha',
                    'email' => 'fajar.nugraha@example.com',
                    'telepon' => '081234567890',
                    'perusahaan' => 'PT Nusantara Investama',
                    'subjek' => 'Pertanyaan Peluang Investasi',
                    'pesan' => 'Saya ingin mendapatkan informasi mengenai ketersediaan lahan untuk industri manufaktur.',
                    'balasan' => null,
                    'status' => 'baru',
                    'ip_address' => null,
                    'dibaca_at' => null,
                    'dibalas_at' => null,
                ],
                [
                    'nama' => 'Dewi Lestari',
                    'email' => 'dewi.lestari@example.com',
                    'telepon' => '081298765432',
                    'perusahaan' => 'PT Maju Industri',
                    'subjek' => 'Permintaan Profil Kawasan',
                    'pesan' => 'Mohon informasi mengenai profil dan fasilitas yang tersedia di kawasan.',
                    'balasan' => 'Terima kasih atas ketertarikannya. Tim kami akan menghubungi Anda untuk memberikan informasi lebih lanjut.',
                    'status' => 'dibalas',
                    'ip_address' => null,
                    'dibaca_at' => now()->subDays(2),
                    'dibalas_at' => now()->subDay(),
                ],
                [
                    'nama' => 'Hendra Wijaya',
                    'email' => 'hendra.wijaya@example.com',
                    'telepon' => '081211223344',
                    'perusahaan' => null,
                    'subjek' => 'Kunjungan Kawasan',
                    'pesan' => 'Apakah tersedia jadwal kunjungan kawasan untuk calon investor?',
                    'balasan' => null,
                    'status' => 'dibaca',
                    'ip_address' => null,
                    'dibaca_at' => now()->subDays(3),
                    'dibalas_at' => null,
                ],
            ];

            foreach ($pesanKontak as $item) {
                DB::table('pesan_kontaks')->updateOrInsert(
                    [
                        'email' => $item['email'],
                        'subjek' => $item['subjek'],
                    ],
                    [
                        'nama' => $item['nama'],
                        'telepon' => $item['telepon'],
                        'perusahaan' => $item['perusahaan'],
                        'pesan' => $item['pesan'],
                        'balasan' => $item['balasan'],
                        'status' => $item['status'],
                        'ip_address' => $item['ip_address'],
                        'dibaca_at' => $item['dibaca_at'],
                        'dibalas_at' => $item['dibalas_at'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 21. MITRA PERUSAHAAN
            |--------------------------------------------------------------------------
            | Sudah diseed di atas.
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | 22. MASTER PLAN
            |--------------------------------------------------------------------------
            | DILEWATI.
            |
            | Alasannya:
            | master_plans.gambar_path adalah REQUIRED / NOT NULL.
            | Tidak ada file gambar yang bisa dimasukkan secara aman.
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | 23. KAWASAN ZONES
            |--------------------------------------------------------------------------
            | DILEWATI karena master_plans tidak diseed.
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | 24. GALERI
            |--------------------------------------------------------------------------
            | DILEWATI.
            |
            | galeris.gambar adalah REQUIRED / NOT NULL.
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | 25. LAMARAN
            |--------------------------------------------------------------------------
            | DILEWATI.
            |
            | cv adalah REQUIRED dan biasanya berupa file upload.
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | 26. TABEL YANG MEMBUTUHKAN FILE
            |--------------------------------------------------------------------------
            | Tidak dibuat dummy path karena dapat menyebabkan broken image/link.
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | 27. PESAN INFO
            |--------------------------------------------------------------------------
            */
            echo PHP_EOL;
            echo "=============================================" . PHP_EOL;
            echo "KITB DATABASE SEEDER BERHASIL" . PHP_EOL;
            echo "=============================================" . PHP_EOL;
            echo "Data utama berhasil dibuat." . PHP_EOL;
            echo "Tabel dengan file wajib dilewati." . PHP_EOL;
            echo PHP_EOL;
        });
    }
}
