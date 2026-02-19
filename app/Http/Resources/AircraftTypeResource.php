<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Aircraft Type Resource
 * Transforms AircraftType model for API responses
 */
class AircraftTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'category' => $this->category,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
