<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Event Request
 * Validates event update data
 */
class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_id' => 'sometimes|integer|exists:tr2_forms,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'facility_id' => 'nullable|integer|exists:tr2_training_facilities,id',
            'instructor_id' => 'nullable|integer|exists:tr2_users,id',
            'max_participants' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|string|in:draft,scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:2000',
            'is_private' => 'nullable|boolean',
        ];
    }
}
