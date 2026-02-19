<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Form Request
 * Validates training form creation data
 */
class StoreFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
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
            'endorsements' => 'nullable|array',
            'endorsements.*' => 'integer|exists:tr2_endorsements,id',
            'instructor_endorsements' => 'nullable|array',
            'instructor_endorsements.*' => 'integer|exists:tr2_endorsements,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Form name is required.',
            'name.max' => 'Form name cannot exceed 255 characters.',
        ];
    }
}
