<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Config Controller
 * Manages system configuration settings
 */
class ConfigController extends BaseController
{
    /**
     * GET /api/v1/config
     * Get system configuration for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $clientId = $this->getClientId();

        // Fetch client-specific configuration
        $client = \App\Models\Client::find($clientId);

        $config = [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'subdomain' => $client->subdomain,
                'date_format' => $client->date_format ?? 'Y-m-d',
                'currency' => $client->currency ?? 'USD',
            ],
            'features' => [
                'branches_enabled' => $client->branches_enabled ?? false,
                'divisions_enabled' => $client->divisions_enabled ?? false,
                'progression_enabled' => $client->progression_enabled ?? false,
                'two_factor_auth_enabled' => $client->two_factor_auth_enabled ?? false,
                'facial_verification_enabled' => $client->facial_verification_enabled ?? false,
                'author_area_enabled' => $client->author_area_enabled ?? false,
                'continuous_training' => $client->continuous_training ?? false,
            ],
            'training' => [
                'days_overdue_until' => $client->days_overdue_until ?? 30,
                'gdpr_expire_months' => $client->gdpr_expire_months ?? 36,
                'one_time_training_expires_option' => $client->one_time_training_expires_option ?? false,
            ],
            'branding' => [
                'logo_main' => $client->logo_main,
                'logo_icon' => $client->logo_icon,
                'company_logo_enabled' => $client->company_logo_enabled ?? false,
            ],
        ];

        return $this->success($config);
    }

    /**
     * PUT /api/v1/config
     * Update system configuration
     */
    public function update(Request $request): JsonResponse
    {
        $clientId = $this->getClientId();
        $client = \App\Models\Client::findOrFail($clientId);

        $validated = $request->validate([
            'date_format' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:3',
            'branches_enabled' => 'boolean',
            'divisions_enabled' => 'boolean',
            'progression_enabled' => 'boolean',
            'two_factor_auth_enabled' => 'boolean',
            'facial_verification_enabled' => 'boolean',
            'author_area_enabled' => 'boolean',
            'continuous_training' => 'boolean',
            'days_overdue_until' => 'integer|min:1|max:365',
            'gdpr_expire_months' => 'integer|min:1|max:120',
            'one_time_training_expires_option' => 'boolean',
            'logo_main' => 'nullable|string|max:500',
            'logo_icon' => 'nullable|string|max:500',
            'company_logo_enabled' => 'boolean',
        ]);

        $client->update($validated);

        return $this->success(null, 'Configuration updated successfully');
    }
}
