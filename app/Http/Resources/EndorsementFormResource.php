<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Endorsement Form Resource
 * Transforms EndorsementForm model for API responses
 */
class EndorsementFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'reference' => $this->reference,
            'sort_order' => $this->sort_order,
            'properties' => $this->properties,
            'disabled' => $this->disabled,
            'created_at' => $this->created_at?->toIso8601String(),
            
            // Relationships
            'fields' => EndorsementFormFieldResource::collection($this->whenLoaded('fields')),
        ];
    }
}
