<?php

namespace App\Http\Controllers\Api;

use App\Models\EventSession;
use App\Models\EventSessionGrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Session Grade Controller
 * Manages grade entries for training sessions
 */
class SessionGradeController extends BaseController
{
    /**
     * GET /api/v1/sessions/{session}/grades
     * List all grades for a session
     */
    public function index(Request $request, EventSession $session): JsonResponse
    {
        $this->authorizeSessionAccess($session);

        $grades = EventSessionGrade::where('session_id', $session->id)
            ->with(['blockElement:id,description,parent_id', 'blockElement.block:id,name'])
            ->orderBy('block_element_id')
            ->get();

        return $this->success($grades);
    }

    /**
     * POST /api/v1/sessions/{session}/grades
     * Create or update grades for a session (bulk operation)
     */
    public function store(Request $request, EventSession $session): JsonResponse
    {
        $this->authorizeSessionAccess($session);

        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.block_element_id' => 'required|integer|exists:block_elements,id',
            'grades.*.grade_value' => 'nullable|string|max:10',
            'grades.*.notes' => 'nullable|string|max:1000',
        ]);

        $results = [];
        foreach ($validated['grades'] as $gradeData) {
            $grade = EventSessionGrade::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'block_element_id' => $gradeData['block_element_id'],
                ],
                [
                    'grade_value' => $gradeData['grade_value'] ?? null,
                    'notes' => $gradeData['notes'] ?? null,
                ]
            );
            $results[] = $grade;
        }

        return $this->success($results, 'Grades saved successfully');
    }

    private function authorizeSessionAccess(EventSession $session): void
    {
        if ($session->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
