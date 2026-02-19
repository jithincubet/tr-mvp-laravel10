<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Currency Request
 * Validates EMS currency record creation data
 */
class StoreCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:tr2_users,id',
            'endorsement_id' => 'required|integer|exists:tr2_endorsements,id',
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
            'session_id' => 'nullable|integer|exists:tr2_event_sessions,id',
            'ltc_base' => 'nullable|string|max:100',
            'ltc_authorization_level' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'A user must be selected.',
            'user_id.exists' => 'The selected user does not exist.',
            'endorsement_id.required' => 'An endorsement must be selected.',
            'endorsement_id.exists' => 'The selected endorsement does not exist.',
            'date_expired.after_or_equal' => 'Expiry date must be after or equal to qualification date.',
        ];
    }
}
