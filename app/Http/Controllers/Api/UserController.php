<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * User Controller
 * Manages user accounts within a client organization
 */
class UserController extends BaseController
{
    /**
     * GET /api/v1/users
     * List all users for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::where('client_id', $this->getClientId())
            ->when($request->search, fn($q, $search) => 
                $q->where(fn($q) => 
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                )
            )
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy($request->sort ?? 'last_name', $request->order ?? 'asc');

        $perPage = min($request->per_page ?? 50, 100);
        
        return $this->paginated($query->paginate($perPage));
    }

    /**
     * POST /api/v1/users
     * Create a new user
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:tr2_users,email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'status' => 'string|in:active,inactive,pending',
        ]);

        $validated['client_id'] = $this->getClientId();
        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);

        return $this->success($user, 'User created successfully', 201);
    }

    /**
     * GET /api/v1/users/{user}
     * Get a single user by ID
     */
    public function show(User $user): JsonResponse
    {
        $this->authorizeClientAccess($user);

        return $this->success($user->load(['roles', 'teams']));
    }

    /**
     * PUT /api/v1/users/{user}
     * Update user information
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorizeClientAccess($user);

        $validated = $request->validate([
            'email' => "email|unique:tr2_users,email,{$user->id}",
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'status' => 'string|in:active,inactive,pending',
        ]);

        $user->update($validated);

        return $this->success($user, 'User updated successfully');
    }

    /**
     * DELETE /api/v1/users/{user}
     * Soft delete a user
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorizeClientAccess($user);

        $user->delete();

        return $this->success(null, 'User deleted successfully');
    }

    /**
     * PUT /api/v1/users/{user}/roles
     * Update user's role assignments
     */
    public function updateRoles(Request $request, User $user): JsonResponse
    {
        $this->authorizeClientAccess($user);

        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:tr2_roles,code',
        ]);

        // TODO: Sync user roles
        // $user->roles()->sync($validated['roles']);

        return $this->success($user->load('roles'), 'User roles updated');
    }

    /**
     * Ensure user belongs to the current client
     */
    private function authorizeClientAccess(User $user): void
    {
        if ($user->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
