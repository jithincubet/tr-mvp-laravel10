<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'enrollable_type' => $this->enrollable_type,
            'enrollable_id' => $this->enrollable_id,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'enrolled_by' => $this->enrolled_by,
            'notes' => $this->notes,
            
            // Computed status based on dates
            'status' => $this->computeStatus(),
            'is_active' => $this->isActive(),
            'is_expired' => $this->isExpired(),
            'is_completed' => $this->completed_at !== null,
            
            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'enrolledBy' => $this->whenLoaded('enrolledBy', function () {
                return [
                    'id' => $this->enrolledBy->id,
                    'name' => $this->enrolledBy->name,
                ];
            }),
            
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    protected function computeStatus(): string
    {
        $now = Carbon::now();

        if ($this->completed_at) {
            return 'completed';
        }

        if ($this->ends_at && $this->ends_at < $now) {
            return 'expired';
        }

        if ($this->starts_at && $this->starts_at > $now) {
            return 'pending';
        }

        return 'active';
    }

    protected function isActive(): bool
    {
        $now = Carbon::now();
        $started = !$this->starts_at || $this->starts_at <= $now;
        $notEnded = !$this->ends_at || $this->ends_at >= $now;
        $notCompleted = !$this->completed_at;

        return $started && $notEnded && $notCompleted;
    }

    protected function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at < Carbon::now() && !$this->completed_at;
    }
}
