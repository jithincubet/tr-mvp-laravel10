<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'trigger_type' => ['required', 'string', 'in:expiring,expired,event,manual'],
            'trigger_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'endorsement_id' => ['nullable', 'integer', 'exists:tr2_endorsements,id'],
            'template_id' => ['nullable', 'integer', 'exists:tr2_email_templates,id'],
            'recipients' => ['nullable', 'array'],
            'recipients.*' => ['string', 'in:user,manager,admin,instructor'],
            'channels' => ['nullable', 'array'],
            'channels.*' => ['string', 'in:email,in_app,sms'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }
}
