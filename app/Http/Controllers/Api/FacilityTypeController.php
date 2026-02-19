<?php

namespace App\Http\Controllers\Api;

use App\Models\FacilityType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Facility Type Controller
 * Manages facility type/category definitions
 */
class FacilityTypeController extends BaseController
{
    /**
     * GET /api/v1/facility-types
     * List all facility types for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = FacilityType::where('client_id', $this->getClientId())
            ->withCount('facilities')
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
            )
            ->orderBy($request->sort ?? 'name', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/facility-types
     * Create a new facility type
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'code' => 'nullable|string|max:50',
        ]);

        $validated['client_id'] = $this->getClientId();

        $facilityType = FacilityType::create($validated);

        return $this->success($facilityType, 'Facility type created successfully', 201);
    }

    /**
     * GET /api/v1/facility-types/{facilityType}
     * Get facility type details
     */
    public function show(FacilityType $facilityType): JsonResponse
    {
        $this->authorizeClientAccess($facilityType);

        return $this->success(
            $facilityType->load('facilities')
        );
    }

    /**
     * PUT /api/v1/facility-types/{facilityType}
     * Update facility type
     */
    public function update(Request $request, FacilityType $facilityType): JsonResponse
    {
        $this->authorizeClientAccess($facilityType);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'code' => 'nullable|string|max:50',
        ]);

        $facilityType->update($validated);

        return $this->success($facilityType, 'Facility type updated successfully');
    }

    /**
     * DELETE /api/v1/facility-types/{facilityType}
     * Delete facility type
     */
    public function destroy(FacilityType $facilityType): JsonResponse
    {
        $this->authorizeClientAccess($facilityType);

        // TODO: Check if facilities are using this type
        $facilityType->delete();

        return $this->success(null, 'Facility type deleted successfully');
    }

    private function authorizeClientAccess(FacilityType $facilityType): void
    {
        if ($facilityType->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
