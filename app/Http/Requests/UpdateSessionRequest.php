<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Session Request
 * Validates event session update data
 */
class UpdateSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'instructor_id' => 'nullable|integer|exists:tr2_users,id',
            'session_date' => 'sometimes|date',
            'session_number' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:pending,in_progress,completed,approved,rejected',
            'pass_fail_result' => 'nullable|string|in:pass,fail,incomplete',
            'notes_public' => 'nullable|string|max:2000',
            'notes_private' => 'nullable|string|max:2000',
            'notes_admin' => 'nullable|string|max:2000',
            'flight_number' => 'nullable|string|max:20',
            'flight_route' => 'nullable|string|max:100',
            'aircraft_registration' => 'nullable|string|max:20',
            'sector_type' => 'nullable|string|max:50',
            'is_line_check' => 'nullable|boolean',
            'ltc_recommendation' => 'nullable|string|in:release,continue,additional_training',
            'ltc_recommendation_notes' => 'nullable|string|max:2000',
            'crm_assessment' => 'nullable|string|max:2000',
            'requires_admin_attention' => 'nullable|boolean',
            'admin_attention_reason' => 'nullable|string|max:500',
        ];
    }
}
