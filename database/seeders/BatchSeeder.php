<?php

namespace Database\Seeders;

use App\Models\Batch;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Angkatan 2024', 'year' => 2024],
            ['name' => 'Angkatan 2025', 'year' => 2025],
            ['name' => 'Angkatan 2026', 'year' => 2026],
        ];

        foreach ($rows as $row) {
            Batch::firstOrCreate(['name' => $row['name']], $row);
        }
    }
}
