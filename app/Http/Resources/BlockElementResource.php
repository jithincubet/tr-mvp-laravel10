<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Block Element Resource
 * Transforms BlockElement model for API responses
 */
class BlockElementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'sortorder' => $this->sortorder,
            'enabled' => $this->enabled,
            'mandatory' => $this->mandatory,
            'is_critical' => $this->is_critical,
            'fail_triggers_additional_training' => $this->fail_triggers_additional_training,
            'guidance_text' => $this->guidance_text,
            'syllabus_text' => $this->syllabus_text,
            'created_at' => $this->created_at?->toIso8601String(),
            
            // Relationships
            'block' => new BlockResource($this->whenLoaded('block')),
            'grading' => new GradingResource($this->whenLoaded('grading')),
        ];
    }
}
