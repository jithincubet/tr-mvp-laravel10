<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExerciseQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_text' => ['required', 'string', 'max:2000'],
            'question_type' => ['required', 'string', 'in:mcq,true_false,text'],
            'explanation' => ['nullable', 'string', 'max:2000'],
            'media_url' => ['nullable', 'url', 'max:500'],
            'points' => ['integer', 'min:1', 'max:100'],
            'sort_order' => ['integer', 'min:0'],
            'enabled' => ['boolean'],
            
            // Responses array for MCQ questions
            'responses' => ['nullable', 'array', 'min:2', 'max:10'],
            'responses.*.response_text' => ['required_with:responses', 'string', 'max:500'],
            'responses.*.is_correct' => ['required_with:responses', 'boolean'],
            'responses.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'question_type.in' => 'Question type must be mcq, true_false, or text.',
            'responses.min' => 'Multiple choice questions require at least 2 responses.',
            'responses.*.response_text.required_with' => 'Each response must have text.',
        ];
    }
}
