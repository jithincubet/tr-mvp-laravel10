<?php

namespace App\Http\Controllers\Api;

use App\Models\Tier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tier Controller
 * Manages product pricing tiers
 */
class TierController extends BaseController
{
    /**
     * GET /api/v1/billing/tiers
     */
    public function index(): JsonResponse
    {
        return $this->success(Tier::all());
    }

    /**
     * POST /api/v1/billing/tiers
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:sub_products,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'tier_type' => 'required|string|in:standard,volume,graduated',
        ]);

        $tier = Tier::create($validated);

        return $this->success($tier, 'Tier created successfully', 201);
    }

    /**
     * PUT /api/v1/billing/tiers/{id}
     */
    public function update(Request $request, Tier $tier): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|integer|exists:sub_products,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
            'tier_type' => 'sometimes|string|in:standard,volume,graduated',
        ]);

        $tier->update($validated);

        return $this->success($tier, 'Tier updated successfully');
    }

    /**
     * DELETE /api/v1/billing/tiers/{id}
     */
    public function destroy(Tier $tier): JsonResponse
    {
        $tier->delete();

        return $this->success(null, 'Tier deleted successfully');
    }
}