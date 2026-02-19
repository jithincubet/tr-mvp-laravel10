<?php

namespace App\Http\Controllers\Api;

use App\Models\Grading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Grading Controller
 * Manages grading scale definitions
 */
class GradingController extends BaseController
{
    /**
     * GET /api/v1/gradings
     * List all gradings for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = Grading::where('client_id', $this->getClientId())
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
            )
            ->when($request->enabled !== null, fn($q) => 
                $q->where('enabled', $request->boolean('enabled'))
            )
            ->orderBy($request->sort ?? 'sortorder', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/gradings
     * Create a new grading
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20',
            'description' => 'nullable|string|max:1000',
            'pass_value' => 'boolean',
            'fail_value' => 'boolean',
            'color' => 'nullable|string|max:20',
            'sortorder' => 'integer',
            'enabled' => 'boolean',
        ]);

        $validated['client_id'] = $this->getClientId();

        $grading = Grading::create($validated);

        return $this->success($grading, 'Grading created successfully', 201);
    }

    /**
     * GET /api/v1/gradings/{grading}
     * Get grading details
     */
    public function show(Grading $grading): JsonResponse
    {
        $this->authorizeClientAccess($grading);

        return $this->success($grading);
    }

    /**
     * PUT /api/v1/gradings/{grading}
     * Update grading
     */
    public function update(Request $request, Grading $grading): JsonResponse
    {
        $this->authorizeClientAccess($grading);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => 'string|max:20',
            'description' => 'nullable|string|max:1000',
            'pass_value' => 'boolean',
            'fail_value' => 'boolean',
            'color' => 'nullable|string|max:20',
            'sortorder' => 'integer',
            'enabled' => 'boolean',
        ]);

        $grading->update($validated);

        return $this->success($grading, 'Grading updated successfully');
    }

    /**
     * DELETE /api/v1/gradings/{grading}
     * Delete grading
     */
    public function destroy(Grading $grading): JsonResponse
    {
        $this->authorizeClientAccess($grading);

        // TODO: Check if block elements are using this grading
        $grading->delete();

        return $this->success(null, 'Grading deleted successfully');
    }

    private function authorizeClientAccess(Grading $grading): void
    {
        if ($grading->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
