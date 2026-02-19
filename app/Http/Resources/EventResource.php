<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Event Resource
 * Transforms Event model for API responses
 */
class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'location' => $this->location,
            'status' => $this->status,
            'max_participants' => $this->max_participants,
            'is_private' => $this->is_private,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'form' => new FormResource($this->whenLoaded('form')),
            'facility' => new FacilityResource($this->whenLoaded('facility')),
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'participants' => UserResource::collection($this->whenLoaded('participants')),
            'sessions' => SessionResource::collection($this->whenLoaded('sessions')),
            'endorsements' => EndorsementResource::collection($this->whenLoaded('endorsements')),
            
            // Counts
            'participants_count' => $this->whenCounted('participants'),
            'sessions_count' => $this->whenCounted('sessions'),
        ];
    }
}
