<?php

namespace App\Http\Controllers\Api;

use App\Models\TrainingFacility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Training Facility Controller
 * Manages training facilities/locations/devices
 */
class TrainingFacilityController extends BaseController
{
    /**
     * GET /api/v1/training-facilities
     * List all training facilities for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = TrainingFacility::where('client_id', $this->getClientId())
            ->with('facilityType:id,name')
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
            )
            ->when($request->type_id, fn($q, $typeId) => 
                $q->where('facility_type_id', $typeId)
            )
            ->when($request->disabled !== null, fn($q) => 
                $q->where('disabled', $request->boolean('disabled'))
            )
            ->orderBy($request->sort ?? 'name', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/training-facilities
     * Create a new training facility
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'facility_type_id' => 'nullable|integer|exists:facility_types,id',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'disabled' => 'boolean',
        ]);

        $validated['client_id'] = $this->getClientId();

        $facility = TrainingFacility::create($validated);

        return $this->success($facility->load('facilityType'), 'Training facility created successfully', 201);
    }

    /**
     * GET /api/v1/training-facilities/{trainingFacility}
     * Get training facility details
     */
    public function show(TrainingFacility $trainingFacility): JsonResponse
    {
        $this->authorizeClientAccess($trainingFacility);

        return $this->success(
            $trainingFacility->load('facilityType')
        );
    }

    /**
     * PUT /api/v1/training-facilities/{trainingFacility}
     * Update training facility
     */
    public function update(Request $request, TrainingFacility $trainingFacility): JsonResponse
    {
        $this->authorizeClientAccess($trainingFacility);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'facility_type_id' => 'nullable|integer|exists:facility_types,id',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'disabled' => 'boolean',
        ]);

        $trainingFacility->update($validated);

        return $this->success($trainingFacility, 'Training facility updated successfully');
    }

    /**
     * DELETE /api/v1/training-facilities/{trainingFacility}
     * Delete training facility
     */
    public function destroy(TrainingFacility $trainingFacility): JsonResponse
    {
        $this->authorizeClientAccess($trainingFacility);

        // TODO: Check if events are using this facility
        $trainingFacility->delete();

        return $this->success(null, 'Training facility deleted successfully');
    }

    private function authorizeClientAccess(TrainingFacility $trainingFacility): void
    {
        if ($trainingFacility->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
