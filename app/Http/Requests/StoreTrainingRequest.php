<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:100'],
            'duration_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1', 'max:99'],
            'validity_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'prerequisites' => ['nullable', 'array'],
            'prerequisites.*' => ['integer', 'exists:tr2_training,id'],
            'endorsement_ids' => ['nullable', 'array'],
            'endorsement_ids.*' => ['integer', 'exists:tr2_endorsements,id'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }
}
