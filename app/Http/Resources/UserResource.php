<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User Resource
 * Transforms User model for API responses
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'employment_code' => $this->employment_code,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'nationality' => $this->nationality,
            'language' => $this->language,
            'timezone' => $this->timezone,
            'avatar' => $this->avatar,
            'disabled' => $this->disabled,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships (conditionally loaded)
            'division' => new DivisionResource($this->whenLoaded('division')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'teams' => TeamResource::collection($this->whenLoaded('teamMemberships')),
            'currencies' => CurrencyResource::collection($this->whenLoaded('currencies')),
            'trainee_profile' => new TraineeProfileResource($this->whenLoaded('traineeProfile')),
        ];
    }
}
