<?php

namespace App\Http\Controllers\Api;

use App\Models\TierBracket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tier Bracket Controller
 * Manages pricing brackets within tiers
 */
class TierBracketController extends BaseController
{
    /**
     * GET /api/v1/billing/tier-brackets
     */
    public function index(Request $request): JsonResponse
    {
        $query = TierBracket::query()
            ->when($request->tier_id, fn($q, $tierId) => $q->where('tier_id', $tierId));

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/billing/tier-brackets
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tier_id' => 'required|integer|exists:sub_tiers,id',
            'min_users' => 'required|integer|min:0',
            'max_users' => 'nullable|integer|min:1',
            'price_unit' => 'required|numeric|min:0',
            'price_flat_fee' => 'required|numeric|min:0',
        ]);

        $bracket = TierBracket::create($validated);

        return $this->success($bracket, 'Tier bracket created successfully', 201);
    }

    /**
     * PUT /api/v1/billing/tier-brackets/{id}
     */
    public function update(Request $request, TierBracket $tierBracket): JsonResponse
    {
        $validated = $request->validate([
            'tier_id' => 'sometimes|integer|exists:sub_tiers,id',
            'min_users' => 'sometimes|integer|min:0',
            'max_users' => 'nullable|integer|min:1',
            'price_unit' => 'sometimes|numeric|min:0',
            'price_flat_fee' => 'sometimes|numeric|min:0',
        ]);

        $tierBracket->update($validated);

        return $this->success($tierBracket, 'Tier bracket updated successfully');
    }

    /**
     * DELETE /api/v1/billing/tier-brackets/{id}
     */
    public function destroy(TierBracket $tierBracket): JsonResponse
    {
        $tierBracket->delete();

        return $this->success(null, 'Tier bracket deleted successfully');
    }
}