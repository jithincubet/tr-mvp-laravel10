<?php

namespace App\Http\Controllers\Api;

use App\Models\Training;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Training Controller
 * Manages training courses/programs
 */
class TrainingController extends BaseController
{
    /**
     * GET /api/v1/training
     * List all training programs for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = Training::where('client_id', $this->getClientId())
            ->withCount(['enrollments', 'sessions'])
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
            )
            ->when($request->status, fn($q, $status) => 
                $q->where('status', $status)
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
     * POST /api/v1/training
     * Create a new training program
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'duration_hours' => 'nullable|numeric|min:0',
            'max_participants' => 'nullable|integer|min:1',
            'prerequisites' => 'nullable|array',
            'syllabus' => 'nullable|array',
            'status' => 'string|in:draft,published,archived',
            'disabled' => 'boolean',
        ]);

        $validated['client_id'] = $this->getClientId();

        $training = Training::create($validated);

        return $this->success($training, 'Training program created successfully', 201);
    }

    /**
     * GET /api/v1/training/{training}
     * Get training program details
     */
    public function show(Training $training): JsonResponse
    {
        $this->authorizeClientAccess($training);

        return $this->success(
            $training->load(['enrollments.user', 'sessions'])
        );
    }

    /**
     * PUT /api/v1/training/{training}
     * Update training program
     */
    public function update(Request $request, Training $training): JsonResponse
    {
        $this->authorizeClientAccess($training);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'duration_hours' => 'nullable|numeric|min:0',
            'max_participants' => 'nullable|integer|min:1',
            'prerequisites' => 'nullable|array',
            'syllabus' => 'nullable|array',
            'status' => 'string|in:draft,published,archived',
            'disabled' => 'boolean',
        ]);

        $training->update($validated);

        return $this->success($training, 'Training program updated successfully');
    }

    /**
     * DELETE /api/v1/training/{training}
     * Delete training program
     */
    public function destroy(Training $training): JsonResponse
    {
        $this->authorizeClientAccess($training);

        // TODO: Check for active enrollments
        $training->delete();

        return $this->success(null, 'Training program deleted successfully');
    }

    private function authorizeClientAccess(Training $training): void
    {
        if ($training->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
