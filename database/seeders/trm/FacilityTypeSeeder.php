<?php

namespace Database\Seeders;

use App\Models\FacilityType;
use Illuminate\Database\Seeder;

class FacilityTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Simulator', 'Classroom', 'Aircraft', 'Online'];
        foreach ($types as $name) {
            FacilityType::create(['client_id' => 1, 'name' => $name]);
        }
    }
}
