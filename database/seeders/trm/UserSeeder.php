<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserCredential;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'client_id' => 1,
            'email' => 'admin@example.com',
            'first_name' => 'System',
            'last_name' => 'Administrator',
        ]);

        UserCredential::create([
            'user_id' => $admin->id,
            'password_hash' => Hash::make('password'),
        ]);
    }
}
