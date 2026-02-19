<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Notification Resource
 * Transforms Notification model for API responses
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'read_at' => $this->read_at?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            
            // Computed
            'is_read' => $this->read_at !== null,
            
            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'rule' => new NotificationRuleResource($this->whenLoaded('rule')),
        ];
    }
}
