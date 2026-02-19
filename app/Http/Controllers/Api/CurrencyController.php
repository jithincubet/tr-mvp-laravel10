<?php

namespace App\Http\Controllers\Api;

use App\Models\EmsCurrency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Currency Controller
 * Manages user qualifications/certifications (EMS Currencies)
 */
class CurrencyController extends BaseController
{
    /**
     * GET /api/v1/currencies
     * List currencies with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = EmsCurrency::where('client_id', $this->getClientId())
            ->with(['user', 'endorsement.type', 'endorsement.schedule'])
            ->when($request->user_id, fn($q, $userId) => $q->where('user_id', $userId))
            ->when($request->endorsement_id, fn($q, $id) => $q->where('endorsement_id', $id))
            ->when($request->current !== null, fn($q) => $q->where('current', $request->current))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->expiring_within, function($q, $days) {
                $q->where('date_expired', '<=', now()->addDays($days))
                  ->where('date_expired', '>=', now());
            })
            ->when($request->expired, fn($q) => $q->where('date_expired', '<', now()))
            ->orderBy($request->sort ?? 'date_expired', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/currencies
     * Create a new currency record
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:tr2_users,id',
            'endorsement_id' => 'required|integer|exists:tr2_endorsements,id',
            'date_qualified' => 'nullable|date',
            'date_expired' => 'nullable|date|after:date_qualified',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
            'reference' => 'nullable|string|max:255',
            'current' => 'boolean',
            'passes' => 'boolean',
            'fails' => 'boolean',
        ]);

        $validated['client_id'] = $this->getClientId();
        $validated['current'] = $validated['current'] ?? true;

        $currency = EmsCurrency::create($validated);

        return $this->success(
            $currency->load(['user', 'endorsement']),
            'Currency created successfully',
            201
        );
    }

    /**
     * GET /api/v1/currencies/{currency}
     * Get currency details with documents and responses
     */
    public function show(EmsCurrency $currency): JsonResponse
    {
        $this->authorizeClientAccess($currency);

        return $this->success(
            $currency->load(['user', 'endorsement.type', 'endorsement.schedule', 'documents', 'responses'])
        );
    }

    /**
     * PUT /api/v1/currencies/{currency}
     * Update currency record
     */
    public function update(Request $request, EmsCurrency $currency): JsonResponse
    {
        $this->authorizeClientAccess($currency);

        $validated = $request->validate([
            'date_qualified' => 'nullable|date',
            'date_expired' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
            'reference' => 'nullable|string|max:255',
            'current' => 'boolean',
            'passes' => 'boolean',
            'fails' => 'boolean',
            'disabled' => 'boolean',
        ]);

        $currency->update($validated);

        return $this->success($currency, 'Currency updated successfully');
    }

    /**
     * DELETE /api/v1/currencies/{currency}
     * Soft delete currency
     */
    public function destroy(EmsCurrency $currency): JsonResponse
    {
        $this->authorizeClientAccess($currency);

        $currency->delete();

        return $this->success(null, 'Currency deleted successfully');
    }

    private function authorizeClientAccess(EmsCurrency $currency): void
    {
        if ($currency->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
