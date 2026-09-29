<?php

namespace Database\Seeders;

use App\Models\PeluangInvestasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PeluangInvestasiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'judul' => 'Pabrik Pengolahan Kelapa Sawit Terintegrasi',
                'sektor_industri' => 'Agroindustri',
                'deskripsi' => 'Peluang investasi pembangunan pabrik pengolahan kelapa sawit terintegrasi yang mencakup fasilitas pengolahan bahan baku, produksi, penyimpanan, dan distribusi. Lokasi berada di dalam kawasan industri dengan dukungan infrastruktur dasar dan akses logistik yang dapat menunjang kegiatan operasional industri.',
                'luas_lahan' => 25.50,
                'satuan_luas' => 'Ha',
                'lokasi' => 'Zona Industri Utama, KITB',
                'status' => 'tersedia',
                'nilai_investasi' => 350000000000,
                'mata_uang' => 'IDR',
                'gambar' => null,
                'urutan' => 1,
                'aktif' => true,
            ],
            [
                'judul' => 'Industri Hilirisasi Produk Kelapa',
                'sektor_industri' => 'Agroindustri',
                'deskripsi' => 'Pengembangan industri hilirisasi kelapa untuk menghasilkan berbagai produk turunan bernilai tambah, seperti bahan pangan, bahan baku industri, dan produk olahan lainnya. Kawasan ini menawarkan peluang pengembangan fasilitas produksi dengan akses terhadap jaringan logistik dan infrastruktur kawasan.',
                'luas_lahan' => 18.00,
                'satuan_luas' => 'Ha',
                'lokasi' => 'Zona Agroindustri, KITB',
                'status' => 'tersedia',
                'nilai_investasi' => 175000000000,
                'mata_uang' => 'IDR',
                'gambar' => null,
                'urutan' => 2,
                'aktif' => true,
            ],
            [
                'judul' => 'Pabrik Oleokimia',
                'sektor_industri' => 'Kimia',
                'deskripsi' => 'Peluang pengembangan fasilitas industri oleokimia yang memanfaatkan bahan baku berbasis minyak nabati untuk menghasilkan produk turunan bagi kebutuhan industri. Lokasi dan ketersediaan lahan dirancang untuk mendukung pembangunan fasilitas produksi berskala menengah hingga besar.',
                'luas_lahan' => 35.75,
                'satuan_luas' => 'Ha',
                'lokasi' => 'Kawasan Industri Selatan, KITB',
                'status' => 'tersedia',
                'nilai_investasi' => 850000000000,
                'mata_uang' => 'IDR',
                'gambar' => null,
                'urutan' => 3,
                'aktif' => true,
            ],
            [
                'judul' => 'Gudang Logistik & Distribution Center',
                'sektor_industri' => 'Logistik',
                'deskripsi' => 'Peluang investasi pembangunan pusat pergudangan dan distribusi untuk mendukung aktivitas rantai pasok perusahaan di dalam maupun di luar kawasan industri. Fasilitas dapat dikembangkan untuk kebutuhan penyimpanan, konsolidasi, distribusi, dan pengelolaan barang.',
                'luas_lahan' => 12.50,
                'satuan_luas' => 'Ha',
                'lokasi' => 'Zona Logistik, KITB',
                'status' => 'terbatas',
                'nilai_investasi' => 120000000000,
                'mata_uang' => 'IDR',
                'gambar' => null,
                'urutan' => 4,
                'aktif' => true,
            ],
            [
                'judul' => 'Industri Pengolahan Kayu Terpadu',
                'sektor_industri' => 'Manufaktur',
                'deskripsi' => 'Pengembangan fasilitas industri pengolahan kayu terpadu mulai dari pengolahan bahan baku hingga menghasilkan produk manufaktur bernilai tambah. Kawasan menyediakan ruang pengembangan yang dapat disesuaikan dengan kebutuhan fasilitas produksi dan pendukungnya.',
                'luas_lahan' => 20.00,
                'satuan_luas' => 'Ha',
                'lokasi' => 'Zona Industri Barat, KITB',
                'status' => 'tersedia',
                'nilai_investasi' => 275000000000,
                'mata_uang' => 'IDR',
                'gambar' => null,
                'urutan' => 5,
                'aktif' => true,
            ],
            [
                'judul' => 'Pabrik Komponen Elektronik',
                'sektor_industri' => 'Elektronik',
                'deskripsi' => 'Peluang investasi pembangunan fasilitas manufaktur komponen elektronik untuk memenuhi kebutuhan industri domestik dan pasar ekspor. Pengembangan dapat mencakup fasilitas produksi, perakitan, pengujian, penyimpanan, serta fasilitas pendukung kegiatan manufaktur.',
                'luas_lahan' => 15.25,
                'satuan_luas' => 'Ha',
                'lokasi' => 'Zona Industri Utama, KITB',
                'status' => 'dalam_penjajakan',
                'nilai_investasi' => 500000000000,
                'mata_uang' => 'IDR',
                'gambar' => null,
                'urutan' => 6,
                'aktif' => true,
            ],
            [
                'judul' => 'Pembangkit Listrik Tenaga Biomassa',
                'sektor_industri' => 'Energi',
                'deskripsi' => 'Peluang pengembangan fasilitas pembangkit listrik berbasis biomassa untuk mendukung kebutuhan energi kawasan industri. Proyek diarahkan untuk memanfaatkan sumber bahan baku biomassa yang tersedia serta mendukung penyediaan energi bagi kegiatan industri di kawasan.',
                'luas_lahan' => 10.00,
                'satuan_luas' => 'Ha',
                'lokasi' => 'Zona Utilitas, KITB',
                'status' => 'tersedia',
                'nilai_investasi' => 225000000000,
                'mata_uang' => 'IDR',
                'gambar' => null,
                'urutan' => 7,
                'aktif' => true,
            ],
            [
                'judul' => 'Terminal Logistik & Pergudangan',
                'sektor_industri' => 'Logistik',
                'deskripsi' => 'Pengembangan terminal logistik dan fasilitas pergudangan untuk menunjang kegiatan penerimaan, penyimpanan, konsolidasi, serta distribusi barang. Lokasi dirancang untuk mendukung efisiensi pergerakan barang dan konektivitas rantai pasok kawasan industri.',
                'luas_lahan' => 30.00,
                'satuan_luas' => 'Ha',
                'lokasi' => 'Kawasan Logistik, KITB',
                'status' => 'terbatas',
                'nilai_investasi' => 400000000000,
                'mata_uang' => 'IDR',
                'gambar' => null,
                'urutan' => 8,
                'aktif' => true,
            ],
            [
                'judul' => 'Industri Pengolahan Hasil Laut',
                'sektor_industri' => 'Maritim',
                'deskripsi' => 'Peluang investasi pembangunan fasilitas pengolahan hasil laut menjadi produk bernilai tambah untuk memenuhi kebutuhan pasar domestik maupun ekspor. Pengembangan fasilitas dapat mencakup pengolahan, penyimpanan, pengemasan, dan distribusi produk hasil laut.',
                'luas_lahan' => 22.50,
                'satuan_luas' => 'Ha',
                'lokasi' => 'Zona Industri Maritim, KITB',
                'status' => 'tersedia',
                'nilai_investasi' => 300000000000,
                'mata_uang' => 'IDR',
                'gambar' => null,
                'urutan' => 9,
                'aktif' => true,
            ],
            [
                'judul' => 'Kawasan Industri Halal',
                'sektor_industri' => 'Industri Halal',
                'deskripsi' => 'Pengembangan kawasan yang mendukung kegiatan industri berbasis produk halal dengan fasilitas produksi, pengolahan, penyimpanan, dan distribusi yang terintegrasi. Konsep pengembangan diarahkan untuk membangun ekosistem industri yang mendukung berbagai sektor usaha halal.',
                'luas_lahan' => 40.00,
                'satuan_luas' => 'Ha',
                'lokasi' => 'Zona Pengembangan, KITB',
                'status' => 'perencanaan',
                'nilai_investasi' => 1200000000000,
                'mata_uang' => 'IDR',
                'gambar' => null,
                'urutan' => 10,
                'aktif' => false,
            ],
        ];

        foreach ($data as $item) {
            $item['slug'] = Str::slug($item['judul']);

            PeluangInvestasi::create($item);
        }
    }
}
