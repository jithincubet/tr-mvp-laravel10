<?php

namespace App\Http\Controllers\Api;

use App\Models\Endorsement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endorsement Controller
 * Manages endorsements/qualifications/certifications
 */
class EndorsementController extends BaseController
{
    /**
     * GET /api/v1/endorsements
     * List all endorsements for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = Endorsement::where('client_id', $this->getClientId())
            ->with(['type', 'schedule', 'form'])
            ->when($request->type_id, fn($q, $typeId) => $q->where('type_id', $typeId))
            ->when($request->disabled !== null, fn($q) => $q->where('disabled', $request->disabled))
            ->orderBy($request->sort ?? 'name', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/endorsements
     * Create a new endorsement
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tr2_endorsements,code',
            'description' => 'nullable|string|max:1000',
            'type_id' => 'nullable|integer|exists:tr2_endorsement_types,id',
            'schedule_id' => 'nullable|integer|exists:tr2_endorsement_schedules,id',
            'form_id' => 'nullable|integer|exists:tr2_endorsement_forms,id',
            'training_id' => 'nullable|integer|exists:tr2_training,id',
            'disabled' => 'boolean',
            'enrollment' => 'boolean',
        ]);

        $validated['client_id'] = $this->getClientId();

        $endorsement = Endorsement::create($validated);

        return $this->success(
            $endorsement->load(['type', 'schedule', 'form']),
            'Endorsement created successfully',
            201
        );
    }

    /**
     * GET /api/v1/endorsements/{endorsement}
     * Get endorsement details
     */
    public function show(Endorsement $endorsement): JsonResponse
    {
        $this->authorizeClientAccess($endorsement);

        return $this->success(
            $endorsement->load(['type', 'schedule', 'form', 'training'])
        );
    }

    /**
     * PUT /api/v1/endorsements/{endorsement}
     * Update endorsement
     */
    public function update(Request $request, Endorsement $endorsement): JsonResponse
    {
        $this->authorizeClientAccess($endorsement);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => "string|max:50|unique:tr2_endorsements,code,{$endorsement->id}",
            'description' => 'nullable|string|max:1000',
            'type_id' => 'nullable|integer|exists:tr2_endorsement_types,id',
            'schedule_id' => 'nullable|integer|exists:tr2_endorsement_schedules,id',
            'form_id' => 'nullable|integer|exists:tr2_endorsement_forms,id',
            'training_id' => 'nullable|integer|exists:tr2_training,id',
            'disabled' => 'boolean',
            'enrollment' => 'boolean',
        ]);

        $endorsement->update($validated);

        return $this->success($endorsement, 'Endorsement updated successfully');
    }

    /**
     * DELETE /api/v1/endorsements/{endorsement}
     * Soft delete endorsement
     */
    public function destroy(Endorsement $endorsement): JsonResponse
    {
        $this->authorizeClientAccess($endorsement);

        // Check for active currencies before deletion
        $activeCurrencies = $endorsement->currencies()->where('current', true)->count();
        if ($activeCurrencies > 0) {
            return $this->error(
                "Cannot delete endorsement with {$activeCurrencies} active currencies",
                'HAS_DEPENDENCIES',
                409
            );
        }

        $endorsement->delete();

        return $this->success(null, 'Endorsement deleted successfully');
    }

    private function authorizeClientAccess(Endorsement $endorsement): void
    {
        if ($endorsement->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
