<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            [
                'name' => 'Flight Operations',
                'is_default' => true,
                'is_locked' => false,
            ],
            [
                'name' => 'Cabin Crew',
                'is_default' => false,
                'is_locked' => false,
            ],
            [
                'name' => 'Ground Operations',
                'is_default' => false,
                'is_locked' => false,
            ],
            [
                'name' => 'Maintenance',
                'is_default' => false,
                'is_locked' => false,
            ],
            [
                'name' => 'Training Department',
                'is_default' => false,
                'is_locked' => true,
            ],
        ];

        foreach ($divisions as $division) {
            DB::table('tr2_divisions')->updateOrInsert(
                ['name' => $division['name'], 'client_id' => 1],
                array_merge($division, [
                    'client_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
