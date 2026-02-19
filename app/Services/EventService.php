<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventSession;
use App\Models\EventParticipant;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Event Service
 * Handles event scheduling, conflicts, and management
 */
class EventService
{
    /**
     * Create a new event with participants
     */
    public function createEvent(array $data, array $participantIds = [], array $endorsementIds = []): Event
    {
        $event = Event::create($data);

        // Attach participants
        if (!empty($participantIds)) {
            $this->syncParticipants($event, $participantIds);
        }

        // Attach endorsements
        if (!empty($endorsementIds)) {
            $event->endorsements()->sync($endorsementIds);
        }

        return $event->fresh(['form', 'participants', 'endorsements']);
    }

    /**
     * Sync event participants
     */
    public function syncParticipants(Event $event, array $userIds): void
    {
        // Remove existing participants not in new list
        EventParticipant::where('event_id', $event->id)
            ->whereNotIn('user_id', $userIds)
            ->delete();

        // Add new participants
        foreach ($userIds as $userId) {
            EventParticipant::firstOrCreate([
                'event_id' => $event->id,
                'user_id' => $userId,
            ]);
        }
    }

    /**
     * Check for scheduling conflicts
     */
    public function checkConflicts(
        int $clientId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $facilityId = null,
        ?int $instructorId = null,
        ?int $excludeEventId = null
    ): Collection {
        $conflicts = collect();

        // Check facility conflicts
        if ($facilityId) {
            $facilityConflicts = Event::where('client_id', $clientId)
                ->where('facility_id', $facilityId)
                ->where('id', '!=', $excludeEventId)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                        });
                })
                ->get();

            $conflicts = $conflicts->merge($facilityConflicts->map(fn($e) => [
                'type' => 'facility',
                'event' => $e,
                'message' => "Facility conflict with event: {$e->title}",
            ]));
        }

        // Check instructor conflicts
        if ($instructorId) {
            $instructorConflicts = Event::where('client_id', $clientId)
                ->where('instructor_id', $instructorId)
                ->where('id', '!=', $excludeEventId)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                        });
                })
                ->get();

            $conflicts = $conflicts->merge($instructorConflicts->map(fn($e) => [
                'type' => 'instructor',
                'event' => $e,
                'message' => "Instructor conflict with event: {$e->title}",
            ]));
        }

        return $conflicts;
    }

    /**
     * Reschedule an event
     */
    public function rescheduleEvent(Event $event, Carbon $newStartDate, Carbon $newEndDate): Event
    {
        $event->update([
            'start_date' => $newStartDate,
            'end_date' => $newEndDate,
        ]);

        // TODO: Notify participants of schedule change

        return $event->fresh();
    }

    /**
     * Cancel an event
     */
    public function cancelEvent(Event $event, ?string $reason = null): Event
    {
        $event->update([
            'status' => 'cancelled',
            'notes' => $reason ? "{$event->notes}\n\nCancellation reason: {$reason}" : $event->notes,
        ]);

        // TODO: Notify participants of cancellation

        return $event;
    }

    /**
     * Get upcoming events for a user
     */
    public function getUpcomingEvents(int $userId, int $days = 30): Collection
    {
        return Event::whereHas('participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('start_date', '>=', now())
            ->where('start_date', '<=', now()->addDays($days))
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Create sessions for all participants
     */
    public function createSessionsForParticipants(Event $event): Collection
    {
        $sessions = collect();

        foreach ($event->participants as $participant) {
            $session = EventSession::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'user_id' => $participant->user_id,
                ],
                [
                    'client_id' => $event->client_id,
                    'session_date' => $event->start_date,
                    'status' => 'pending',
                ]
            );
            $sessions->push($session);
        }

        return $sessions;
    }
}
