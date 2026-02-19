<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Feature;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Administrator', 'code' => 'admin', 'color' => '#EF4444'],
            ['name' => 'Instructor', 'code' => 'instructor', 'color' => '#3B82F6'],
            ['name' => 'User', 'code' => 'user', 'color' => '#10B981', 'is_default' => true],
        ];

        foreach ($roles as $role) {
            Role::create(array_merge($role, ['client_id' => 1]));
        }

        $features = [
            ['name' => 'View Users', 'code' => 'users.view', 'category' => 'users'],
            ['name' => 'Manage Users', 'code' => 'users.manage', 'category' => 'users'],
            ['name' => 'View Events', 'code' => 'events.view', 'category' => 'events'],
            ['name' => 'Manage Events', 'code' => 'events.manage', 'category' => 'events'],
            ['name' => 'Approve Sessions', 'code' => 'sessions.approve', 'category' => 'sessions'],
        ];

        foreach ($features as $feature) {
            Feature::create(array_merge($feature, ['client_id' => 1]));
        }
    }
}
