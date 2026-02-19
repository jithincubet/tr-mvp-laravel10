<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Form Request
 * Validates training form update data
 */
class UpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'reference' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'enabled' => 'nullable|boolean',
            'is_line_training' => 'nullable|boolean',
            'required_sectors' => 'nullable|integer|min:0',
            'exposure_count_min' => 'nullable|integer|min:0',
            'exposure_count_airport' => 'nullable|integer|min:0',
            'exposure_count_weather' => 'nullable|integer|min:0',
            'release_criteria' => 'nullable|array',
        ];
    }
}
