<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'survey_id' => $this->survey_id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'description' => $this->description,
            'required' => $this->required,
            'sort_order' => $this->sort_order,
            'enabled' => $this->enabled,
            'options' => $this->options, // JSON array for choice questions
            'properties' => $this->properties, // JSON for rating config
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
