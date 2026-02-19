<?php

namespace Database\Seeders;

use App\Models\Grading;
use Illuminate\Database\Seeder;

class GradingSeeder extends Seeder
{
    public function run(): void
    {
        Grading::create(['client_id' => 1, 'name' => 'Pass/Fail', 'type' => 'passfail', 'scale' => ['Pass', 'Fail'], 'pass_value' => 'Pass']);
        Grading::create(['client_id' => 1, 'name' => '1-5 Scale', 'type' => 'numeric', 'scale' => ['1', '2', '3', '4', '5'], 'pass_value' => '3']);
    }
}
