<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\Feature;
use App\Models\RoleFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Role Feature Controller
 * Manages role-feature permissions (RBAC)
 */
class RoleFeatureController extends BaseController
{
    /**
     * GET /api/v1/role-features
     * List all role-feature assignments for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = RoleFeature::whereHas('role', fn($q) => 
                $q->where('client_id', $this->getClientId())
            )
            ->with(['role:id,name', 'feature:id,name,code,category'])
            ->when($request->role_id, fn($q, $roleId) => 
                $q->where('role_id', $roleId)
            )
            ->when($request->feature_id, fn($q, $featureId) => 
                $q->where('feature_id', $featureId)
            )
            ->orderBy('role_id')
            ->orderBy('feature_id');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 100));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/role-features
     * Assign a feature to a role
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'feature_id' => 'required|integer|exists:features,id',
            'permission_level' => 'nullable|string|in:read,write,admin',
        ]);

        // Verify role belongs to client
        $role = Role::findOrFail($validated['role_id']);
        if ($role->client_id !== $this->getClientId()) {
            return $this->forbidden('Cannot modify role from another client');
        }

        // Check if assignment already exists
        $existing = RoleFeature::where('role_id', $validated['role_id'])
            ->where('feature_id', $validated['feature_id'])
            ->first();

        if ($existing) {
            // Update existing
            $existing->update(['permission_level' => $validated['permission_level'] ?? 'read']);
            return $this->success($existing->load(['role', 'feature']), 'Permission updated');
        }

        $roleFeature = RoleFeature::create($validated);

        return $this->success(
            $roleFeature->load(['role', 'feature']),
            'Feature assigned to role successfully',
            201
        );
    }

    /**
     * GET /api/v1/role-features/{roleFeature}
     * Get role-feature assignment details
     */
    public function show(RoleFeature $roleFeature): JsonResponse
    {
        $this->authorizeClientAccess($roleFeature);

        return $this->success($roleFeature->load(['role', 'feature']));
    }

    /**
     * PUT /api/v1/role-features/{roleFeature}
     * Update role-feature assignment
     */
    public function update(Request $request, RoleFeature $roleFeature): JsonResponse
    {
        $this->authorizeClientAccess($roleFeature);

        $validated = $request->validate([
            'permission_level' => 'string|in:read,write,admin',
        ]);

        $roleFeature->update($validated);

        return $this->success($roleFeature, 'Permission updated successfully');
    }

    /**
     * DELETE /api/v1/role-features/{roleFeature}
     * Remove a feature from a role
     */
    public function destroy(RoleFeature $roleFeature): JsonResponse
    {
        $this->authorizeClientAccess($roleFeature);

        $roleFeature->delete();

        return $this->success(null, 'Feature removed from role successfully');
    }

    private function authorizeClientAccess(RoleFeature $roleFeature): void
    {
        if ($roleFeature->role->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
