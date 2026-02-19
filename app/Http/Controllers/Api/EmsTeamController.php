<?php

namespace App\Http\Controllers\Api;

use App\Models\EmsTeam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * EMS Team Controller
 * Manages EMS (Emergency Medical Services) team configuration
 */
class EmsTeamController extends BaseController
{
    /**
     * GET /api/v1/ems-teams
     * List all EMS teams for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = EmsTeam::where('client_id', $this->getClientId())
            ->with(['members:id,name,email'])
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
     * PUT /api/v1/ems-teams/{emsTeam}
     * Update EMS team configuration
     */
    public function update(Request $request, EmsTeam $emsTeam): JsonResponse
    {
        $this->authorizeClientAccess($emsTeam);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'base_location' => 'nullable|string|max:255',
            'aircraft_type' => 'nullable|string|max:100',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'integer|exists:users,id',
            'disabled' => 'boolean',
            'properties' => 'nullable|array',
        ]);

        // Handle member sync if provided
        if (isset($validated['member_ids'])) {
            $emsTeam->members()->sync($validated['member_ids']);
            unset($validated['member_ids']);
        }

        $emsTeam->update($validated);

        return $this->success(
            $emsTeam->load('members'),
            'EMS team updated successfully'
        );
    }

    private function authorizeClientAccess(EmsTeam $emsTeam): void
    {
        if ($emsTeam->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
