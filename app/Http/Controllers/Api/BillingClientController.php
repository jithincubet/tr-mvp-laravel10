<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Billing Client Controller
 * Manages billing-specific client data
 */
class BillingClientController extends BaseController
{
    /**
     * GET /api/v1/billing/clients
     * List all billing clients
     */
    public function index(Request $request): JsonResponse
    {
        $clients = Client::query()
            ->select([
                'id', 'name', 'email_invoice', 'billing_type', 'currency',
                'vat_rate', 'vat', 'address', 'address_1', 'address_2', 'address_3',
                'city', 'postal_code', 'country', 'vat_number',
                'your_reference', 'excluded_from_invoicing', 'created_at',
            ])
            ->orderBy('name')
            ->get();

        return $this->success($clients);
    }

    /**
     * GET /api/v1/billing/clients/{id}
     * Get a single billing client
     */
    public function show(Client $client): JsonResponse
    {
        return $this->success($client);
    }

    /**
     * POST /api/v1/billing/clients
     * Create a new billing client
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email_invoice' => 'nullable|email|max:255',
            'billing_type' => 'nullable|string|in:billing_records,tiers,both',
            'currency' => 'nullable|string|max:3',
        ]);

        $client = Client::create($validated);

        return $this->success($client, 'Client created successfully', 201);
    }

    /**
     * PUT /api/v1/billing/clients/{id}
     * Update a billing client
     */
    public function update(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email_invoice' => 'nullable|email|max:255',
            'billing_type' => 'nullable|string|in:billing_records,tiers,both',
            'currency' => 'nullable|string|max:3',
            'vat_rate' => 'nullable|numeric|min:0',
            'vat' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'address_1' => 'nullable|string|max:500',
            'address_2' => 'nullable|string|max:500',
            'address_3' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:50',
            'your_reference' => 'nullable|string|max:255',
            'excluded_from_invoicing' => 'nullable|boolean',
        ]);

        $client->update($validated);

        return $this->success($client, 'Client updated successfully');
    }

    /**
     * DELETE /api/v1/billing/clients/{id}
     * Delete a billing client
     */
    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return $this->success(null, 'Client deleted successfully');
    }

    /**
     * PATCH /api/v1/billing/clients/{id}
     * Toggle invoicing exclusion
     */
    public function updateExclusion(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'excluded_from_invoicing' => 'required|boolean',
        ]);

        $client->update($validated);

        return $this->success(null, 'Client exclusion updated');
    }
}