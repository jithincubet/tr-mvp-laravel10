<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Exposure Requirement Resource
 * Transforms FormExposureRequirement model for API responses
 */
class ExposureRequirementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'min_count' => $this->min_count,
            'notes' => $this->notes,
            
            // Relationships
            'exposure_type' => new ExposureTypeResource($this->whenLoaded('exposureType')),
        ];
    }
}
