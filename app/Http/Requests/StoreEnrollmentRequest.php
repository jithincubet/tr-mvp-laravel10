<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:tr2_users,id'],
            'enrollable_type' => ['required', 'string', 'in:training,course,module'],
            'enrollable_id' => ['required', 'integer'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'enrolled_by' => ['nullable', 'integer', 'exists:tr2_users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'ends_at.after_or_equal' => 'End date must be after or equal to start date.',
            'enrollable_type.in' => 'Enrollable type must be training, course, or module.',
        ];
    }
}
