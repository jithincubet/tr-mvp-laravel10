<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Block Request
 * Validates training block update data
 */
class UpdateBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'tbt_id' => 'nullable|integer|exists:tr2_block_types,id',
            'sortorder' => 'nullable|integer|min:0',
            'enabled' => 'nullable|boolean',
            'guidance_text' => 'nullable|string|max:5000',
            'syllabus_text' => 'nullable|string|max:5000',
        ];
    }
}
