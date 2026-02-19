<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * User Role Controller
 * Manages user-role assignments
 */
class UserRoleController extends BaseController
{
    /**
     * GET /api/v1/user-roles
     * List all user-role assignments for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = UserRole::whereHas('user', fn($q) => 
                $q->where('client_id', $this->getClientId())
            )
            ->with(['user:id,name,email', 'role:id,name'])
            ->when($request->user_id, fn($q, $userId) => 
                $q->where('user_id', $userId)
            )
            ->when($request->role_id, fn($q, $roleId) => 
                $q->where('role_id', $roleId)
            )
            ->orderBy('created_at', 'desc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/user-roles
     * Assign a role to a user
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        // Verify user belongs to client
        $user = User::findOrFail($validated['user_id']);
        if ($user->client_id !== $this->getClientId()) {
            return $this->forbidden('Cannot assign role to user from another client');
        }

        // Verify role belongs to client
        $role = Role::findOrFail($validated['role_id']);
        if ($role->client_id !== $this->getClientId()) {
            return $this->forbidden('Cannot assign role from another client');
        }

        // Check if assignment already exists
        $existing = UserRole::where('user_id', $validated['user_id'])
            ->where('role_id', $validated['role_id'])
            ->first();

        if ($existing) {
            return $this->error('User already has this role', 'DUPLICATE', 409);
        }

        $userRole = UserRole::create($validated);

        return $this->success($userRole->load(['user', 'role']), 'Role assigned successfully', 201);
    }

    /**
     * DELETE /api/v1/user-roles/{userRole}
     * Remove a role from a user
     */
    public function destroy(UserRole $userRole): JsonResponse
    {
        // Verify user belongs to client
        if ($userRole->user->client_id !== $this->getClientId()) {
            return $this->forbidden('Access denied');
        }

        $userRole->delete();

        return $this->success(null, 'Role removed successfully');
    }
}
