<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyRequest;
use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class SurveyController extends Controller
{
    /**
     * List all surveys for the authenticated client.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Survey::query()
            ->withCount('questions');

        // Filter by enabled status
        if ($request->has('enabled')) {
            $query->where('enabled', $request->boolean('enabled'));
        }

        // Filter by training
        if ($request->has('training_id')) {
            $query->where('training_id', $request->input('training_id'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $surveys = $query->orderBy('name')->paginate($request->input('per_page', 25));

        return SurveyResource::collection($surveys);
    }

    /**
     * Create a new survey.
     */
    public function store(StoreSurveyRequest $request): JsonResponse
    {
        $survey = Survey::create([
            'client_id' => auth()->user()->client_id,
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Survey created successfully.',
            'data' => new SurveyResource($survey),
        ], 201);
    }

    /**
     * Get a specific survey with all questions.
     */
    public function show(Survey $survey): SurveyResource
    {
        $survey->load(['questions' => function ($query) {
            $query->orderBy('sort_order');
        }]);
        $survey->loadCount('questions');

        return new SurveyResource($survey);
    }

    /**
     * Update a survey.
     */
    public function update(StoreSurveyRequest $request, Survey $survey): JsonResponse
    {
        $survey->update($request->validated());

        return response()->json([
            'message' => 'Survey updated successfully.',
            'data' => new SurveyResource($survey),
        ]);
    }

    /**
     * Delete a survey (cascades to questions).
     */
    public function destroy(Survey $survey): JsonResponse
    {
        // Check for responses
        $responsesCount = $survey->responses()->count();
        if ($responsesCount > 0) {
            return response()->json([
                'message' => "Cannot delete survey with {$responsesCount} responses. Archive instead.",
            ], 422);
        }

        $survey->delete();

        return response()->json([
            'message' => 'Survey deleted successfully.',
        ]);
    }
}
