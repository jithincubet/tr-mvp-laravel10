<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Event Controller
 * Manages training events (scheduling, status, participants)
 */
class EventController extends BaseController
{
    /**
     * GET /api/v1/events
     * List all events for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::where('client_id', $this->getClientId())
            ->with(['form', 'facility', 'instructor', 'participants'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->form_id, fn($q, $formId) => $q->where('form_id', $formId))
            ->when($request->instructor_id, fn($q, $id) => $q->where('instructor_id', $id))
            ->when($request->date_from, fn($q, $date) => $q->where('date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->where('date', '<=', $date))
            ->orderBy($request->sort ?? 'date', $request->order ?? 'desc');

        $perPage = min($request->per_page ?? 50, 100);

        return $this->paginated($query->paginate($perPage));
    }

    /**
     * POST /api/v1/events
     * Create a new training event
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_id' => 'required|integer|exists:tr2_forms,id',
            'facility_id' => 'nullable|integer|exists:tr2_training_facilities,id',
            'instructor_id' => 'required|integer|exists:tr2_users,id',
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'status' => 'string|in:scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:2000',
            'participant_ids' => 'array',
            'participant_ids.*' => 'integer|exists:tr2_users,id',
            'endorsement_ids' => 'array',
            'endorsement_ids.*' => 'integer|exists:tr2_endorsements,id',
        ]);

        $validated['client_id'] = $this->getClientId();
        $validated['status'] = $validated['status'] ?? 'scheduled';

        $event = Event::create($validated);

        // Attach participants
        if (!empty($validated['participant_ids'])) {
            $event->participants()->attach($validated['participant_ids']);
        }

        // Attach endorsements
        if (!empty($validated['endorsement_ids'])) {
            $event->endorsements()->attach($validated['endorsement_ids']);
        }

        return $this->success(
            $event->load(['form', 'facility', 'instructor', 'participants', 'endorsements']),
            'Event created successfully',
            201
        );
    }

    /**
     * GET /api/v1/events/{event}
     * Get a single event with all relationships
     */
    public function show(Event $event): JsonResponse
    {
        $this->authorizeClientAccess($event);

        return $this->success(
            $event->load(['form', 'facility', 'instructor', 'participants', 'endorsements', 'sessions'])
        );
    }

    /**
     * PUT /api/v1/events/{event}
     * Update event details
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        $this->authorizeClientAccess($event);

        $validated = $request->validate([
            'form_id' => 'integer|exists:tr2_forms,id',
            'facility_id' => 'nullable|integer|exists:tr2_training_facilities,id',
            'instructor_id' => 'integer|exists:tr2_users,id',
            'date' => 'date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'status' => 'string|in:scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:2000',
            'participant_ids' => 'array',
            'participant_ids.*' => 'integer|exists:tr2_users,id',
        ]);

        $event->update($validated);

        if (isset($validated['participant_ids'])) {
            $event->participants()->sync($validated['participant_ids']);
        }

        return $this->success($event->fresh(['form', 'facility', 'instructor', 'participants']), 'Event updated');
    }

    /**
     * DELETE /api/v1/events/{event}
     * Delete an event
     */
    public function destroy(Event $event): JsonResponse
    {
        $this->authorizeClientAccess($event);

        $event->delete();

        return $this->success(null, 'Event deleted successfully');
    }

    /**
     * PATCH /api/v1/events/{event}/reschedule
     * Reschedule an event to a new date/time
     */
    public function reschedule(Request $request, Event $event): JsonResponse
    {
        $this->authorizeClientAccess($event);

        $validated = $request->validate([
            'date' => 'required|date|after:today',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:500',
        ]);

        $event->update([
            'date' => $validated['date'],
            'start_time' => $validated['start_time'] ?? $event->start_time,
            'end_time' => $validated['end_time'] ?? $event->end_time,
        ]);

        // TODO: Log reschedule in audit trail
        // TODO: Send notifications to participants

        return $this->success($event, 'Event rescheduled successfully');
    }

    /**
     * PATCH /api/v1/events/bulk-status
     * Update status for multiple events
     */
    public function bulkStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_ids' => 'required|array|min:1',
            'event_ids.*' => 'integer|exists:tr2_events,id',
            'status' => 'required|string|in:scheduled,in_progress,completed,cancelled',
        ]);

        Event::where('client_id', $this->getClientId())
            ->whereIn('id', $validated['event_ids'])
            ->update(['status' => $validated['status']]);

        return $this->success(null, 'Events updated successfully');
    }

    /**
     * DELETE /api/v1/events/bulk
     * Delete multiple events
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_ids' => 'required|array|min:1',
            'event_ids.*' => 'integer|exists:tr2_events,id',
        ]);

        $deleted = Event::where('client_id', $this->getClientId())
            ->whereIn('id', $validated['event_ids'])
            ->delete();

        return $this->success(['deleted_count' => $deleted], 'Events deleted successfully');
    }

    private function authorizeClientAccess(Event $event): void
    {
        if ($event->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
