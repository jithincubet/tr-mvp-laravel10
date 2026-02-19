<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\EventSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Event Session Controller
 * Manages individual training sessions within events
 */
class EventSessionController extends BaseController
{
    /**
     * GET /api/v1/events/{event}/sessions
     * List all sessions for an event
     */
    public function index(Event $event): JsonResponse
    {
        $this->authorizeEventAccess($event);

        $sessions = $event->sessions()
            ->with(['trainee', 'grades', 'sectors', 'exposures'])
            ->orderBy('created_at')
            ->get();

        return $this->success($sessions);
    }

    /**
     * POST /api/v1/events/{event}/sessions
     * Create a new session for an event
     */
    public function store(Request $request, Event $event): JsonResponse
    {
        $this->authorizeEventAccess($event);

        $validated = $request->validate([
            'trainee_id' => 'required|integer|exists:tr2_users,id',
            'status' => 'string|in:pending,in_progress,completed,approved',
            'notes' => 'nullable|string|max:2000',
            'pass_fail' => 'nullable|string|in:pass,fail,incomplete',
        ]);

        $validated['event_id'] = $event->id;
        $validated['client_id'] = $event->client_id;
        $validated['status'] = $validated['status'] ?? 'pending';

        $session = EventSession::create($validated);

        return $this->success(
            $session->load(['trainee', 'event']),
            'Session created successfully',
            201
        );
    }

    /**
     * GET /api/v1/sessions/{session}
     * Get session details with all grading data
     */
    public function show(EventSession $session): JsonResponse
    {
        $this->authorizeSessionAccess($session);

        return $this->success(
            $session->load([
                'event.form.blocks.elements',
                'trainee',
                'grades.blockElement',
                'sectors',
                'exposures.exposureType',
            ])
        );
    }

    /**
     * PUT /api/v1/sessions/{session}
     * Update session information
     */
    public function update(Request $request, EventSession $session): JsonResponse
    {
        $this->authorizeSessionAccess($session);

        $validated = $request->validate([
            'status' => 'string|in:pending,in_progress,completed,approved',
            'notes' => 'nullable|string|max:2000',
            'pass_fail' => 'nullable|string|in:pass,fail,incomplete',
            'instructor_signature' => 'nullable|string',
            'trainee_signature' => 'nullable|string',
        ]);

        $session->update($validated);

        return $this->success($session, 'Session updated successfully');
    }

    /**
     * PATCH /api/v1/sessions/{session}/approve
     * Approve a completed session
     */
    public function approve(Request $request, EventSession $session): JsonResponse
    {
        $this->authorizeSessionAccess($session);

        if ($session->status !== 'completed') {
            return $this->error('Session must be completed before approval', 'INVALID_STATUS', 400);
        }

        $validated = $request->validate([
            'approved_by' => 'required|integer|exists:tr2_users,id',
            'approval_notes' => 'nullable|string|max:500',
        ]);

        $session->update([
            'status' => 'approved',
            'approved_by' => $validated['approved_by'],
            'approved_at' => now(),
        ]);

        // TODO: Process endorsement/currency updates based on session results

        return $this->success($session, 'Session approved successfully');
    }

    /**
     * PATCH /api/v1/sessions/{session}/archive
     * Archive a session
     */
    public function archive(EventSession $session): JsonResponse
    {
        $this->authorizeSessionAccess($session);

        $session->update([
            'archived' => true,
            'archived_at' => now(),
        ]);

        return $this->success($session, 'Session archived successfully');
    }

    private function authorizeEventAccess(Event $event): void
    {
        if ($event->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }

    private function authorizeSessionAccess(EventSession $session): void
    {
        if ($session->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
