<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Trainee Profile Resource
 * Transforms TraineeProfile model for API responses
 */
class TraineeProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'training_status' => $this->training_status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'target_release_date' => $this->target_release_date?->format('Y-m-d'),
            'actual_release_date' => $this->actual_release_date?->format('Y-m-d'),
            'current_phase' => $this->current_phase,
            'sectors_completed' => $this->sectors_completed,
            'sectors_required' => $this->sectors_required,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'aircraft_type' => new AircraftTypeResource($this->whenLoaded('aircraftType')),
        ];
    }
}
