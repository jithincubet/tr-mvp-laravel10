<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExerciseQuestionRequest;
use App\Models\Exercise;
use App\Models\ExerciseQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExerciseQuestionController extends Controller
{
    /**
     * List questions for a specific exercise.
     */
    public function index(Exercise $exercise): JsonResponse
    {
        $questions = $exercise->questions()
            ->ordered()
            ->with('responses')
            ->get();

        return response()->json([
            'data' => $questions,
        ]);
    }

    /**
     * Create a new question for an exercise.
     */
    public function store(StoreExerciseQuestionRequest $request, Exercise $exercise): JsonResponse
    {
        $validated = $request->validated();
        $responses = $validated['responses'] ?? [];
        unset($validated['responses']);

        // Set sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = $exercise->questions()->max('sort_order') + 1;
        }

        $question = $exercise->questions()->create($validated);

        // Create responses for MCQ questions
        if (!empty($responses)) {
            foreach ($responses as $index => $response) {
                $question->responses()->create([
                    'response_text' => $response['response_text'],
                    'is_correct' => $response['is_correct'] ?? false,
                    'sort_order' => $response['sort_order'] ?? $index,
                ]);
            }
        }

        $question->load('responses');

        return response()->json([
            'message' => 'Question created successfully.',
            'data' => $question,
        ], 201);
    }

    /**
     * Update a question.
     */
    public function update(StoreExerciseQuestionRequest $request, ExerciseQuestion $exerciseQuestion): JsonResponse
    {
        $validated = $request->validated();
        $responses = $validated['responses'] ?? null;
        unset($validated['responses']);

        $exerciseQuestion->update($validated);

        // Update responses if provided
        if ($responses !== null) {
            // Remove existing responses
            $exerciseQuestion->responses()->delete();

            // Create new responses
            foreach ($responses as $index => $response) {
                $exerciseQuestion->responses()->create([
                    'response_text' => $response['response_text'],
                    'is_correct' => $response['is_correct'] ?? false,
                    'sort_order' => $response['sort_order'] ?? $index,
                ]);
            }
        }

        $exerciseQuestion->load('responses');

        return response()->json([
            'message' => 'Question updated successfully.',
            'data' => $exerciseQuestion,
        ]);
    }

    /**
     * Delete a question.
     */
    public function destroy(ExerciseQuestion $exerciseQuestion): JsonResponse
    {
        $exerciseQuestion->delete();

        return response()->json([
            'message' => 'Question deleted successfully.',
        ]);
    }
}
