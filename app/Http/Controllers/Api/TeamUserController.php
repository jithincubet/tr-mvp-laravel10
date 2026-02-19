<?php

namespace App\Http\Controllers\Api;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Team User Controller
 * Manages team membership (users in teams)
 */
class TeamUserController extends BaseController
{
    /**
     * GET /api/v1/teams/{team}/users
     * List all users in a team
     */
    public function index(Request $request, Team $team): JsonResponse
    {
        $this->authorizeTeamAccess($team);

        $users = $team->users()
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            )
            ->orderBy($request->sort ?? 'name', $request->order ?? 'asc')
            ->get();

        return $this->success($users);
    }

    /**
     * POST /api/v1/teams/{team}/users
     * Add users to a team
     */
    public function store(Request $request, Team $team): JsonResponse
    {
        $this->authorizeTeamAccess($team);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        // Verify all users belong to the same client
        $users = User::whereIn('id', $validated['user_ids'])
            ->where('client_id', $this->getClientId())
            ->get();

        if ($users->count() !== count($validated['user_ids'])) {
            return $this->forbidden('Some users do not belong to your organization');
        }

        // Attach users (sync without detaching)
        $team->users()->syncWithoutDetaching($validated['user_ids']);

        return $this->success(
            $team->load('users'),
            'Users added to team successfully'
        );
    }

    /**
     * DELETE /api/v1/teams/{team}/users/{user}
     * Remove a user from a team
     */
    public function destroy(Team $team, User $user): JsonResponse
    {
        $this->authorizeTeamAccess($team);

        if ($user->client_id !== $this->getClientId()) {
            return $this->forbidden('Access denied');
        }

        $team->users()->detach($user->id);

        return $this->success(null, 'User removed from team successfully');
    }

    private function authorizeTeamAccess(Team $team): void
    {
        if ($team->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
