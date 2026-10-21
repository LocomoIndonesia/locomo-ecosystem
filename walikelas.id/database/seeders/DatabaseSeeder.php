<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            CurriculumSeeder::class,
            MissionSeeder::class,
            SyntheticLearnersSeeder::class,
        ]);
    }
}
