<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Role Resource
 * Transforms Role model for API responses
 */
class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'color' => $this->color,
            'disabled' => $this->disabled,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at?->toIso8601String(),
            
            // Relationships
            'features' => FeatureResource::collection($this->whenLoaded('features')),
            
            // Counts
            'users_count' => $this->whenCounted('users'),
        ];
    }
}
