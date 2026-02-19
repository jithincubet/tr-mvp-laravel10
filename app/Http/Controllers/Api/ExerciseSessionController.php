<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\ExerciseSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExerciseSessionController extends Controller
{
    /**
     * List exercise sessions (filterable by exercise/user).
     */
    public function index(Request $request): JsonResponse
    {
        $query = ExerciseSession::query()
            ->with(['exercise:id,name', 'user:id,name,email']);

        // Filter by exercise
        if ($request->has('exercise_id')) {
            $query->where('exercise_id', $request->input('exercise_id'));
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by passed status
        if ($request->has('passed')) {
            $query->where('passed', $request->boolean('passed'));
        }

        // Filter by completion status
        if ($request->boolean('completed_only')) {
            $query->whereNotNull('ended_at');
        }

        $sessions = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 25));

        return response()->json($sessions);
    }

    /**
     * Start a new exercise attempt session.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'exercise_id' => ['required', 'integer', 'exists:tr2_exercises,id'],
        ]);

        $exercise = Exercise::findOrFail($request->input('exercise_id'));
        $userId = auth()->id();

        // Check max attempts
        if ($exercise->max_attempts) {
            $attemptCount = ExerciseSession::where('exercise_id', $exercise->id)
                ->where('user_id', $userId)
                ->count();

            if ($attemptCount >= $exercise->max_attempts) {
                return response()->json([
                    'message' => "Maximum attempts ({$exercise->max_attempts}) reached for this exercise.",
                ], 422);
            }
        }

        // Check for incomplete session
        $incompleteSession = ExerciseSession::where('exercise_id', $exercise->id)
            ->where('user_id', $userId)
            ->whereNull('ended_at')
            ->first();

        if ($incompleteSession) {
            return response()->json([
                'message' => 'You have an incomplete session for this exercise.',
                'data' => $incompleteSession,
            ], 422);
        }

        // Calculate attempt number
        $attemptNumber = ExerciseSession::where('exercise_id', $exercise->id)
            ->where('user_id', $userId)
            ->count() + 1;

        $session = ExerciseSession::create([
            'client_id' => auth()->user()->client_id,
            'exercise_id' => $exercise->id,
            'user_id' => $userId,
            'attempt_number' => $attemptNumber,
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Exercise session started.',
            'data' => $session,
        ], 201);
    }

    /**
     * Get a session with events.
     */
    public function show(ExerciseSession $exerciseSession): JsonResponse
    {
        $exerciseSession->load([
            'exercise:id,name,pass_percentage,time_limit_minutes',
            'events.question:id,question_text,question_type',
        ]);

        return response()->json([
            'data' => $exerciseSession,
        ]);
    }

    /**
     * Submit answers and calculate score.
     */
    public function update(Request $request, ExerciseSession $exerciseSession): JsonResponse
    {
        if ($exerciseSession->ended_at) {
            return response()->json([
                'message' => 'This session has already been completed.',
            ], 422);
        }

        $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.response_id' => ['nullable', 'integer'],
            'answers.*.text_response' => ['nullable', 'string'],
        ]);

        $exercise = $exerciseSession->exercise;
        $answers = collect($request->input('answers'));

        $totalPoints = 0;
        $earnedPoints = 0;

        // Process each answer
        foreach ($answers as $answer) {
            $question = $exercise->questions()->find($answer['question_id']);
            if (!$question) continue;

            $totalPoints += $question->points;

            $isCorrect = false;
            $responseId = $answer['response_id'] ?? null;

            if ($question->question_type === 'mcq' && $responseId) {
                $correctResponse = $question->responses()
                    ->where('is_correct', true)
                    ->first();
                $isCorrect = $correctResponse && $correctResponse->id === $responseId;
            } elseif ($question->question_type === 'true_false' && $responseId) {
                $response = $question->responses()->find($responseId);
                $isCorrect = $response && $response->is_correct;
            }

            if ($isCorrect) {
                $earnedPoints += $question->points;
            }

            // Record the event
            $exerciseSession->events()->create([
                'question_id' => $question->id,
                'response_id' => $responseId,
                'text_response' => $answer['text_response'] ?? null,
                'is_correct' => $isCorrect,
                'points_earned' => $isCorrect ? $question->points : 0,
            ]);
        }

        // Calculate score
        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;
        $passed = $score >= $exercise->pass_percentage;

        $exerciseSession->update([
            'ended_at' => now(),
            'score' => $score,
            'passed' => $passed,
        ]);

        return response()->json([
            'message' => $passed ? 'Congratulations! You passed.' : 'Session completed.',
            'data' => [
                'score' => $score,
                'passed' => $passed,
                'pass_percentage' => $exercise->pass_percentage,
                'earned_points' => $earnedPoints,
                'total_points' => $totalPoints,
            ],
        ]);
    }
}
