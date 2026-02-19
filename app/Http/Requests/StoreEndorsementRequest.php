<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Endorsement Request
 * Validates endorsement creation data
 */
class StoreEndorsementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tr2_endorsements,code',
            'description' => 'nullable|string|max:2000',
            'reference' => 'nullable|string|max:100',
            'type_id' => 'nullable|integer|exists:tr2_endorsement_types,id',
            'schedule_id' => 'nullable|integer|exists:tr2_endorsement_schedules,id',
            'form_id' => 'nullable|integer|exists:tr2_endorsement_forms,id',
            'training_id' => 'nullable|integer|exists:tr2_training,id',
            'notification_id' => 'nullable|integer|exists:tr2_notification_rules,id',
            'disabled' => 'nullable|boolean',
            'enrollment' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Endorsement name is required.',
            'code.required' => 'Endorsement code is required.',
            'code.unique' => 'This endorsement code already exists.',
        ];
    }
}
