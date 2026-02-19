<?php

/**
 * TR2 Training Management System Configuration
 * 
 * Application-specific configuration for the training management system.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy Settings
    |--------------------------------------------------------------------------
    */

    'multi_tenancy' => [
        'enabled' => env('TR2_MULTI_TENANCY', true),
        'default_client_id' => env('TR2_DEFAULT_CLIENT_ID', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    */

    'auth' => [
        'token_expiry_hours' => env('TR2_TOKEN_EXPIRY_HOURS', 24),
        'pin_login_enabled' => env('TR2_PIN_LOGIN', false),
        'max_login_attempts' => env('TR2_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_minutes' => env('TR2_LOCKOUT_MINUTES', 15),
        'password_reset_expiry_hours' => env('TR2_PASSWORD_RESET_EXPIRY', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Endorsement & Currency Settings
    |--------------------------------------------------------------------------
    */

    'endorsements' => [
        'default_expiry_months' => env('TR2_DEFAULT_EXPIRY_MONTHS', 12),
        'warning_days_before_expiry' => env('TR2_WARNING_DAYS', 30),
        'overdue_grace_days' => env('TR2_OVERDUE_GRACE_DAYS', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Line Training Settings
    |--------------------------------------------------------------------------
    */

    'line_training' => [
        'min_sectors_required' => env('TR2_MIN_SECTORS', 4),
        'auto_release_enabled' => env('TR2_AUTO_RELEASE', false),
        'default_exposure_requirements' => [
            'NIGHT_LAND' => 2,
            'CAT_II' => 1,
            'XWIND_LAND' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'email_enabled' => env('TR2_EMAIL_NOTIFICATIONS', true),
        'in_app_enabled' => env('TR2_IN_APP_NOTIFICATIONS', true),
        'digest_frequency' => env('TR2_DIGEST_FREQUENCY', 'daily'),
        'from_email' => env('TR2_FROM_EMAIL', 'noreply@training.app'),
        'from_name' => env('TR2_FROM_NAME', 'Training Management System'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'max_file_size_mb' => env('TR2_MAX_UPLOAD_SIZE', 10),
        'allowed_extensions' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'],
        'storage_disk' => env('TR2_STORAGE_DISK', 's3'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    */

    'audit' => [
        'enabled' => env('TR2_AUDIT_ENABLED', true),
        'log_reads' => env('TR2_AUDIT_READS', false),
        'retention_days' => env('TR2_AUDIT_RETENTION', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | RBAC Settings
    |--------------------------------------------------------------------------
    */

    'rbac' => [
        'cache_permissions' => env('TR2_CACHE_PERMISSIONS', true),
        'cache_ttl_minutes' => env('TR2_PERMISSION_CACHE_TTL', 60),
        'super_admin_role' => 'master',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Defaults
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'default_per_page' => 50,
        'max_per_page' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'features' => [
        'lms_enabled' => env('TR2_LMS_ENABLED', true),
        'certificates_enabled' => env('TR2_CERTIFICATES_ENABLED', true),
        'line_training_enabled' => env('TR2_LINE_TRAINING_ENABLED', true),
        'exercises_enabled' => env('TR2_EXERCISES_ENABLED', true),
    ],

];
