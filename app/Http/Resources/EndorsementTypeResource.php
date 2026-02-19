<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Endorsement Type Resource
 * Transforms EndorsementType model for API responses
 */
class EndorsementTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'reference' => $this->reference,
            'scheme' => $this->scheme,
            'learner_upload' => $this->learner_upload,
            'is_default' => $this->is_default,
            'disabled' => $this->disabled,
        ];
    }
}
