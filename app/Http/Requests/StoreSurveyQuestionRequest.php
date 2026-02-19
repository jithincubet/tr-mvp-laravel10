<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_text' => ['required', 'string', 'max:2000'],
            'question_type' => ['required', 'string', 'in:text,rating,yes_no,single_choice,multi_choice'],
            'description' => ['nullable', 'string', 'max:1000'],
            'required' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'enabled' => ['boolean'],
            
            // Options for choice questions (stored as JSON)
            'options' => ['nullable', 'array'],
            'options.*' => ['string', 'max:255'],
            
            // Properties for rating questions (stored as JSON)
            'properties' => ['nullable', 'array'],
            'properties.min_value' => ['nullable', 'integer', 'min:1'],
            'properties.max_value' => ['nullable', 'integer', 'max:10'],
            'properties.min_label' => ['nullable', 'string', 'max:100'],
            'properties.max_label' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'question_type.in' => 'Question type must be text, rating, yes_no, single_choice, or multi_choice.',
        ];
    }
}
