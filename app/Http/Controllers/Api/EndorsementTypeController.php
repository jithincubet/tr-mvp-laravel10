<?php

namespace App\Http\Controllers\Api;

use App\Models\EndorsementType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endorsement Type Controller
 * Manages endorsement type/category definitions
 */
class EndorsementTypeController extends BaseController
{
    /**
     * GET /api/v1/endorsement-types
     * List all endorsement types for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = EndorsementType::where('client_id', $this->getClientId())
            ->withCount('endorsements')
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
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
     * POST /api/v1/endorsement-types
     * Create a new endorsement type
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'reference' => 'nullable|string|max:100',
            'scheme' => 'string|max:50',
            'learner_upload' => 'boolean',
            'is_default' => 'boolean',
            'disabled' => 'boolean',
        ]);

        $validated['client_id'] = $this->getClientId();

        $endorsementType = EndorsementType::create($validated);

        return $this->success($endorsementType, 'Endorsement type created successfully', 201);
    }

    /**
     * GET /api/v1/endorsement-types/{endorsementType}
     * Get endorsement type details
     */
    public function show(EndorsementType $endorsementType): JsonResponse
    {
        $this->authorizeClientAccess($endorsementType);

        return $this->success(
            $endorsementType->load('endorsements')
        );
    }

    /**
     * PUT /api/v1/endorsement-types/{endorsementType}
     * Update endorsement type
     */
    public function update(Request $request, EndorsementType $endorsementType): JsonResponse
    {
        $this->authorizeClientAccess($endorsementType);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'reference' => 'nullable|string|max:100',
            'scheme' => 'string|max:50',
            'learner_upload' => 'boolean',
            'is_default' => 'boolean',
            'disabled' => 'boolean',
        ]);

        $endorsementType->update($validated);

        return $this->success($endorsementType, 'Endorsement type updated successfully');
    }

    /**
     * DELETE /api/v1/endorsement-types/{endorsementType}
     * Delete endorsement type
     */
    public function destroy(EndorsementType $endorsementType): JsonResponse
    {
        $this->authorizeClientAccess($endorsementType);

        // TODO: Check if endorsements are using this type
        $endorsementType->delete();

        return $this->success(null, 'Endorsement type deleted successfully');
    }

    private function authorizeClientAccess(EndorsementType $endorsementType): void
    {
        if ($endorsementType->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
