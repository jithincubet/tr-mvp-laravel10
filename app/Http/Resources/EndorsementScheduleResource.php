<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Endorsement Schedule Resource
 * Transforms EndorsementSchedule model for API responses
 */
class EndorsementScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'reference' => $this->reference,
            'expire_months' => $this->expire_months,
            'check_days' => $this->check_days,
            'open_days' => $this->open_days,
            'once' => $this->once,
            'resume' => $this->resume,
            'cycles' => $this->cycles,
            'properties' => $this->properties,
            'is_default' => $this->is_default,
            'disabled' => $this->disabled,
        ];
    }
}
