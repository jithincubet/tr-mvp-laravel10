<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update User Request
 * Validates user update data
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');
        
        return [
            'email' => "sometimes|email|max:255|unique:tr2_users,email,{$userId}",
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'password' => 'nullable|string|min:8|max:128',
            'division_id' => 'nullable|integer|exists:tr2_divisions,id',
            'employment_code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date|before:today',
            'nationality' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'disabled' => 'nullable|boolean',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
