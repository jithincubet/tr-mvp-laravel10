<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Facility Resource
 * Transforms TrainingFacility model for API responses
 */
class FacilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'timezone' => $this->timezone,
            'disabled' => $this->disabled,
            'created_at' => $this->created_at?->toIso8601String(),
            
            // Relationships
            'type' => new FacilityTypeResource($this->whenLoaded('type')),
        ];
    }
}
