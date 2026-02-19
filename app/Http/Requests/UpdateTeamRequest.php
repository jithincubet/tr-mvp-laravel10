<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['nullable', 'string', 'in:team,group,collection'],
            'parent_id' => ['nullable', 'integer', 'exists:tr2_users_collections,id'],
            'disabled' => ['sometimes', 'boolean'],
        ];
    }
}
