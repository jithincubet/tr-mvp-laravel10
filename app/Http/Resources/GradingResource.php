<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Grading Resource
 * Transforms Grading model for API responses
 */
class GradingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'scale' => $this->scale,
            'pass_value' => $this->pass_value,
            'disabled' => $this->disabled,
        ];
    }
}
