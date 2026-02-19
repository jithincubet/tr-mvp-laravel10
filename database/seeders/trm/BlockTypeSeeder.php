<?php

namespace Database\Seeders;

use App\Models\BlockType;
use Illuminate\Database\Seeder;

class BlockTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Briefing', 'Exercise', 'Assessment', 'Debrief', 'General'];
        foreach ($types as $name) {
            BlockType::create(['client_id' => 1, 'name' => $name]);
        }
    }
}
