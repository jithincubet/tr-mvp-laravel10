<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Block Element Request
 * Validates block element creation data
 */
class StoreBlockElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => 'required|integer|exists:tr2_blocks,id',
            'description' => 'required|string|max:500',
            'grading_id' => 'nullable|integer|exists:tr2_gradings,id',
            'sortorder' => 'nullable|integer|min:0',
            'enabled' => 'nullable|boolean',
            'mandatory' => 'nullable|boolean',
            'is_critical' => 'nullable|boolean',
            'fail_triggers_additional_training' => 'nullable|boolean',
            'guidance_text' => 'nullable|string|max:5000',
            'syllabus_text' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.required' => 'A parent block must be selected.',
            'parent_id.exists' => 'The selected block does not exist.',
            'description.required' => 'Element description is required.',
        ];
    }
}
