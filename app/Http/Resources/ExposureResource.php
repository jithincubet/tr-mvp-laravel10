<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Exposure Resource
 * Transforms SessionExposure model for API responses
 */
class ExposureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'count' => $this->count,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            
            // Relationships
            'exposure_type' => new ExposureTypeResource($this->whenLoaded('exposureType')),
        ];
    }
}
