<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Notification Rule Resource
 * Transforms NotificationRule model for API responses
 */
class NotificationRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'trigger_type' => $this->trigger_type,
            'trigger_days' => $this->trigger_days,
            'channels' => $this->channels,
            'recipients' => $this->recipients,
            'disabled' => $this->disabled,
            'created_at' => $this->created_at?->toIso8601String(),
            
            // Relationships
            'template' => new EmailTemplateResource($this->whenLoaded('template')),
        ];
    }
}
