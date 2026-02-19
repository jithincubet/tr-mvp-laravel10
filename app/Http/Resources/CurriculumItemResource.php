<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'training_id' => $this->training_id,
            'item_type' => $this->item_type, // 'section', 'lesson', 'quiz', 'assignment'
            'title' => $this->title,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'duration_minutes' => $this->duration_minutes,
            'is_mandatory' => $this->is_mandatory,
            'enabled' => $this->enabled,
            
            // Content reference (polymorphic)
            'content_type' => $this->content_type,
            'content_id' => $this->content_id,
            
            // Completion requirements
            'completion_type' => $this->completion_type, // 'view', 'time', 'score', 'manual'
            'completion_threshold' => $this->completion_threshold,
            
            // Nested children for tree structure
            'children' => CurriculumItemResource::collection($this->whenLoaded('children')),
            'children_count' => $this->whenCounted('children'),
            
            // Progress tracking (when loaded with user context)
            'user_progress' => $this->whenLoaded('userProgress', function () {
                return [
                    'status' => $this->userProgress->status,
                    'progress_percentage' => $this->userProgress->progress_percentage,
                    'started_at' => $this->userProgress->started_at?->toIso8601String(),
                    'completed_at' => $this->userProgress->completed_at?->toIso8601String(),
                ];
            }),
            
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
