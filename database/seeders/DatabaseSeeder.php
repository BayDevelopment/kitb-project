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

                    // Indonesian
                    'tentang_kami' => 'PT Kawasan Industri Tanjung Buton (KITB) merupakan pengelola kawasan industri yang dikembangkan untuk mendukung pertumbuhan industri, investasi, logistik, dan kegiatan ekonomi strategis di Indonesia.',

                    // English
                    'tentang_kami_en' => 'PT Kawasan Industri Tanjung Buton (KITB) is an industrial estate developer and operator established to support industrial growth, investment, logistics, and strategic economic activities in Indonesia.',

                    // Mandarin
                    'tentang_kami_zh' => 'PT Kawasan Industri Tanjung Buton（KITB）是一家工业园区开发与运营企业，致力于支持印度尼西亚的产业发展、投资、物流以及战略经济活动。',

                    // Indonesian
                    'latar_belakang' => 'Kawasan Industri Tanjung Buton dikembangkan sebagai kawasan industri terpadu dengan dukungan infrastruktur, akses logistik, serta potensi konektivitas menuju jalur perdagangan regional dan nasional.',

                    // English
                    'latar_belakang_en' => 'Tanjung Buton Industrial Estate is developed as an integrated industrial area supported by infrastructure, logistics access, and connectivity potential to regional and national trade routes.',

                    // Mandarin
                    'latar_belakang_zh' => '丹绒布顿工业园区定位为综合性工业园区，配备完善的基础设施、物流通道，并具备连接区域及国家贸易线路的潜力。',

                    // Indonesian
                    'moto' => 'Membangun Kawasan Industri Berkelanjutan',

                    // English
                    'moto_en' => 'Building a Sustainable Industrial Estate',

                    // Mandarin
                    'moto_zh' => '建设可持续发展的工业园区',

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

            DB::table('visis')->updateOrInsert(
                ['id' => 1],
                [
                    // Indonesian
                    'isi' => 'Menjadi kawasan industri terpadu yang unggul, berkelanjutan, kompetitif, dan memberikan nilai tambah bagi industri, masyarakat, serta perekonomian nasional.',

                    // English
                    'isi_en' => 'To become an excellent, sustainable, and competitive integrated industrial estate that creates added value for industries, communities, and the national economy.',

                    // Mandarin
                    'isi_zh' => '成为一个卓越、可持续且具有竞争力的综合性工业园区，为产业、社会以及国家经济创造附加价值。',

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
                    'isi_en' => 'Develop an industrial estate supported by reliable and integrated infrastructure.',
                    'isi_zh' => '建设配备可靠且一体化基础设施的工业园区。',
                    'urutan' => 1,
                ],

                [
                    'visi_id' => $visiId,
                    'isi' => 'Mendorong terciptanya iklim investasi yang aman, mudah, dan kompetitif.',
                    'isi_en' => 'Promote a safe, accessible, and competitive investment climate.',
                    'isi_zh' => '营造安全、便捷且具有竞争力的投资环境。',
                    'urutan' => 2,
                ],

                [
                    'visi_id' => $visiId,
                    'isi' => 'Memberikan pelayanan profesional kepada investor dan pelaku industri.',
                    'isi_en' => 'Provide professional services to investors and industrial stakeholders.',
                    'isi_zh' => '为投资者及产业参与者提供专业的服务。',
                    'urutan' => 3,
                ],

                [
                    'visi_id' => $visiId,
                    'isi' => 'Mendukung pertumbuhan ekonomi daerah dan menciptakan lapangan kerja berkelanjutan.',
                    'isi_en' => 'Support regional economic growth and create sustainable employment opportunities.',
                    'isi_zh' => '支持区域经济增长，并创造可持续的就业机会。',
                    'urutan' => 4,
                ],

                [
                    'visi_id' => $visiId,
                    'isi' => 'Menerapkan prinsip pembangunan kawasan industri yang berwawasan lingkungan.',
                    'isi_en' => 'Implement environmentally responsible principles in industrial estate development.',
                    'isi_zh' => '在工业园区开发中贯彻环境友好的发展理念。',
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
                        'isi_en' => $misi['isi_en'],
                        'isi_zh' => $misi['isi_zh'],
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
                    'nama_en' => 'Budi Santoso',
                    'nama_zh' => '布迪·桑托索',

                    'jabatan' => 'Direktur Utama',
                    'jabatan_en' => 'President Director',
                    'jabatan_zh' => '总裁董事',

                    'gambar' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Andi Pratama',
                    'nama_en' => 'Andi Pratama',
                    'nama_zh' => '安迪·普拉塔马',

                    'jabatan' => 'Direktur Operasional',
                    'jabatan_en' => 'Operations Director',
                    'jabatan_zh' => '运营董事',

                    'gambar' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Rina Kurniawati',
                    'nama_en' => 'Rina Kurniawati',
                    'nama_zh' => '丽娜·库尔尼亚瓦蒂',

                    'jabatan' => 'Direktur Keuangan',
                    'jabatan_en' => 'Finance Director',
                    'jabatan_zh' => '财务董事',

                    'gambar' => null,
                    'urutan' => 3,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Dedi Firmansyah',
                    'nama_en' => 'Dedi Firmansyah',
                    'nama_zh' => '德迪·菲尔曼夏',

                    'jabatan' => 'Manajer Pengembangan Kawasan',
                    'jabatan_en' => 'Area Development Manager',
                    'jabatan_zh' => '园区开发经理',

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
                        'nama_en' => $item['nama_en'],
                        'nama_zh' => $item['nama_zh'],

                        'jabatan_en' => $item['jabatan_en'],
                        'jabatan_zh' => $item['jabatan_zh'],

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
                    'nama_en' => 'PT Tanjung Buton Logistics',
                    'nama_zh' => '丹戎布顿物流有限公司',

                    'logo' => null,

                    'deskripsi' => 'Perusahaan yang bergerak dalam pengembangan layanan logistik dan mendukung kebutuhan rantai pasok kawasan industri.',
                    'deskripsi_en' => 'A company engaged in the development of logistics services and supporting the supply chain needs of the industrial estate.',
                    'deskripsi_zh' => '一家致力于物流服务发展并支持工业园区供应链需求的公司。',

                    'website' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],

                [
                    'nama' => 'PT Tanjung Buton Infrastruktur',
                    'nama_en' => 'PT Tanjung Buton Infrastructure',
                    'nama_zh' => '丹戎布顿基础设施有限公司',

                    'logo' => null,

                    'deskripsi' => 'Perusahaan yang mendukung pembangunan dan pengelolaan infrastruktur kawasan industri.',
                    'deskripsi_en' => 'A company supporting the development and management of industrial estate infrastructure.',
                    'deskripsi_zh' => '一家支持工业园区基础设施建设与管理的公司。',

                    'website' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],

                [
                    'nama' => 'PT Tanjung Buton Properti',
                    'nama_en' => 'PT Tanjung Buton Property',
                    'nama_zh' => '丹戎布顿地产有限公司',

                    'logo' => null,

                    'deskripsi' => 'Perusahaan yang bergerak dalam pengembangan aset dan properti pendukung kawasan industri.',
                    'deskripsi_en' => 'A company engaged in the development of assets and supporting properties for the industrial estate.',
                    'deskripsi_zh' => '一家致力于工业园区相关资产及配套物业开发的公司。',

                    'website' => null,
                    'urutan' => 3,
                    'aktif' => true,
                ],
            ];

            foreach ($anakUsaha as $item) {
                DB::table('anak_usahas')->updateOrInsert(
                    [
                        'nama' => $item['nama'],
                    ],
                    [
                        'nama_en' => $item['nama_en'],
                        'nama_zh' => $item['nama_zh'],

                        'logo' => $item['logo'],

                        'deskripsi' => $item['deskripsi'],
                        'deskripsi_en' => $item['deskripsi_en'],
                        'deskripsi_zh' => $item['deskripsi_zh'],

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

                    // Bahasa Indonesia
                    'deskripsi' => 'Kawasan industri terpadu yang dirancang untuk mendukung kegiatan manufaktur, logistik, energi, dan industri strategis dengan dukungan konektivitas serta infrastruktur kawasan.',

                    // English
                    'deskripsi_en' => 'An integrated industrial estate designed to support manufacturing, logistics, energy, and strategic industries, supported by strong connectivity and comprehensive estate infrastructure.',

                    // 中文
                    'deskripsi_zh' => '丹绒布顿工业园区是一个综合性工业园区，旨在支持制造业、物流、能源及战略性产业发展，并配备完善的园区基础设施和互联互通体系。',

                    // Luas kawasan dalam hektare
                    'luas_kawasan' => 5000.00,

                    // Lokasi
                    'lokasi' => 'Kabupaten Siak, Provinsi Riau',

                    // Koordinat pusat kawasan
                    'latitude' => 0.9321000,
                    'longitude' => 102.1425000,

                    // Batas kawasan - GeoJSON
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

                    // Tahun berdiri
                    'tahun_berdiri' => 2012,

                    // Status aktif
                    'status' => true,

                    // Gambar default
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
                    'nama_en' => 'Estate Road Network',
                    'nama_zh' => '园区道路网络',
                    'slug' => 'jaringan-jalan-kawasan',
                    'deskripsi' => 'Jaringan jalan kawasan yang mendukung mobilitas kendaraan industri dan distribusi logistik.',
                    'deskripsi_en' => 'An estate road network that supports the mobility of industrial vehicles and logistics distribution.',
                    'deskripsi_zh' => '园区道路网络,为工业车辆通行和物流配送提供有力支持。',
                    'gambar' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Pelabuhan dan Terminal Logistik',
                    'nama_en' => 'Port and Logistics Terminal',
                    'nama_zh' => '港口及物流码头',
                    'slug' => 'pelabuhan-terminal-logistik',
                    'deskripsi' => 'Infrastruktur pendukung kegiatan bongkar muat dan distribusi barang melalui jalur laut.',
                    'deskripsi_en' => 'Supporting infrastructure for loading and unloading activities and the distribution of goods by sea.',
                    'deskripsi_zh' => '支持装卸作业及海运货物配送的配套基础设施。',
                    'gambar' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Jaringan Listrik',
                    'nama_en' => 'Electricity Network',
                    'nama_zh' => '电力网络',
                    'slug' => 'jaringan-listrik',
                    'deskripsi' => 'Sistem kelistrikan untuk memenuhi kebutuhan operasional kawasan dan tenant.',
                    'deskripsi_en' => 'An electrical system that meets the operational needs of the estate and its tenants.',
                    'deskripsi_zh' => '满足园区及入驻企业运营需求的电力系统。',
                    'gambar' => null,
                    'urutan' => 3,
                    'aktif' => true,
                ],
                [
                    'nama' => 'Sistem Air Bersih',
                    'nama_en' => 'Clean Water System',
                    'nama_zh' => '清洁水供应系统',
                    'slug' => 'sistem-air-bersih',
                    'deskripsi' => 'Infrastruktur penyediaan air bersih untuk kebutuhan kawasan industri.',
                    'deskripsi_en' => 'Clean water supply infrastructure for the needs of the industrial estate.',
                    'deskripsi_zh' => '为工业园区用水需求提供的清洁水供应基础设施。',
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
                        'nama_en' => $item['nama_en'],
                        'nama_zh' => $item['nama_zh'],
                        'deskripsi' => $item['deskripsi'],
                        'deskripsi_en' => $item['deskripsi_en'],
                        'deskripsi_zh' => $item['deskripsi_zh'],
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
                    'nama_en' => 'Industrial Estate Management Building',
                    'nama_zh' => '园区管理大楼',

                    'slug' => 'gedung-pengelola-kawasan',

                    'deskripsi' => 'Pusat administrasi dan pelayanan pengelolaan kawasan industri.',
                    'deskripsi_en' => 'The central facility for industrial estate administration and management services.',
                    'deskripsi_zh' => '工业园区行政管理及服务中心。',

                    'gambar' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],

                [
                    'nama' => 'Area Komersial',
                    'nama_en' => 'Commercial Area',
                    'nama_zh' => '商业区',

                    'slug' => 'area-komersial',

                    'deskripsi' => 'Area komersial yang mendukung kebutuhan tenant dan pekerja kawasan.',
                    'deskripsi_en' => 'A commercial area that supports the needs of tenants and workers within the industrial estate.',
                    'deskripsi_zh' => '为园区租户及员工提供配套服务的商业区域。',

                    'gambar' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],

                [
                    'nama' => 'Fasilitas Kesehatan',
                    'nama_en' => 'Healthcare Facilities',
                    'nama_zh' => '医疗设施',

                    'slug' => 'fasilitas-kesehatan',

                    'deskripsi' => 'Fasilitas pelayanan kesehatan untuk mendukung aktivitas kawasan.',
                    'deskripsi_en' => 'Healthcare facilities provided to support activities within the industrial estate.',
                    'deskripsi_zh' => '为园区运营及日常活动提供支持的医疗服务设施。',

                    'gambar' => null,
                    'urutan' => 3,
                    'aktif' => true,
                ],

                [
                    'nama' => 'Fasilitas Keamanan',
                    'nama_en' => 'Security Facilities',
                    'nama_zh' => '安保设施',

                    'slug' => 'fasilitas-keamanan',

                    'deskripsi' => 'Fasilitas keamanan dan pengawasan kawasan selama 24 jam.',
                    'deskripsi_en' => 'Security and surveillance facilities providing 24-hour protection throughout the industrial estate.',
                    'deskripsi_zh' => '为园区提供全天候24小时安全保障及监控服务的设施。',

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
                        'nama_en' => $item['nama_en'],
                        'nama_zh' => $item['nama_zh'],

                        'deskripsi' => $item['deskripsi'],
                        'deskripsi_en' => $item['deskripsi_en'],
                        'deskripsi_zh' => $item['deskripsi_zh'],

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
                    'nama_en' => 'Tanjung Buton Industrial Estate Map',
                    'nama_zh' => '丹戎布通工业区地图',

                    'slug' => 'peta-kawasan-industri-tanjung-buton',

                    'deskripsi' => 'Peta umum kawasan industri beserta area pengembangan dan fasilitas pendukung.',
                    'deskripsi_en' => 'General map of the industrial estate, including development areas and supporting facilities.',
                    'deskripsi_zh' => '工业区总体地图，包括开发区域及配套设施。',

                    'gambar' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],

                [
                    'nama' => 'Peta Infrastruktur Kawasan',
                    'nama_en' => 'Industrial Estate Infrastructure Map',
                    'nama_zh' => '工业区基础设施地图',

                    'slug' => 'peta-infrastruktur-kawasan',

                    'deskripsi' => 'Peta jaringan infrastruktur utama yang tersedia di dalam kawasan.',
                    'deskripsi_en' => 'Map of the main infrastructure networks available within the industrial estate.',
                    'deskripsi_zh' => '工业区内主要基础设施网络地图。',

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
                        'nama_en' => $item['nama_en'],
                        'nama_zh' => $item['nama_zh'],

                        'deskripsi' => $item['deskripsi'],
                        'deskripsi_en' => $item['deskripsi_en'],
                        'deskripsi_zh' => $item['deskripsi_zh'],

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
                    // Bahasa Indonesia
                    'nama_rute' => 'Rute Tanjung Buton - Pelabuhan',
                    'jalur' => 'Kawasan Industri Tanjung Buton → Pelabuhan Tanjung Buton',
                    'deskripsi' => 'Rute utama menuju fasilitas pelabuhan untuk mendukung kegiatan logistik kawasan.',

                    // Bahasa Inggris
                    'nama_rute_en' => 'Tanjung Buton - Port Route',
                    'jalur_en' => 'Tanjung Buton Industrial Estate → Tanjung Buton Port',
                    'deskripsi_en' => 'The main route to the port facility supporting logistics activities within the industrial estate.',

                    // Bahasa Mandarin
                    'nama_rute_zh' => '丹戎布顿 - 港口路线',
                    'jalur_zh' => '丹戎布顿工业园区 → 丹戎布顿港',
                    'deskripsi_zh' => '通往港口设施的主要路线，为园区物流活动提供支持。',

                    // Data teknis
                    'jarak' => 8.50,
                    'satuan_jarak' => 'km',
                    'waktu_tempuh' => '15 - 20 menit',

                    // Asal & tujuan
                    'asal' => 'Kawasan Industri Tanjung Buton',
                    'tujuan' => 'Pelabuhan Tanjung Buton',

                    // Lokasi
                    'latitude' => 0.9321000,
                    'longitude' => 102.1425000,

                    // GeoJSON
                    'geometry' => null,

                    // Gambar
                    'gambar' => null,

                    // Pengaturan
                    'urutan' => 1,
                    'aktif' => true,
                ],

                [
                    // Bahasa Indonesia
                    'nama_rute' => 'Rute Kawasan - Pusat Kabupaten',
                    'jalur' => 'Kawasan Industri Tanjung Buton → Pusat Kabupaten',
                    'deskripsi' => 'Rute penghubung kawasan menuju pusat pemerintahan dan layanan kabupaten.',

                    // Bahasa Inggris
                    'nama_rute_en' => 'Industrial Estate - Regency Center Route',
                    'jalur_en' => 'Tanjung Buton Industrial Estate → Regency Center',
                    'deskripsi_en' => 'A connecting route from the industrial estate to the regency government center and public services.',

                    // Bahasa Mandarin
                    'nama_rute_zh' => '园区 - 县城路线',
                    'jalur_zh' => '丹戎布顿工业园区 → 县城中心',
                    'deskripsi_zh' => '连接工业园区与县政府中心及公共服务设施的路线。',

                    // Data teknis
                    'jarak' => 35.00,
                    'satuan_jarak' => 'km',
                    'waktu_tempuh' => '45 - 60 menit',

                    // Asal & tujuan
                    'asal' => 'Kawasan Industri Tanjung Buton',
                    'tujuan' => 'Pusat Kabupaten',

                    // Lokasi
                    'latitude' => 0.9321000,
                    'longitude' => 102.1425000,

                    // GeoJSON
                    'geometry' => null,

                    // Gambar
                    'gambar' => null,

                    // Pengaturan
                    'urutan' => 2,
                    'aktif' => true,
                ],

                [
                    // Bahasa Indonesia
                    'nama_rute' => 'Rute Kawasan - Kota Pekanbaru',
                    'jalur' => 'Kawasan Industri Tanjung Buton → Pekanbaru',
                    'deskripsi' => 'Rute darat menuju pusat ekonomi Provinsi Riau.',

                    // Bahasa Inggris
                    'nama_rute_en' => 'Industrial Estate - Pekanbaru Route',
                    'jalur_en' => 'Tanjung Buton Industrial Estate → Pekanbaru',
                    'deskripsi_en' => 'A land route connecting the industrial estate to Pekanbaru, the economic center of Riau Province.',

                    // Bahasa Mandarin
                    'nama_rute_zh' => '园区 - 北干巴鲁路线',
                    'jalur_zh' => '丹戎布顿工业园区 → 北干巴鲁',
                    'deskripsi_zh' => '连接工业园区与廖内省经济中心北干巴鲁的陆路路线。',

                    // Data teknis
                    'jarak' => 180.00,
                    'satuan_jarak' => 'km',
                    'waktu_tempuh' => '4 - 5 jam',

                    // Asal & tujuan
                    'asal' => 'Kawasan Industri Tanjung Buton',
                    'tujuan' => 'Pekanbaru',

                    // Lokasi
                    'latitude' => 0.9321000,
                    'longitude' => 102.1425000,

                    // GeoJSON
                    'geometry' => null,

                    // Gambar
                    'gambar' => null,

                    // Pengaturan
                    'urutan' => 3,
                    'aktif' => true,
                ],
            ];

            foreach ($rutes as $item) {
                DB::table('rutes')->updateOrInsert(
                    [
                        'nama_rute' => $item['nama_rute'],
                    ],
                    [
                        // Bahasa Indonesia
                        'jalur' => $item['jalur'],
                        'deskripsi' => $item['deskripsi'],

                        // Bahasa Inggris
                        'nama_rute_en' => $item['nama_rute_en'],
                        'jalur_en' => $item['jalur_en'],
                        'deskripsi_en' => $item['deskripsi_en'],

                        // Bahasa Mandarin
                        'nama_rute_zh' => $item['nama_rute_zh'],
                        'jalur_zh' => $item['jalur_zh'],
                        'deskripsi_zh' => $item['deskripsi_zh'],

                        // Data teknis
                        'jarak' => $item['jarak'],
                        'satuan_jarak' => $item['satuan_jarak'],
                        'waktu_tempuh' => $item['waktu_tempuh'],

                        // Asal & tujuan
                        'asal' => $item['asal'],
                        'tujuan' => $item['tujuan'],

                        // Lokasi
                        'latitude' => $item['latitude'],
                        'longitude' => $item['longitude'],

                        // GeoJSON
                        'geometry' => $item['geometry'],

                        // Gambar
                        'gambar' => $item['gambar'],

                        // Pengaturan
                        'urutan' => $item['urutan'],
                        'aktif' => $item['aktif'],

                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            /********************************************************************************
             * |--------------------------------------------------------------------------|
             * | 13. PELUANG INVESTASI                                                     |
             * |--------------------------------------------------------------------------|
             ********************************************************************************/

            $investasi = [
                [
                    // Indonesia
                    'judul' => 'Kawasan Industri Manufaktur',
                    'sektor_industri' => 'Manufaktur',
                    'deskripsi' => 'Peluang investasi untuk pengembangan industri manufaktur dan industri pendukung.',
                    'lokasi' => 'Zona Industri Utama',

                    // English
                    'judul_en' => 'Manufacturing Industrial Estate',
                    'sektor_industri_en' => 'Manufacturing',
                    'deskripsi_en' => 'An investment opportunity for the development of manufacturing industries and supporting industries.',
                    'lokasi_en' => 'Main Industrial Zone',

                    // Mandarin
                    'judul_zh' => '制造业工业园区',
                    'sektor_industri_zh' => '制造业',
                    'deskripsi_zh' => '为制造业及配套产业发展提供投资机会。',
                    'lokasi_zh' => '主要工业区',

                    // General
                    'slug' => 'kawasan-industri-manufaktur',
                    'luas_lahan' => 500.00,
                    'satuan_luas' => 'Ha',
                    'status' => 'tersedia',
                    'nilai_investasi' => 2500000000000.00,
                    'mata_uang' => 'IDR',
                    'gambar' => null,
                    'urutan' => 1,
                    'aktif' => true,
                ],

                [
                    // Indonesia
                    'judul' => 'Kawasan Logistik dan Pergudangan',
                    'sektor_industri' => 'Logistik',
                    'deskripsi' => 'Area investasi untuk pergudangan, distribusi, dan kegiatan logistik terpadu.',
                    'lokasi' => 'Zona Logistik',

                    // English
                    'judul_en' => 'Logistics and Warehousing Area',
                    'sektor_industri_en' => 'Logistics',
                    'deskripsi_en' => 'An investment area for warehousing, distribution, and integrated logistics activities.',
                    'lokasi_en' => 'Logistics Zone',

                    // Mandarin
                    'judul_zh' => '物流与仓储园区',
                    'sektor_industri_zh' => '物流业',
                    'deskripsi_zh' => '为仓储、配送及综合物流活动提供投资区域。',
                    'lokasi_zh' => '物流区',

                    // General
                    'slug' => 'kawasan-logistik-pergudangan',
                    'luas_lahan' => 250.00,
                    'satuan_luas' => 'Ha',
                    'status' => 'tersedia',
                    'nilai_investasi' => 1250000000000.00,
                    'mata_uang' => 'IDR',
                    'gambar' => null,
                    'urutan' => 2,
                    'aktif' => true,
                ],

                [
                    // Indonesia
                    'judul' => 'Industri Hilirisasi',
                    'sektor_industri' => 'Hilirisasi',
                    'deskripsi' => 'Peluang investasi bagi industri pengolahan dan hilirisasi komoditas strategis.',
                    'lokasi' => 'Zona Industri Pengembangan',

                    // English
                    'judul_en' => 'Downstream Processing Industry',
                    'sektor_industri_en' => 'Downstream Processing',
                    'deskripsi_en' => 'An investment opportunity for processing industries and the downstream development of strategic commodities.',
                    'lokasi_en' => 'Industrial Development Zone',

                    // Mandarin
                    'judul_zh' => '下游产业',
                    'sektor_industri_zh' => '下游产业',
                    'deskripsi_zh' => '为战略性商品加工及下游产业发展提供投资机会。',
                    'lokasi_zh' => '工业开发区',

                    // General
                    'slug' => 'industri-hilirisasi',
                    'luas_lahan' => 750.00,
                    'satuan_luas' => 'Ha',
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
                        // Indonesia
                        'judul' => $item['judul'],
                        'sektor_industri' => $item['sektor_industri'],
                        'deskripsi' => $item['deskripsi'],
                        'lokasi' => $item['lokasi'],

                        // English
                        'judul_en' => $item['judul_en'],
                        'sektor_industri_en' => $item['sektor_industri_en'],
                        'deskripsi_en' => $item['deskripsi_en'],
                        'lokasi_en' => $item['lokasi_en'],

                        // Mandarin
                        'judul_zh' => $item['judul_zh'],
                        'sektor_industri_zh' => $item['sektor_industri_zh'],
                        'deskripsi_zh' => $item['deskripsi_zh'],
                        'lokasi_zh' => $item['lokasi_zh'],

                        // General
                        'luas_lahan' => $item['luas_lahan'],
                        'satuan_luas' => $item['satuan_luas'],
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
                    'judul_en' => 'Integrated Licensing',
                    'judul_zh' => '一体化许可服务',

                    'slug' => 'perizinan-terintegrasi',

                    'ringkasan' => 'Proses perizinan yang mudah dan terkoordinasi.',
                    'ringkasan_en' => 'Easy and coordinated licensing processes.',
                    'ringkasan_zh' => '便捷且协调的许可办理流程。',

                    'deskripsi' => 'Investor mendapatkan dukungan informasi dan pendampingan dalam proses perizinan usaha.',
                    'deskripsi_en' => 'Investors receive information support and assistance throughout the business licensing process.',
                    'deskripsi_zh' => '投资者可在企业许可办理过程中获得信息支持与咨询协助。',

                    'ikon' => 'FileCheck',
                    'urutan' => 1,
                    'aktif' => true,
                ],

                [
                    'judul' => 'Infrastruktur Terpadu',
                    'judul_en' => 'Integrated Infrastructure',
                    'judul_zh' => '综合基础设施',

                    'slug' => 'infrastruktur-terpadu',

                    'ringkasan' => 'Infrastruktur kawasan yang mendukung kegiatan industri.',
                    'ringkasan_en' => 'Integrated infrastructure supporting industrial activities.',
                    'ringkasan_zh' => '为工业活动提供支持的综合园区基础设施。',

                    'deskripsi' => 'Kawasan dilengkapi dengan infrastruktur dasar dan fasilitas pendukung untuk memenuhi kebutuhan tenant.',
                    'deskripsi_en' => 'The area is equipped with basic infrastructure and supporting facilities to meet tenant needs.',
                    'deskripsi_zh' => '园区配备完善的基础设施和配套设施，以满足入驻企业的需求。',

                    'ikon' => 'Building2',
                    'urutan' => 2,
                    'aktif' => true,
                ],

                [
                    'judul' => 'Dukungan Investasi',
                    'judul_en' => 'Investment Support',
                    'judul_zh' => '投资支持',

                    'slug' => 'dukungan-investasi',

                    'ringkasan' => 'Pendampingan bagi calon investor dan tenant.',
                    'ringkasan_en' => 'Assistance for prospective investors and tenants.',
                    'ringkasan_zh' => '为潜在投资者和入驻企业提供支持。',

                    'deskripsi' => 'Tim pengelola kawasan membantu investor memahami potensi, fasilitas, serta proses pengembangan usaha.',
                    'deskripsi_en' => 'The estate management team assists investors in understanding the potential, facilities, and business development processes.',
                    'deskripsi_zh' => '园区管理团队协助投资者了解园区潜力、设施以及企业发展流程。',

                    'ikon' => 'Handshake',
                    'urutan' => 3,
                    'aktif' => true,
                ],

                [
                    'judul' => 'Konektivitas Logistik',
                    'judul_en' => 'Logistics Connectivity',
                    'judul_zh' => '物流连接',

                    'slug' => 'konektivitas-logistik',

                    'ringkasan' => 'Akses menuju jaringan transportasi dan pelabuhan.',
                    'ringkasan_en' => 'Access to transportation networks and ports.',
                    'ringkasan_zh' => '连接交通网络和港口的便捷通道。',

                    'deskripsi' => 'Konektivitas kawasan mendukung pergerakan bahan baku dan produk menuju pasar.',
                    'deskripsi_en' => 'The area connectivity supports the movement of raw materials and products to domestic and international markets.',
                    'deskripsi_zh' => '园区完善的交通连接有助于原材料和产品向市场高效流通。',

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
                        'judul_en' => $item['judul_en'],
                        'judul_zh' => $item['judul_zh'],

                        'ringkasan' => $item['ringkasan'],
                        'ringkasan_en' => $item['ringkasan_en'],
                        'ringkasan_zh' => $item['ringkasan_zh'],

                        'deskripsi' => $item['deskripsi'],
                        'deskripsi_en' => $item['deskripsi_en'],
                        'deskripsi_zh' => $item['deskripsi_zh'],

                        'ikon' => $item['ikon'],
                        'urutan' => $item['urutan'],
                        'aktif' => $item['aktif'],

                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }

            /*
|--------------------------------------------------------------------------
| 15. BERITA
|--------------------------------------------------------------------------
*/
            $berita = [
                [
                    // Bahasa Indonesia
                    'judul_id' => 'KITB Dorong Pengembangan Kawasan Industri Berkelanjutan',
                    'excerpt_id' => 'Pengembangan kawasan industri diarahkan untuk menciptakan ekosistem industri yang kompetitif dan berkelanjutan.',
                    'konten_id' => '<p>PT Kawasan Industri Tanjung Buton terus mendorong pengembangan kawasan industri yang terintegrasi, kompetitif, dan berkelanjutan.</p><p>Pengembangan kawasan dilakukan dengan memperhatikan kebutuhan industri, infrastruktur, lingkungan, dan masyarakat sekitar.</p>',

                    // English
                    'judul_en' => 'KITB Promotes Sustainable Industrial Estate Development',
                    'excerpt_en' => 'The development of the industrial estate is aimed at creating a competitive and sustainable industrial ecosystem.',
                    'konten_en' => '<p>PT Kawasan Industri Tanjung Buton continues to promote the development of an integrated, competitive, and sustainable industrial estate.</p><p>The development takes into account the needs of industries, infrastructure, the environment, and surrounding communities.</p>',

                    // Mandarin
                    'judul_zh' => 'KITB 推动可持续工业园区发展',
                    'excerpt_zh' => '工业园区的发展旨在打造具有竞争力和可持续发展的产业生态系统。',
                    'konten_zh' => '<p>丹绒布顿工业园区（PT Kawasan Industri Tanjung Buton）持续推动一体化、具有竞争力和可持续发展的工业园区建设。</p><p>园区发展充分考虑产业需求、基础设施、环境以及周边社区的需求。</p>',

                    'slug' => 'kitb-dorong-pengembangan-kawasan-industri-berkelanjutan',
                    'gambar' => null,
                    'kategori' => 'Kawasan',
                    'penulis' => 'Admin KITB',
                    'status' => 'published',
                    'published_at' => now()->subDays(5),
                    'is_featured' => true,
                    'views' => 125,
                ],

                [
                    // Bahasa Indonesia
                    'judul_id' => 'Peluang Investasi Baru di Kawasan Industri Tanjung Buton',
                    'excerpt_id' => 'KITB membuka peluang investasi bagi berbagai sektor industri strategis.',
                    'konten_id' => '<p>Kawasan Industri Tanjung Buton menyediakan berbagai peluang investasi untuk sektor manufaktur, logistik, hilirisasi, dan industri pendukung.</p>',

                    // English
                    'judul_en' => 'New Investment Opportunities at Tanjung Buton Industrial Estate',
                    'excerpt_en' => 'KITB offers investment opportunities across various strategic industrial sectors.',
                    'konten_en' => '<p>Tanjung Buton Industrial Estate provides various investment opportunities in manufacturing, logistics, downstream industries, and supporting industries.</p>',

                    // Mandarin
                    'judul_zh' => '丹绒布顿工业园区的新投资机会',
                    'excerpt_zh' => 'KITB 为多个战略产业领域提供投资机会。',
                    'konten_zh' => '<p>丹绒布顿工业园区为制造业、物流、下游产业以及配套产业提供多种投资机会。</p>',

                    'slug' => 'peluang-investasi-baru-di-kawasan-industri-tanjung-buton',
                    'gambar' => null,
                    'kategori' => 'Investasi',
                    'penulis' => 'Admin KITB',
                    'status' => 'published',
                    'published_at' => now()->subDays(10),
                    'is_featured' => false,
                    'views' => 86,
                ],

                [
                    // Bahasa Indonesia
                    'judul_id' => 'Penguatan Infrastruktur Pendukung Kawasan',
                    'excerpt_id' => 'Pengembangan infrastruktur menjadi bagian penting dalam meningkatkan daya saing kawasan.',
                    'konten_id' => '<p>Penguatan infrastruktur kawasan menjadi salah satu fokus utama dalam mendukung kebutuhan investor dan tenant.</p>',

                    // English
                    'judul_en' => 'Strengthening Supporting Infrastructure for the Industrial Estate',
                    'excerpt_en' => 'Infrastructure development is an important part of improving the competitiveness of the industrial estate.',
                    'konten_en' => '<p>Strengthening the infrastructure of the industrial estate is one of the main focuses in supporting the needs of investors and tenants.</p>',

                    // Mandarin
                    'judul_zh' => '加强工业园区配套基础设施',
                    'excerpt_zh' => '基础设施建设是提高园区竞争力的重要组成部分。',
                    'konten_zh' => '<p>加强工业园区基础设施建设是满足投资者和租户需求的重要重点之一。</p>',

                    'slug' => 'penguatan-infrastruktur-pendukung-kawasan',
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
                        // Bahasa Indonesia
                        'judul_id' => $item['judul_id'],
                        'excerpt_id' => $item['excerpt_id'],
                        'konten_id' => $item['konten_id'],

                        // English
                        'judul_en' => $item['judul_en'],
                        'excerpt_en' => $item['excerpt_en'],
                        'konten_en' => $item['konten_en'],

                        // Mandarin
                        'judul_zh' => $item['judul_zh'],
                        'excerpt_zh' => $item['excerpt_zh'],
                        'konten_zh' => $item['konten_zh'],

                        // Data umum
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
                    'slug' => 'staff-administrasi',
                    'judul_id' => 'Staff Administrasi',
                    'judul_en' => 'Administrative Staff',
                    'judul_zh' => '行政管理专员',
                    'departemen_id' => 'Administrasi',
                    'departemen_en' => 'Administration',
                    'departemen_zh' => '行政部门',
                    'lokasi_id' => 'Kawasan Industri Tanjung Buton',
                    'lokasi_en' => 'Tanjung Buton Industrial Estate',
                    'lokasi_zh' => '丹戎布顿工业园区',
                    'tipe_pekerjaan' => 'Full Time',
                    'deskripsi_id' => '<p>Bertanggung jawab terhadap kegiatan administrasi dan dokumentasi perusahaan.</p>',
                    'deskripsi_en' => '<p>Responsible for company administration and documentation activities.</p>',
                    'deskripsi_zh' => '<p>负责公司的行政管理和文件资料工作。</p>',
                    'tanggung_jawab_id' => '<ul><li>Mengelola dokumen administrasi.</li><li>Menyusun laporan rutin.</li><li>Mendukung kegiatan operasional departemen.</li></ul>',
                    'tanggung_jawab_en' => '<ul><li>Manage administrative documents.</li><li>Prepare routine reports.</li><li>Support departmental operational activities.</li></ul>',
                    'tanggung_jawab_zh' => '<ul><li>管理行政文件。</li><li>编制日常报告。</li><li>支持部门运营工作。</li></ul>',
                    'kualifikasi_id' => '<ul><li>Minimal D3/S1.</li><li>Mampu menggunakan Microsoft Office.</li><li>Memiliki kemampuan komunikasi yang baik.</li></ul>',
                    'kualifikasi_en' => '<ul><li>Minimum D3/Bachelor degree.</li><li>Proficient in Microsoft Office.</li><li>Good communication skills.</li></ul>',
                    'kualifikasi_zh' => '<ul><li>最低大专或本科学历。</li><li>熟练使用 Microsoft Office。</li><li>具备良好的沟通能力。</li></ul>',
                    'benefit_id' => '<ul><li>Lingkungan kerja profesional.</li><li>Pengembangan kompetensi.</li><li>Jenjang karier.</li></ul>',
                    'benefit_en' => '<ul><li>Professional work environment.</li><li>Competency development.</li><li>Career development opportunities.</li></ul>',
                    'benefit_zh' => '<ul><li>专业的工作环境。</li><li>能力提升机会。</li><li>职业发展机会。</li></ul>',
                    'tanggal_mulai' => now()->toDateString(),
                    'tanggal_tutup' => now()->addDays(30)->toDateString(),
                    'status' => 'published',
                    'unggulan' => true,
                    'urutan' => 1,
                ],
                [
                    'slug' => 'staff-it-support',
                    'judul_id' => 'Staff IT Support',
                    'judul_en' => 'IT Support Staff',
                    'judul_zh' => 'IT 技术支持专员',
                    'departemen_id' => 'Teknologi Informasi',
                    'departemen_en' => 'Information Technology',
                    'departemen_zh' => '信息技术部门',
                    'lokasi_id' => 'Kawasan Industri Tanjung Buton',
                    'lokasi_en' => 'Tanjung Buton Industrial Estate',
                    'lokasi_zh' => '丹戎布顿工业园区',
                    'tipe_pekerjaan' => 'Full Time',
                    'deskripsi_id' => '<p>Mendukung operasional teknologi informasi dan sistem digital perusahaan.</p>',
                    'deskripsi_en' => '<p>Support the company information technology operations and digital systems.</p>',
                    'deskripsi_zh' => '<p>负责支持公司的信息技术运营和数字化系统。</p>',
                    'tanggung_jawab_id' => '<ul><li>Melakukan troubleshooting perangkat.</li><li>Mendukung sistem jaringan.</li><li>Mendukung pengguna internal.</li></ul>',
                    'tanggung_jawab_en' => '<ul><li>Troubleshoot hardware and devices.</li><li>Support network systems.</li><li>Provide support to internal users.</li></ul>',
                    'tanggung_jawab_zh' => '<ul><li>进行设备故障排查。</li><li>支持网络系统。</li><li>为内部用户提供技术支持。</li></ul>',
                    'kualifikasi_id' => '<ul><li>Minimal D3 Teknik Informatika/Sistem Informasi.</li><li>Memahami jaringan komputer.</li><li>Mampu melakukan troubleshooting.</li></ul>',
                    'kualifikasi_en' => '<ul><li>Minimum D3 in Informatics Engineering/Information Systems.</li><li>Understand computer networks.</li><li>Able to perform troubleshooting.</li></ul>',
                    'kualifikasi_zh' => '<ul><li>最低信息工程或信息系统大专学历。</li><li>了解计算机网络。</li><li>具备故障排查能力。</li></ul>',
                    'benefit_id' => '<ul><li>Lingkungan kerja profesional.</li><li>Pengembangan kompetensi.</li><li>Jenjang karier.</li></ul>',
                    'benefit_en' => '<ul><li>Professional work environment.</li><li>Competency development.</li><li>Career development opportunities.</li></ul>',
                    'benefit_zh' => '<ul><li>专业的工作环境。</li><li>能力提升机会。</li><li>职业发展机会。</li></ul>',
                    'tanggal_mulai' => now()->toDateString(),
                    'tanggal_tutup' => now()->addDays(45)->toDateString(),
                    'status' => 'published',
                    'unggulan' => false,
                    'urutan' => 2,
                ],
                [
                    'slug' => 'staff-marketing-business-development',
                    'judul_id' => 'Staff Marketing & Business Development',
                    'judul_en' => 'Marketing & Business Development Staff',
                    'judul_zh' => '市场与业务发展专员',
                    'departemen_id' => 'Marketing',
                    'departemen_en' => 'Marketing',
                    'departemen_zh' => '市场部门',
                    'lokasi_id' => 'Kawasan Industri Tanjung Buton',
                    'lokasi_en' => 'Tanjung Buton Industrial Estate',
                    'lokasi_zh' => '丹戎布顿工业园区',
                    'tipe_pekerjaan' => 'Full Time',
                    'deskripsi_id' => '<p>Mendukung kegiatan pemasaran kawasan dan pengembangan hubungan dengan calon investor.</p>',
                    'deskripsi_en' => '<p>Support estate marketing activities and relationship development with prospective investors.</p>',
                    'deskripsi_zh' => '<p>支持园区市场推广活动以及与潜在投资者的关系发展。</p>',
                    'tanggung_jawab_id' => '<ul><li>Mengelola komunikasi dengan calon investor.</li><li>Menyusun materi pemasaran.</li><li>Melakukan analisis peluang bisnis.</li></ul>',
                    'tanggung_jawab_en' => '<ul><li>Manage communication with prospective investors.</li><li>Prepare marketing materials.</li><li>Analyze business opportunities.</li></ul>',
                    'tanggung_jawab_zh' => '<ul><li>负责与潜在投资者沟通。</li><li>编制市场推广材料。</li><li>分析商业机会。</li></ul>',
                    'kualifikasi_id' => '<ul><li>Minimal S1.</li><li>Komunikatif dan memiliki kemampuan negosiasi.</li><li>Memahami konsep pemasaran dan bisnis.</li></ul>',
                    'kualifikasi_en' => '<ul><li>Bachelor degree minimum.</li><li>Good communication and negotiation skills.</li><li>Understand marketing and business concepts.</li></ul>',
                    'kualifikasi_zh' => '<ul><li>最低本科。</li><li>具备良好的沟通和谈判能力。</li><li>了解市场营销和商业概念。</li></ul>',
                    'benefit_id' => '<ul><li>Insentif kinerja.</li><li>Pengembangan kompetensi.</li><li>Jenjang karier.</li></ul>',
                    'benefit_en' => '<ul><li>Performance incentives.</li><li>Competency development.</li><li>Career development opportunities.</li></ul>',
                    'benefit_zh' => '<ul><li>绩效奖励。</li><li>能力提升机会。</li><li>职业发展机会。</li></ul>',
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
                        'judul_id' => $item['judul_id'],
                        'judul_en' => $item['judul_en'],
                        'judul_zh' => $item['judul_zh'],
                        'departemen_id' => $item['departemen_id'],
                        'departemen_en' => $item['departemen_en'],
                        'departemen_zh' => $item['departemen_zh'],
                        'lokasi_id' => $item['lokasi_id'],
                        'lokasi_en' => $item['lokasi_en'],
                        'lokasi_zh' => $item['lokasi_zh'],
                        'tipe_pekerjaan' => $item['tipe_pekerjaan'],
                        'deskripsi_id' => $item['deskripsi_id'],
                        'deskripsi_en' => $item['deskripsi_en'],
                        'deskripsi_zh' => $item['deskripsi_zh'],
                        'tanggung_jawab_id' => $item['tanggung_jawab_id'],
                        'tanggung_jawab_en' => $item['tanggung_jawab_en'],
                        'tanggung_jawab_zh' => $item['tanggung_jawab_zh'],
                        'kualifikasi_id' => $item['kualifikasi_id'],
                        'kualifikasi_en' => $item['kualifikasi_en'],
                        'kualifikasi_zh' => $item['kualifikasi_zh'],
                        'benefit_id' => $item['benefit_id'],
                        'benefit_en' => $item['benefit_en'],
                        'benefit_zh' => $item['benefit_zh'],
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
            | 28. SAMBUTAN DIREKTUR
            |--------------------------------------------------------------------------
            */
            DB::table('sambutan_direkturs')->updateOrInsert(
                ['id' => 1],
                [
                    // Bahasa Indonesia
                    'nama_direktur' => 'Budi Santoso',
                    'jabatan_direktur' => 'Direktur Utama',
                    'sambutan_direktur' => '<p>Selamat datang di situs resmi PT Kawasan Industri Tanjung Buton.</p><p>Kami berkomitmen untuk mengembangkan kawasan industri yang terintegrasi, kompetitif, dan berkelanjutan. Melalui dukungan infrastruktur, pelayanan profesional, serta kolaborasi dengan para mitra, kami berharap dapat menciptakan nilai tambah bagi investor, masyarakat, dan perekonomian Indonesia.</p><p>Terima kasih atas kepercayaan dan dukungan Anda terhadap pengembangan Kawasan Industri Tanjung Buton.</p>',

                    // Bahasa Inggris
                    'nama_direktur_en' => 'Budi Santoso',
                    'jabatan_direktur_en' => 'President Director',
                    'sambutan_direktur_en' => '<p>Welcome to the official website of PT Kawasan Industri Tanjung Buton.</p><p>We are committed to developing an integrated, competitive, and sustainable industrial estate. Through reliable infrastructure, professional services, and collaboration with our partners, we aim to create added value for investors, communities, and the Indonesian economy.</p><p>Thank you for your trust and support in the development of Tanjung Buton Industrial Estate.</p>',

                    // Bahasa Mandarin
                    'nama_direktur_zh' => '布迪·桑托索',
                    'jabatan_direktur_zh' => '总裁董事',
                    'sambutan_direktur_zh' => '<p>欢迎访问 PT Kawasan Industri Tanjung Buton 官方网站。</p><p>我们致力于打造一体化、具有竞争力且可持续发展的工业园区。通过可靠的基础设施、专业的服务以及与合作伙伴的协作，我们希望为投资者、当地社区和印度尼西亚经济创造更多价值。</p><p>感谢您对丹绒布顿工业园区发展的信任与支持。</p>',

                    // Data umum
                    'foto_direktur' => null,
                    'status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

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
