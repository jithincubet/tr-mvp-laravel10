<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            // Dashboard
            ['code' => 'dashboard', 'name' => 'Dashboard', 'section' => 'dashboard', 'route' => '/dashboard'],
            
            // User Management
            ['code' => 'users', 'name' => 'Users', 'section' => 'user_management', 'route' => '/users'],
            ['code' => 'roles', 'name' => 'Roles', 'section' => 'user_management', 'route' => '/roles'],
            ['code' => 'teams', 'name' => 'Teams', 'section' => 'user_management', 'route' => '/teams'],
            
            // EMS - Endorsement Management
            ['code' => 'endorsements', 'name' => 'Endorsements', 'section' => 'ems', 'route' => '/endorsements'],
            ['code' => 'currencies', 'name' => 'Currencies', 'section' => 'ems', 'route' => '/currencies'],
            ['code' => 'compliance', 'name' => 'Compliance', 'section' => 'ems', 'route' => '/compliance'],
            
            // Training Forms
            ['code' => 'forms', 'name' => 'Training Forms', 'section' => 'training', 'route' => '/forms'],
            ['code' => 'blocks', 'name' => 'Training Blocks', 'section' => 'training', 'route' => '/blocks'],
            ['code' => 'events', 'name' => 'Training Events', 'section' => 'training', 'route' => '/events'],
            ['code' => 'sessions', 'name' => 'Training Sessions', 'section' => 'training', 'route' => '/sessions'],
            
            // Line Training
            ['code' => 'line_training', 'name' => 'Line Training', 'section' => 'ltm', 'route' => '/line-training'],
            ['code' => 'trainee_progress', 'name' => 'Trainee Progress', 'section' => 'ltm', 'route' => '/trainee-progress'],
            
            // LMS
            ['code' => 'lms_courses', 'name' => 'Courses', 'section' => 'lms', 'route' => '/lms/courses'],
            ['code' => 'lms_exercises', 'name' => 'Exercises', 'section' => 'lms', 'route' => '/lms/exercises'],
            
            // Reports
            ['code' => 'reports', 'name' => 'Reports', 'section' => 'reports', 'route' => '/reports'],
            ['code' => 'analytics', 'name' => 'Analytics', 'section' => 'reports', 'route' => '/analytics'],
            
            // Settings
            ['code' => 'settings', 'name' => 'Settings', 'section' => 'admin', 'route' => '/settings'],
            ['code' => 'notifications', 'name' => 'Notifications', 'section' => 'admin', 'route' => '/notifications'],
            ['code' => 'email_templates', 'name' => 'Email Templates', 'section' => 'admin', 'route' => '/email-templates'],
            ['code' => 'certificates', 'name' => 'Certificates', 'section' => 'admin', 'route' => '/certificates'],
            ['code' => 'audit_log', 'name' => 'Audit Log', 'section' => 'admin', 'route' => '/audit-log'],
            ['code' => 'rbac_matrix', 'name' => 'RBAC Matrix', 'section' => 'admin', 'route' => '/rbac-matrix'],
        ];

        foreach ($features as $feature) {
            DB::table('tr2_features')->updateOrInsert(
                ['code' => $feature['code'], 'client_id' => 1],
                array_merge($feature, [
                    'client_id' => 1,
                    'enabled' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
