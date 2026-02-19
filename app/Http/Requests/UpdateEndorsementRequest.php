<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Endorsement Request
 * Validates endorsement update data
 */
class UpdateEndorsementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $endorsementId = $this->route('endorsement')?->id ?? $this->route('endorsement');
        
        return [
            'name' => 'sometimes|string|max:255',
            'code' => "sometimes|string|max:50|unique:tr2_endorsements,code,{$endorsementId}",
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
}
