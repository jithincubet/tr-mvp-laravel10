<?php

namespace App\Http\Controllers\Api;

use App\Models\Training;
use App\Models\TrainingEnrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Training Enrollment Controller
 * Manages user enrollments in training programs
 */
class TrainingEnrollmentController extends BaseController
{
    /**
     * GET /api/v1/training-enrollments
     * List all enrollments for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = TrainingEnrollment::whereHas('training', fn($q) => 
                $q->where('client_id', $this->getClientId())
            )
            ->with(['training:id,name,code', 'user:id,name,email'])
            ->when($request->training_id, fn($q, $trainingId) => 
                $q->where('training_id', $trainingId)
            )
            ->when($request->user_id, fn($q, $userId) => 
                $q->where('user_id', $userId)
            )
            ->when($request->status, fn($q, $status) => 
                $q->where('status', $status)
            )
            ->orderBy($request->sort ?? 'created_at', $request->order ?? 'desc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/training-enrollments
     * Enroll a user in a training program
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'training_id' => 'required|integer|exists:training,id',
            'user_id' => 'required|integer|exists:users,id',
            'enrolled_by' => 'nullable|integer|exists:users,id',
            'start_date' => 'nullable|date',
            'target_completion_date' => 'nullable|date|after:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify training belongs to client
        $training = Training::findOrFail($validated['training_id']);
        if ($training->client_id !== $this->getClientId()) {
            return $this->forbidden('Cannot enroll in training from another client');
        }

        // Check for existing enrollment
        $existing = TrainingEnrollment::where('training_id', $validated['training_id'])
            ->where('user_id', $validated['user_id'])
            ->whereIn('status', ['enrolled', 'in_progress'])
            ->first();

        if ($existing) {
            return $this->error('User is already enrolled in this training', 'DUPLICATE', 409);
        }

        $validated['status'] = 'enrolled';
        $validated['enrolled_at'] = now();

        $enrollment = TrainingEnrollment::create($validated);

        return $this->success(
            $enrollment->load(['training', 'user']),
            'User enrolled successfully',
            201
        );
    }

    /**
     * GET /api/v1/training-enrollments/{trainingEnrollment}
     * Get enrollment details
     */
    public function show(TrainingEnrollment $trainingEnrollment): JsonResponse
    {
        $this->authorizeClientAccess($trainingEnrollment);

        return $this->success(
            $trainingEnrollment->load(['training', 'user', 'sessions'])
        );
    }

    /**
     * PUT /api/v1/training-enrollments/{trainingEnrollment}
     * Update enrollment
     */
    public function update(Request $request, TrainingEnrollment $trainingEnrollment): JsonResponse
    {
        $this->authorizeClientAccess($trainingEnrollment);

        $validated = $request->validate([
            'status' => 'string|in:enrolled,in_progress,completed,withdrawn,failed',
            'progress_percentage' => 'integer|min:0|max:100',
            'target_completion_date' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Auto-set completed_at when status changes to completed
        if (isset($validated['status']) && $validated['status'] === 'completed' && !$trainingEnrollment->completed_at) {
            $validated['completed_at'] = now();
        }

        $trainingEnrollment->update($validated);

        return $this->success($trainingEnrollment, 'Enrollment updated successfully');
    }

    /**
     * DELETE /api/v1/training-enrollments/{trainingEnrollment}
     * Delete enrollment
     */
    public function destroy(TrainingEnrollment $trainingEnrollment): JsonResponse
    {
        $this->authorizeClientAccess($trainingEnrollment);

        $trainingEnrollment->delete();

        return $this->success(null, 'Enrollment deleted successfully');
    }

    private function authorizeClientAccess(TrainingEnrollment $trainingEnrollment): void
    {
        if ($trainingEnrollment->training->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
