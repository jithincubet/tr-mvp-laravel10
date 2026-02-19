<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExposureTypeSeeder extends Seeder
{
    public function run(): void
    {
        $exposureTypes = [
            [
                'name' => 'Night Landing',
                'code' => 'NIGHT_LAND',
                'description' => 'Landing during night operations',
                'category' => 'landing',
            ],
            [
                'name' => 'CAT II Approach',
                'code' => 'CAT_II',
                'description' => 'Category II instrument approach',
                'category' => 'approach',
            ],
            [
                'name' => 'CAT III Approach',
                'code' => 'CAT_III',
                'description' => 'Category III instrument approach',
                'category' => 'approach',
            ],
            [
                'name' => 'Crosswind Landing',
                'code' => 'XWIND_LAND',
                'description' => 'Landing with significant crosswind component',
                'category' => 'landing',
            ],
            [
                'name' => 'Low Visibility Takeoff',
                'code' => 'LVTO',
                'description' => 'Takeoff in low visibility conditions',
                'category' => 'takeoff',
            ],
            [
                'name' => 'RNAV Approach',
                'code' => 'RNAV',
                'description' => 'Area navigation approach',
                'category' => 'approach',
            ],
            [
                'name' => 'ILS Approach',
                'code' => 'ILS',
                'description' => 'Instrument Landing System approach',
                'category' => 'approach',
            ],
            [
                'name' => 'Visual Approach',
                'code' => 'VISUAL',
                'description' => 'Visual approach procedure',
                'category' => 'approach',
            ],
            [
                'name' => 'Circling Approach',
                'code' => 'CIRCLING',
                'description' => 'Circling approach maneuver',
                'category' => 'approach',
            ],
            [
                'name' => 'Go-Around',
                'code' => 'GO_AROUND',
                'description' => 'Missed approach/go-around execution',
                'category' => 'maneuver',
            ],
            [
                'name' => 'Windshear Encounter',
                'code' => 'WINDSHEAR',
                'description' => 'Windshear encounter or escape maneuver',
                'category' => 'abnormal',
            ],
            [
                'name' => 'Engine Failure',
                'code' => 'ENG_FAIL',
                'description' => 'Engine failure handling',
                'category' => 'emergency',
            ],
        ];

        foreach ($exposureTypes as $type) {
            DB::table('tr2_exposure_types')->updateOrInsert(
                ['code' => $type['code'], 'client_id' => 1],
                array_merge($type, [
                    'client_id' => 1,
                    'enabled' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
