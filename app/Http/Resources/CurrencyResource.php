<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Currency Resource
 * Transforms Currency (EMS) model for API responses
 */
class CurrencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date_qualified' => $this->date_qualified?->format('Y-m-d'),
            'date_expired' => $this->date_expired?->format('Y-m-d'),
            'status' => $this->status,
            'current' => $this->current,
            'passes' => $this->passes,
            'fails' => $this->fails,
            'score' => $this->score,
            'progress' => $this->progress,
            'description' => $this->description,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'disabled' => $this->disabled,
            'ltc_base' => $this->ltc_base,
            'ltc_authorization_level' => $this->ltc_authorization_level,
            'started' => $this->started,
            'ended' => $this->ended,
            'checked' => $this->checked,
            'changed' => $this->changed,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Computed
            'is_expired' => $this->date_expired && $this->date_expired->isPast(),
            'days_until_expiry' => $this->date_expired ? now()->diffInDays($this->date_expired, false) : null,
            
            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'endorsement' => new EndorsementResource($this->whenLoaded('endorsement')),
            'session' => new SessionResource($this->whenLoaded('session')),
        ];
    }
}
