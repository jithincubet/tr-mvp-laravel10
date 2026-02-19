<?php

namespace App\Http\Controllers\Api;

use App\Models\EndorsementSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endorsement Schedule Controller
 * Manages endorsement recurrency schedules
 */
class EndorsementScheduleController extends BaseController
{
    /**
     * GET /api/v1/endorsement-schedules
     * List all endorsement schedules for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = EndorsementSchedule::where('client_id', $this->getClientId())
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
     * POST /api/v1/endorsement-schedules
     * Create a new endorsement schedule
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'reference' => 'nullable|string|max:100',
            'expire_months' => 'integer|min:0',
            'open_days' => 'integer|min:0',
            'check_days' => 'integer|min:0',
            'cycles' => 'nullable|string',
            'once' => 'boolean',
            'resume' => 'boolean',
            'is_default' => 'boolean',
            'disabled' => 'boolean',
            'properties' => 'nullable|array',
        ]);

        $validated['client_id'] = $this->getClientId();

        $schedule = EndorsementSchedule::create($validated);

        return $this->success($schedule, 'Endorsement schedule created successfully', 201);
    }

    /**
     * GET /api/v1/endorsement-schedules/{endorsementSchedule}
     * Get endorsement schedule details
     */
    public function show(EndorsementSchedule $endorsementSchedule): JsonResponse
    {
        $this->authorizeClientAccess($endorsementSchedule);

        return $this->success(
            $endorsementSchedule->load('endorsements')
        );
    }

    /**
     * PUT /api/v1/endorsement-schedules/{endorsementSchedule}
     * Update endorsement schedule
     */
    public function update(Request $request, EndorsementSchedule $endorsementSchedule): JsonResponse
    {
        $this->authorizeClientAccess($endorsementSchedule);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'reference' => 'nullable|string|max:100',
            'expire_months' => 'integer|min:0',
            'open_days' => 'integer|min:0',
            'check_days' => 'integer|min:0',
            'cycles' => 'nullable|string',
            'once' => 'boolean',
            'resume' => 'boolean',
            'is_default' => 'boolean',
            'disabled' => 'boolean',
            'properties' => 'nullable|array',
        ]);

        $endorsementSchedule->update($validated);

        return $this->success($endorsementSchedule, 'Endorsement schedule updated successfully');
    }

    /**
     * DELETE /api/v1/endorsement-schedules/{endorsementSchedule}
     * Delete endorsement schedule
     */
    public function destroy(EndorsementSchedule $endorsementSchedule): JsonResponse
    {
        $this->authorizeClientAccess($endorsementSchedule);

        // TODO: Check if endorsements are using this schedule
        $endorsementSchedule->delete();

        return $this->success(null, 'Endorsement schedule deleted successfully');
    }

    private function authorizeClientAccess(EndorsementSchedule $endorsementSchedule): void
    {
        if ($endorsementSchedule->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
