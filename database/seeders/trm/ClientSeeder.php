<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::create([
            'name' => 'Default Client',
            'name_legal' => 'Default Client Ltd',
            'subdomain' => 'default',
            'status' => 'active',
            'country' => 'GB',
            'currency' => 'GBP',
        ]);
    }
}
