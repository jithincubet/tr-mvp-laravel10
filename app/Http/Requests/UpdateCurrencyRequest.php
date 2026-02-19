<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Currency Request
 * Validates EMS currency record update data
 */
class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_qualified' => 'nullable|date',
            'date_expired' => 'nullable|date|after_or_equal:date_qualified',
            'status' => 'nullable|string|in:active,expired,pending,suspended',
            'current' => 'nullable|boolean',
            'passes' => 'nullable|boolean',
            'fails' => 'nullable|boolean',
            'score' => 'nullable|integer|min:0|max:100',
            'progress' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string|max:2000',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'disabled' => 'nullable|boolean',
            'ltc_base' => 'nullable|string|max:100',
            'ltc_authorization_level' => 'nullable|string|max:50',
        ];
    }
}
