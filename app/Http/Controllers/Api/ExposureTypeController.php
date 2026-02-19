<?php

namespace App\Http\Controllers\Api;

use App\Models\ExposureType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Exposure Type Controller
 * Manages exposure type definitions for line training
 */
class ExposureTypeController extends BaseController
{
    /**
     * GET /api/v1/exposure-types
     * List all exposure types for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = ExposureType::where('client_id', $this->getClientId())
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
            )
            ->when($request->disabled !== null, fn($q) => 
                $q->where('disabled', $request->boolean('disabled'))
            )
            ->orderBy($request->sort ?? 'sortorder', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/exposure-types
     * Create a new exposure type
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'sortorder' => 'integer',
            'disabled' => 'boolean',
        ]);

        $validated['client_id'] = $this->getClientId();

        $exposureType = ExposureType::create($validated);

        return $this->success($exposureType, 'Exposure type created successfully', 201);
    }

    /**
     * GET /api/v1/exposure-types/{exposureType}
     * Get exposure type details
     */
    public function show(ExposureType $exposureType): JsonResponse
    {
        $this->authorizeClientAccess($exposureType);

        return $this->success($exposureType);
    }

    /**
     * PUT /api/v1/exposure-types/{exposureType}
     * Update exposure type
     */
    public function update(Request $request, ExposureType $exposureType): JsonResponse
    {
        $this->authorizeClientAccess($exposureType);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => 'string|max:20',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'sortorder' => 'integer',
            'disabled' => 'boolean',
        ]);

        $exposureType->update($validated);

        return $this->success($exposureType, 'Exposure type updated successfully');
    }

    /**
     * DELETE /api/v1/exposure-types/{exposureType}
     * Delete exposure type
     */
    public function destroy(ExposureType $exposureType): JsonResponse
    {
        $this->authorizeClientAccess($exposureType);

        // TODO: Check if session exposures are using this type
        $exposureType->delete();

        return $this->success(null, 'Exposure type deleted successfully');
    }

    private function authorizeClientAccess(ExposureType $exposureType): void
    {
        if ($exposureType->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
