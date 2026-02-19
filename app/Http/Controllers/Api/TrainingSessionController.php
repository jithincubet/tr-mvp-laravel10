<?php

namespace App\Http\Controllers\Api;

use App\Models\TrainingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Training Session Controller
 * Manages individual training sessions within programs
 */
class TrainingSessionController extends BaseController
{
    /**
     * GET /api/v1/training-sessions
     * List all training sessions for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = TrainingSession::whereHas('training', fn($q) => 
                $q->where('client_id', $this->getClientId())
            )
            ->with(['training:id,name', 'enrollment:id,user_id', 'enrollment.user:id,name'])
            ->when($request->training_id, fn($q, $trainingId) => 
                $q->where('training_id', $trainingId)
            )
            ->when($request->enrollment_id, fn($q, $enrollmentId) => 
                $q->where('enrollment_id', $enrollmentId)
            )
            ->when($request->status, fn($q, $status) => 
                $q->where('status', $status)
            )
            ->when($request->date_from, fn($q, $date) => 
                $q->whereDate('session_date', '>=', $date)
            )
            ->when($request->date_to, fn($q, $date) => 
                $q->whereDate('session_date', '<=', $date)
            )
            ->orderBy($request->sort ?? 'session_date', $request->order ?? 'desc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/training-sessions
     * Create a new training session
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'training_id' => 'required|integer|exists:training,id',
            'enrollment_id' => 'required|integer|exists:training_enrollments,id',
            'session_date' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:0',
            'module_name' => 'nullable|string|max:255',
            'instructor_id' => 'nullable|integer|exists:users,id',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'string|in:scheduled,in_progress,completed,cancelled',
        ]);

        // Verify training belongs to client
        $training = \App\Models\Training::findOrFail($validated['training_id']);
        if ($training->client_id !== $this->getClientId()) {
            return $this->forbidden('Access denied');
        }

        $session = TrainingSession::create($validated);

        return $this->success($session->load(['training', 'enrollment.user']), 'Training session created successfully', 201);
    }

    /**
     * GET /api/v1/training-sessions/{trainingSession}
     * Get training session details
     */
    public function show(TrainingSession $trainingSession): JsonResponse
    {
        $this->authorizeClientAccess($trainingSession);

        return $this->success(
            $trainingSession->load(['training', 'enrollment.user', 'instructor'])
        );
    }

    /**
     * PUT /api/v1/training-sessions/{trainingSession}
     * Update training session
     */
    public function update(Request $request, TrainingSession $trainingSession): JsonResponse
    {
        $this->authorizeClientAccess($trainingSession);

        $validated = $request->validate([
            'session_date' => 'date',
            'duration_minutes' => 'nullable|integer|min:0',
            'module_name' => 'nullable|string|max:255',
            'instructor_id' => 'nullable|integer|exists:users,id',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'string|in:scheduled,in_progress,completed,cancelled',
            'score' => 'nullable|numeric|min:0|max:100',
            'passed' => 'nullable|boolean',
        ]);

        $trainingSession->update($validated);

        return $this->success($trainingSession, 'Training session updated successfully');
    }

    /**
     * DELETE /api/v1/training-sessions/{trainingSession}
     * Delete training session
     */
    public function destroy(TrainingSession $trainingSession): JsonResponse
    {
        $this->authorizeClientAccess($trainingSession);

        $trainingSession->delete();

        return $this->success(null, 'Training session deleted successfully');
    }

    private function authorizeClientAccess(TrainingSession $trainingSession): void
    {
        if ($trainingSession->training->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
