<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Ketua', 'level' => 1, 'description' => 'Ketua umum organisasi'],
            ['name' => 'Wakil Ketua', 'level' => 2, 'description' => 'Wakil ketua organisasi'],
            ['name' => 'Sekretaris', 'level' => 3, 'description' => 'Sekretaris organisasi'],
            ['name' => 'Bendahara', 'level' => 4, 'description' => 'Bendahara organisasi'],
            ['name' => 'Koordinator Bidang Kesehatan', 'level' => 5, 'description' => 'Koordinator bidang kesehatan'],
            ['name' => 'Koordinator Bidang Pelatihan', 'level' => 6, 'description' => 'Koordinator bidang pelatihan'],
            ['name' => 'Anggota', 'level' => 9, 'description' => 'Anggota biasa'],
        ];

        foreach ($rows as $row) {
            Position::firstOrCreate(['name' => $row['name']], $row);
        }
    }
}
