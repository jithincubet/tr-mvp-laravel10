<?php

namespace App\Http\Controllers\Api;

use App\Models\AircraftType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Aircraft Type Controller
 * Manages aircraft type definitions
 */
class AircraftTypeController extends BaseController
{
    /**
     * GET /api/v1/aircraft-types
     * List all aircraft types for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = AircraftType::where('client_id', $this->getClientId())
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
            )
            ->when($request->category, fn($q, $category) => 
                $q->where('category', $category)
            )
            ->orderBy($request->sort ?? 'name', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/aircraft-types
     * Create a new aircraft type
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20',
            'category' => 'nullable|string|max:100',
        ]);

        $validated['client_id'] = $this->getClientId();

        $aircraftType = AircraftType::create($validated);

        return $this->success($aircraftType, 'Aircraft type created successfully', 201);
    }

    /**
     * GET /api/v1/aircraft-types/{aircraftType}
     * Get aircraft type details
     */
    public function show(AircraftType $aircraftType): JsonResponse
    {
        $this->authorizeClientAccess($aircraftType);

        return $this->success($aircraftType);
    }

    /**
     * PUT /api/v1/aircraft-types/{aircraftType}
     * Update aircraft type
     */
    public function update(Request $request, AircraftType $aircraftType): JsonResponse
    {
        $this->authorizeClientAccess($aircraftType);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => 'string|max:20',
            'category' => 'nullable|string|max:100',
        ]);

        $aircraftType->update($validated);

        return $this->success($aircraftType, 'Aircraft type updated successfully');
    }

    /**
     * DELETE /api/v1/aircraft-types/{aircraftType}
     * Delete aircraft type
     */
    public function destroy(AircraftType $aircraftType): JsonResponse
    {
        $this->authorizeClientAccess($aircraftType);

        // TODO: Check if sessions are using this aircraft type
        $aircraftType->delete();

        return $this->success(null, 'Aircraft type deleted successfully');
    }

    private function authorizeClientAccess(AircraftType $aircraftType): void
    {
        if ($aircraftType->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
