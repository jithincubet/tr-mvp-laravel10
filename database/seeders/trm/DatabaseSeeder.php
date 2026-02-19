<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Core Setup
            ClientSeeder::class,
            RoleSeeder::class,
            FeatureSeeder::class,
            UserSeeder::class,
            DivisionSeeder::class,
            
            // Reference Data
            BlockTypeSeeder::class,
            GradingSeeder::class,
            EndorsementTypeSeeder::class,
            FacilityTypeSeeder::class,
            AircraftTypeSeeder::class,
            ExposureTypeSeeder::class,
        ]);
    }
}
