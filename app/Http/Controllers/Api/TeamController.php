<?php

namespace App\Http\Controllers\Api;

use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Team Controller
 * Manages teams/groups for organizing users
 */
class TeamController extends BaseController
{
    /**
     * GET /api/v1/teams
     * List all teams for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = Team::where('client_id', $this->getClientId())
            ->withCount('users')
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
     * POST /api/v1/teams
     * Create a new team
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'nullable|string|max:50',
            'disabled' => 'boolean',
        ]);

        $validated['client_id'] = $this->getClientId();

        $team = Team::create($validated);

        return $this->success($team, 'Team created successfully', 201);
    }

    /**
     * GET /api/v1/teams/{team}
     * Get team details with users
     */
    public function show(Team $team): JsonResponse
    {
        $this->authorizeClientAccess($team);

        return $this->success(
            $team->load(['users'])
        );
    }

    /**
     * PUT /api/v1/teams/{team}
     * Update team
     */
    public function update(Request $request, Team $team): JsonResponse
    {
        $this->authorizeClientAccess($team);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'nullable|string|max:50',
            'disabled' => 'boolean',
        ]);

        $team->update($validated);

        return $this->success($team, 'Team updated successfully');
    }

    /**
     * DELETE /api/v1/teams/{team}
     * Delete team
     */
    public function destroy(Team $team): JsonResponse
    {
        $this->authorizeClientAccess($team);

        $team->users()->detach();
        $team->delete();

        return $this->success(null, 'Team deleted successfully');
    }

    private function authorizeClientAccess(Team $team): void
    {
        if ($team->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
