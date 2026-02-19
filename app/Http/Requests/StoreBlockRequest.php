<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Block Request
 * Validates training block creation data
 */
class StoreBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_id' => 'required|integer|exists:tr2_forms,id',
            'name' => 'required|string|max:255',
            'tbt_id' => 'nullable|integer|exists:tr2_block_types,id',
            'sortorder' => 'nullable|integer|min:0',
            'enabled' => 'nullable|boolean',
            'guidance_text' => 'nullable|string|max:5000',
            'syllabus_text' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'form_id.required' => 'A training form must be selected.',
            'form_id.exists' => 'The selected training form does not exist.',
            'name.required' => 'Block name is required.',
        ];
    }
}
