<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Endorsement Resource
 * Transforms Endorsement model for API responses
 */
class EndorsementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'reference' => $this->reference,
            'disabled' => $this->disabled,
            'enrollment' => $this->enrollment,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'type' => new EndorsementTypeResource($this->whenLoaded('type')),
            'schedule' => new EndorsementScheduleResource($this->whenLoaded('schedule')),
            'form' => new EndorsementFormResource($this->whenLoaded('endorsementForm')),
            'training' => new TrainingResource($this->whenLoaded('training')),
            'notification_rule' => new NotificationRuleResource($this->whenLoaded('notificationRule')),
        ];
    }
}
