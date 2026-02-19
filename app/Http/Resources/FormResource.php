<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Form Resource
 * Transforms Form model for API responses
 */
class FormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'reference' => $this->reference,
            'category' => $this->category,
            'enabled' => $this->enabled,
            'is_line_training' => $this->is_line_training,
            'required_sectors' => $this->required_sectors,
            'exposure_count_min' => $this->exposure_count_min,
            'exposure_count_airport' => $this->exposure_count_airport,
            'exposure_count_weather' => $this->exposure_count_weather,
            'release_criteria' => $this->release_criteria,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'blocks' => BlockResource::collection($this->whenLoaded('blocks')),
            'endorsements' => EndorsementResource::collection($this->whenLoaded('endorsements')),
            'instructor_endorsements' => EndorsementResource::collection($this->whenLoaded('instructorEndorsements')),
            'exposure_requirements' => ExposureRequirementResource::collection($this->whenLoaded('exposureRequirements')),
            
            // Counts
            'blocks_count' => $this->whenCounted('blocks'),
            'events_count' => $this->whenCounted('events'),
        ];
    }
}
