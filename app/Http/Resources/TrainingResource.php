<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Training Resource
 * Transforms Training model for API responses
 */
class TrainingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'reference' => $this->reference,
            'category' => $this->category,
            'type' => $this->type,
            'duration_hours' => $this->duration_hours,
            'pass_score' => $this->pass_score,
            'max_attempts' => $this->max_attempts,
            'disabled' => $this->disabled,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'division' => new DivisionResource($this->whenLoaded('division')),
            
            // Counts
            'enrollments_count' => $this->whenCounted('enrollments'),
        ];
    }
}
