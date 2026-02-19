<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    /**
     * List all exercises for the authenticated client.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Exercise::query()
            ->withCount('questions');

        // Filter by training
        if ($request->has('training_id')) {
            $query->where('training_id', $request->input('training_id'));
        }

        // Filter by enabled status
        if ($request->has('enabled')) {
            $query->where('enabled', $request->boolean('enabled'));
        }

        // Filter by type
        if ($request->has('exercise_type')) {
            $query->where('exercise_type', $request->input('exercise_type'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Include training relationship
        if ($request->boolean('include_training')) {
            $query->with('training:id,name');
        }

        $exercises = $query->orderBy('name')->paginate($request->input('per_page', 25));

        return ExerciseResource::collection($exercises);
    }

    /**
     * Create a new exercise.
     */
    public function store(StoreExerciseRequest $request): JsonResponse
    {
        $exercise = Exercise::create([
            'client_id' => auth()->user()->client_id,
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Exercise created successfully.',
            'data' => new ExerciseResource($exercise),
        ], 201);
    }

    /**
     * Get a specific exercise with questions and responses.
     */
    public function show(Exercise $exercise): ExerciseResource
    {
        $exercise->load(['questions' => function ($query) {
            $query->ordered()->with('responses');
        }]);
        $exercise->loadCount('questions');

        return new ExerciseResource($exercise);
    }

    /**
     * Update an exercise.
     */
    public function update(StoreExerciseRequest $request, Exercise $exercise): JsonResponse
    {
        $exercise->update($request->validated());

        return response()->json([
            'message' => 'Exercise updated successfully.',
            'data' => new ExerciseResource($exercise),
        ]);
    }

    /**
     * Delete an exercise (cascades to questions).
     */
    public function destroy(Exercise $exercise): JsonResponse
    {
        // Check for active sessions
        $activeSessionsCount = $exercise->sessions()
            ->whereNull('ended_at')
            ->count();

        if ($activeSessionsCount > 0) {
            return response()->json([
                'message' => "Cannot delete exercise with {$activeSessionsCount} active sessions.",
            ], 422);
        }

        $exercise->delete();

        return response()->json([
            'message' => 'Exercise deleted successfully.',
        ]);
    }
}
