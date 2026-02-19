<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Block Resource
 * Transforms Block model for API responses
 */
class BlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sortorder' => $this->sortorder,
            'enabled' => $this->enabled,
            'guidance_text' => $this->guidance_text,
            'syllabus_text' => $this->syllabus_text,
            'created_at' => $this->created_at?->toIso8601String(),
            
            // Relationships
            'form' => new FormResource($this->whenLoaded('form')),
            'block_type' => new BlockTypeResource($this->whenLoaded('blockType')),
            'elements' => BlockElementResource::collection($this->whenLoaded('elements')),
            
            // Counts
            'elements_count' => $this->whenCounted('elements'),
        ];
    }
}
