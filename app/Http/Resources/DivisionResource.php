<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Division Resource
 * Transforms Division model for API responses
 */
class DivisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'post_code' => $this->post_code,
            'country' => $this->country,
            'vat_number' => $this->vat_number,
            'reference_name' => $this->reference_name,
            'reference_email' => $this->reference_email,
            'is_default' => $this->is_default,
            'is_locked' => $this->is_locked,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Counts
            'users_count' => $this->whenCounted('users'),
        ];
    }
}
