<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1', 'max:500'],
            'user_ids.*' => ['integer', 'exists:tr2_users,id'],
            'enrollable_type' => ['required', 'string', 'in:training,course,module'],
            'enrollable_id' => ['required', 'integer'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'enrolled_by' => ['nullable', 'integer', 'exists:tr2_users,id'],
            'send_notification' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required' => 'At least one user must be selected.',
            'user_ids.max' => 'Cannot enroll more than 500 users at once.',
            'ends_at.after_or_equal' => 'End date must be after or equal to start date.',
        ];
    }
}
