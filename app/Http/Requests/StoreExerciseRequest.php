<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'exercise_type' => ['required', 'string', 'in:quiz,assessment,exam'],
            'pass_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
            'randomize_questions' => ['boolean'],
            'question_count' => ['nullable', 'integer', 'min:1'],
            'show_correct_answers' => ['boolean'],
            'allow_retakes' => ['boolean'],
            'max_attempts' => ['nullable', 'integer', 'min:1', 'max:100'],
            'training_id' => ['nullable', 'integer', 'exists:tr2_training,id'],
            'enabled' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'pass_percentage.min' => 'Pass percentage must be at least 0.',
            'pass_percentage.max' => 'Pass percentage cannot exceed 100.',
            'exercise_type.in' => 'Exercise type must be quiz, assessment, or exam.',
        ];
    }
}
