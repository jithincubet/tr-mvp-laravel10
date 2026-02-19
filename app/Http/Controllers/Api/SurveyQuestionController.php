<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyQuestionRequest;
use App\Http\Resources\SurveyQuestionResource;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SurveyQuestionController extends Controller
{
    /**
     * List questions for a specific survey.
     */
    public function index(Survey $survey): JsonResponse
    {
        $questions = $survey->questions()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => SurveyQuestionResource::collection($questions),
        ]);
    }

    /**
     * Create a new question for a survey.
     */
    public function store(StoreSurveyQuestionRequest $request, Survey $survey): JsonResponse
    {
        $validated = $request->validated();

        // Set sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = $survey->questions()->max('sort_order') + 1;
        }

        $question = $survey->questions()->create($validated);

        return response()->json([
            'message' => 'Question created successfully.',
            'data' => new SurveyQuestionResource($question),
        ], 201);
    }

    /**
     * Update a question.
     */
    public function update(StoreSurveyQuestionRequest $request, SurveyQuestion $surveyQuestion): JsonResponse
    {
        $surveyQuestion->update($request->validated());

        return response()->json([
            'message' => 'Question updated successfully.',
            'data' => new SurveyQuestionResource($surveyQuestion),
        ]);
    }

    /**
     * Delete a question.
     */
    public function destroy(SurveyQuestion $surveyQuestion): JsonResponse
    {
        $surveyQuestion->delete();

        return response()->json([
            'message' => 'Question deleted successfully.',
        ]);
    }

    /**
     * Bulk update sort order for questions.
     */
    public function reorder(Request $request, Survey $survey): JsonResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'integer', 'exists:tr2_survey_questions,id'],
            'order.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->input('order') as $item) {
            SurveyQuestion::where('id', $item['id'])
                ->where('survey_id', $survey->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'message' => 'Question order updated successfully.',
        ]);
    }
}
