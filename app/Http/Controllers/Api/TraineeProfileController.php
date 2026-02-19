<?php

namespace App\Http\Controllers\Api;

use App\Models\TraineeProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Trainee Profile Controller
 * Manages trainee progress and profile data
 */
class TraineeProfileController extends BaseController
{
    /**
     * GET /api/v1/trainee-profiles
     * List all trainee profiles for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = TraineeProfile::whereHas('user', fn($q) => 
                $q->where('client_id', $this->getClientId())
            )
            ->with(['user:id,name,email'])
            ->when($request->search, fn($q, $search) => 
                $q->whereHas('user', fn($uq) => 
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                )
            )
            ->when($request->training_phase, fn($q, $phase) => 
                $q->where('training_phase', $phase)
            )
            ->orderBy($request->sort ?? 'updated_at', $request->order ?? 'desc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/trainee-profiles
     * Create a new trainee profile
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'training_phase' => 'nullable|string|max:100',
            'current_endorsements' => 'nullable|array',
            'target_endorsements' => 'nullable|array',
            'training_start_date' => 'nullable|date',
            'expected_completion_date' => 'nullable|date',
            'instructor_id' => 'nullable|integer|exists:users,id',
            'notes' => 'nullable|string',
            'status' => 'string|in:active,paused,completed,withdrawn',
        ]);

        // Verify user belongs to client
        $user = \App\Models\User::findOrFail($validated['user_id']);
        if ($user->client_id !== $this->getClientId()) {
            return $this->forbidden('Cannot create profile for user from another client');
        }

        // Check for existing profile
        $existing = TraineeProfile::where('user_id', $validated['user_id'])->first();
        if ($existing) {
            return $this->error('Trainee profile already exists for this user', 'DUPLICATE', 409);
        }

        $profile = TraineeProfile::create($validated);

        return $this->success($profile->load('user'), 'Trainee profile created successfully', 201);
    }

    /**
     * GET /api/v1/trainee-profiles/{traineeProfile}
     * Get trainee profile details
     */
    public function show(TraineeProfile $traineeProfile): JsonResponse
    {
        $this->authorizeClientAccess($traineeProfile);

        return $this->success(
            $traineeProfile->load(['user', 'instructor', 'enrollments.training'])
        );
    }

    /**
     * PUT /api/v1/trainee-profiles/{traineeProfile}
     * Update trainee profile
     */
    public function update(Request $request, TraineeProfile $traineeProfile): JsonResponse
    {
        $this->authorizeClientAccess($traineeProfile);

        $validated = $request->validate([
            'training_phase' => 'nullable|string|max:100',
            'current_endorsements' => 'nullable|array',
            'target_endorsements' => 'nullable|array',
            'training_start_date' => 'nullable|date',
            'expected_completion_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date',
            'instructor_id' => 'nullable|integer|exists:users,id',
            'notes' => 'nullable|string',
            'status' => 'string|in:active,paused,completed,withdrawn',
            'progress_data' => 'nullable|array',
        ]);

        $traineeProfile->update($validated);

        return $this->success($traineeProfile, 'Trainee profile updated successfully');
    }

    /**
     * DELETE /api/v1/trainee-profiles/{traineeProfile}
     * Delete trainee profile
     */
    public function destroy(TraineeProfile $traineeProfile): JsonResponse
    {
        $this->authorizeClientAccess($traineeProfile);

        $traineeProfile->delete();

        return $this->success(null, 'Trainee profile deleted successfully');
    }

    private function authorizeClientAccess(TraineeProfile $traineeProfile): void
    {
        if ($traineeProfile->user->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
