<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Endorsement Form Field Resource
 * Transforms EndorsementFormField model for API responses
 */
class EndorsementFormFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'field_type' => $this->field_type,
            'placeholder' => $this->placeholder,
            'required' => $this->required,
            'sort_order' => $this->sort_order,
            'properties' => $this->properties,
            'disabled' => $this->disabled,
        ];
    }
}
