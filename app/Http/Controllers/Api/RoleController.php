<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Role Controller
 * Manages role definitions for the client
 */
class RoleController extends BaseController
{
    /**
     * GET /api/v1/roles
     * List all roles for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = Role::where('client_id', $this->getClientId())
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
     * POST /api/v1/roles
     * Create a new role
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'disabled' => 'boolean',
        ]);

        $validated['client_id'] = $this->getClientId();

        $role = Role::create($validated);

        return $this->success($role, 'Role created successfully', 201);
    }

    /**
     * GET /api/v1/roles/{role}
     * Get role details with permissions
     */
    public function show(Role $role): JsonResponse
    {
        $this->authorizeClientAccess($role);

        return $this->success(
            $role->load(['users', 'features'])
        );
    }

    /**
     * PUT /api/v1/roles/{role}
     * Update role
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $this->authorizeClientAccess($role);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'disabled' => 'boolean',
        ]);

        $role->update($validated);

        return $this->success($role, 'Role updated successfully');
    }

    /**
     * DELETE /api/v1/roles/{role}
     * Delete role
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->authorizeClientAccess($role);

        // TODO: Check if role has users assigned
        $role->users()->detach();
        $role->delete();

        return $this->success(null, 'Role deleted successfully');
    }

    private function authorizeClientAccess(Role $role): void
    {
        if ($role->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
