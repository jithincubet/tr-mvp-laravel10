<?php

namespace App\Http\Controllers\Api;

use App\Models\ClientProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Client Product Controller
 * Manages client-product subscription assignments
 */
class ClientProductController extends BaseController
{
    /**
     * GET /api/v1/billing/client-products
     */
    public function index(): JsonResponse
    {
        return $this->success(ClientProduct::all());
    }

    /**
     * POST /api/v1/billing/client-products
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|integer|exists:tr2_clients,id',
            'product_id' => 'required|integer|exists:sub_products,id',
            'tier_id' => 'nullable|integer|exists:sub_tiers,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $clientProduct = ClientProduct::create($validated);

        return $this->success($clientProduct, 'Client product created successfully', 201);
    }

    /**
     * PUT /api/v1/billing/client-products/{id}
     */
    public function update(Request $request, ClientProduct $clientProduct): JsonResponse
    {
        $validated = $request->validate([
            'tier_id' => 'nullable|integer|exists:sub_tiers,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'enabled' => 'nullable|boolean',
        ]);

        $clientProduct->update($validated);

        return $this->success($clientProduct, 'Client product updated successfully');
    }

    /**
     * DELETE /api/v1/billing/client-products/{id}
     */
    public function destroy(ClientProduct $clientProduct): JsonResponse
    {
        $clientProduct->delete();

        return $this->success(null, 'Client product deleted successfully');
    }
}