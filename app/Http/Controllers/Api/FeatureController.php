<?php

namespace App\Http\Controllers\Api;

use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Feature Controller
 * Manages feature flags/definitions for RBAC
 */
class FeatureController extends BaseController
{
    /**
     * GET /api/v1/features
     * List all features
     */
    public function index(Request $request): JsonResponse
    {
        $query = Feature::query()
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
            )
            ->when($request->category, fn($q, $category) => 
                $q->where('category', $category)
            )
            ->when($request->enabled !== null, fn($q) => 
                $q->where('enabled', $request->boolean('enabled'))
            )
            ->orderBy($request->sort ?? 'category', $request->order ?? 'asc')
            ->orderBy('name', 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/features
     * Create a new feature
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:features,code',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'enabled' => 'boolean',
        ]);

        $feature = Feature::create($validated);

        return $this->success($feature, 'Feature created successfully', 201);
    }

    /**
     * GET /api/v1/features/{feature}
     * Get feature details
     */
    public function show(Feature $feature): JsonResponse
    {
        return $this->success(
            $feature->load('roles')
        );
    }

    /**
     * PUT /api/v1/features/{feature}
     * Update feature
     */
    public function update(Request $request, Feature $feature): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => 'string|max:100|unique:features,code,' . $feature->id,
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'enabled' => 'boolean',
        ]);

        $feature->update($validated);

        return $this->success($feature, 'Feature updated successfully');
    }

    /**
     * DELETE /api/v1/features/{feature}
     * Delete feature
     */
    public function destroy(Feature $feature): JsonResponse
    {
        // Remove all role associations first
        $feature->roles()->detach();
        $feature->delete();

        return $this->success(null, 'Feature deleted successfully');
    }
}
