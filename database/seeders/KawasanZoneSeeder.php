<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KawasanZoneSeeder extends Seeder
{
    public function run(): void
    {
        $planId = DB::table('master_plans')->insertGetId([
            'judul' => 'Peta tahapan pengembangan & luas per zona',
            'gambar_path' => 'master-plan/master-plan-kitb.png', // salin file ke storage/app/public/master-plan/
            'keterangan' => 'Development Phase Concept Plan Map, skala 1:65.360 (LPPM UIR).',
            'total_luas_ha' => 6070,
            'urutan' => 1,
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rows = [
            ['phase-1',    'Phase 1',    340,  '#7cc4dc', null],
            ['phase-2',    'Phase 2',    723,  '#7b4a4f', null],
            ['phase-3',    'Phase 3',    481,  '#2f3f8f', null],
            ['phase-4',    'Phase 4',    344,  '#6bbf3b', null],
            ['phase-5',    'Phase 5',    472,  '#d9743b', null],
            ['phase-6',    'Phase 6',    993,  '#1f8f80', null],
            ['phase-7',    'Phase 7',    747,  '#e05fb0', null],
            ['supporting', 'Supporting', 1915, '#8a8f2a', null],
            ['port',       'Port',       270,  '#b7e3ef', 'Joint Venture'],
        ];

        foreach ($rows as $i => [$kode, $label, $luas, $warna, $catatan]) {
            DB::table('kawasan_zones')->updateOrInsert(
                ['master_plan_id' => $planId, 'kode' => $kode],
                [
                    'label' => $label,
                    'luas_ha' => $luas,
                    'warna' => $warna,
                    'catatan' => $catatan,
                    'urutan' => $i + 1,
                    'aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }
}
