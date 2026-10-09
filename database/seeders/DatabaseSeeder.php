<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            UserSeeder::class,
            RoleSeeder::class,
            BatchSeeder::class,
            ClassSeeder::class,
            PositionSeeder::class,
            EventSeeder::class,
            ArticleSeeder::class,
            GallerySeeder::class,
            AchievementSeeder::class,
            AcademySeeder::class,
            DemoSeeder::class,
        ]);
    }
}
