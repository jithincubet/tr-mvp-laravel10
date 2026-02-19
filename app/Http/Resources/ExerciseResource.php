<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'exercise_type' => $this->exercise_type,
            'pass_percentage' => $this->pass_percentage,
            'time_limit_minutes' => $this->time_limit_minutes,
            'randomize_questions' => $this->randomize_questions,
            'question_count' => $this->question_count,
            'show_correct_answers' => $this->show_correct_answers,
            'allow_retakes' => $this->allow_retakes,
            'max_attempts' => $this->max_attempts,
            'enabled' => $this->enabled,
            'questions_total' => $this->questions_total,
            'training' => $this->whenLoaded('training', function () {
                return [
                    'id' => $this->training->id,
                    'name' => $this->training->name,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
