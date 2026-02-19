<?php

namespace Database\Seeders;

use App\Models\EndorsementType;
use Illuminate\Database\Seeder;

class EndorsementTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Type Rating', 'scheme' => 'aircraft'],
            ['name' => 'Recurrent Training', 'scheme' => 'recurrent'],
            ['name' => 'Line Training', 'scheme' => 'line'],
            ['name' => 'Certification', 'scheme' => 'certification'],
        ];
        foreach ($types as $type) {
            EndorsementType::create(array_merge($type, ['client_id' => 1]));
        }
    }
}
