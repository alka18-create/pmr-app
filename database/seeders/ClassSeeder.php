<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'X IPA 1', 'level' => 'X', 'year' => 2026],
            ['name' => 'X IPA 2', 'level' => 'X', 'year' => 2026],
            ['name' => 'XI IPA 1', 'level' => 'XI', 'year' => 2026],
            ['name' => 'XI IPS 1', 'level' => 'XI', 'year' => 2026],
            ['name' => 'XII IPA 1', 'level' => 'XII', 'year' => 2026],
            ['name' => 'XII IPS 1', 'level' => 'XII', 'year' => 2026],
        ];

        foreach ($rows as $row) {
            SchoolClass::firstOrCreate(['name' => $row['name']], $row);
        }
    }
}
