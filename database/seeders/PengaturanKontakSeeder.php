<?php

namespace Database\Seeders;

use App\Models\PengaturanKontak;
use Illuminate\Database\Seeder;

class PengaturanKontakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PengaturanKontak::updateOrCreate(
            ['id' => 1],
            [
                // Identitas & alamat
                'nama_perusahaan' => 'PT Kawasan Industri Tanjung Buton',
                'alamat' => 'Kawasan Industri Tanjung Buton, Kabupaten Siak, Provinsi Riau, Indonesia',

                // Kontak
                'telepon' => '+62 761 123456',
                'whatsapp' => '+62 812 3456 7890',
                'email' => 'info@tanjungbuton-industrial.co.id',
                'email_investor' => 'investor@tanjungbuton-industrial.co.id',

                // Jam operasional
                'jam_operasional' => 'Senin - Jumat, 08.00 - 16.00 WIB',

                // Koordinat dummy / lokasi KITB
                'latitude' => 0.9321000,
                'longitude' => 102.1425000,

                // Google Maps Embed
                'maps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d10000!2d102.1425!3d0.9321',

                // Media sosial
                'facebook' => 'https://www.facebook.com/',
                'instagram' => 'https://www.instagram.com/',
                'linkedin' => 'https://www.linkedin.com/',
                'youtube' => 'https://www.youtube.com/',
            ]
        );
    }
}
