<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Event Request
 * Validates event creation data
 */
class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'form_id' => 'required|integer|exists:tr2_forms,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'facility_id' => 'nullable|integer|exists:tr2_training_facilities,id',
            'instructor_id' => 'nullable|integer|exists:tr2_users,id',
            'max_participants' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|string|in:draft,scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:2000',
            'is_private' => 'nullable|boolean',
            'participants' => 'nullable|array',
            'participants.*' => 'integer|exists:tr2_users,id',
            'endorsements' => 'nullable|array',
            'endorsements.*' => 'integer|exists:tr2_endorsements,id',
        ];
    }

    public function messages(): array
    {
        return [
            'form_id.required' => 'A training form must be selected.',
            'form_id.exists' => 'The selected training form does not exist.',
            'title.required' => 'Event title is required.',
            'start_date.after_or_equal' => 'Event cannot be scheduled in the past.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }
}
