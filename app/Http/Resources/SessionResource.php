<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Session Resource
 * Transforms EventSession model for API responses
 */
class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_date' => $this->session_date?->format('Y-m-d'),
            'session_number' => $this->session_number,
            'status' => $this->status,
            'pass_fail_result' => $this->pass_fail_result,
            'is_approved' => $this->is_approved,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'notes_public' => $this->notes_public,
            'notes_private' => $this->when($this->canViewPrivateNotes(), $this->notes_private),
            'notes_admin' => $this->when($this->canViewAdminNotes(), $this->notes_admin),
            'flight_number' => $this->flight_number,
            'flight_route' => $this->flight_route,
            'aircraft_registration' => $this->aircraft_registration,
            'sector_type' => $this->sector_type,
            'is_line_check' => $this->is_line_check,
            'ltc_recommendation' => $this->ltc_recommendation,
            'ltc_recommendation_notes' => $this->ltc_recommendation_notes,
            'crm_assessment' => $this->crm_assessment,
            'requires_admin_attention' => $this->requires_admin_attention,
            'admin_attention_reason' => $this->admin_attention_reason,
            'signature_data' => $this->signature_data,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'event' => new EventResource($this->whenLoaded('event')),
            'user' => new UserResource($this->whenLoaded('user')),
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'approved_by' => new UserResource($this->whenLoaded('approvedBy')),
            'grades' => GradeResource::collection($this->whenLoaded('grades')),
            'sectors' => SectorResource::collection($this->whenLoaded('sectors')),
            'exposures' => ExposureResource::collection($this->whenLoaded('exposures')),
        ];
    }

    private function canViewPrivateNotes(): bool
    {
        $user = request()->user();
        return $user && ($user->id === $this->instructor_id || $user->isAdmin());
    }

    private function canViewAdminNotes(): bool
    {
        $user = request()->user();
        return $user && $user->isAdmin();
    }
}
