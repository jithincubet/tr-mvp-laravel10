<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * ExerciseEvent Model
 * 
 * Individual answer events for exercise sessions.
 * 
 * @property int $id
 * @property int $session_id
 * @property int $question_id
 * @property int|null $response_id
 * @property string|null $text_response
 * @property bool $is_correct
 * @property int $points_earned
 * @property int|null $time_spent_seconds
 * @property \Carbon\Carbon $answered_at
 * @property \Carbon\Carbon $created_at
 */
class ExerciseEvent extends Model
{
    protected $table = 'tr2_exercise_events';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'question_id',
        'response_id',
        'text_response',
        'is_correct',
        'points_earned',
        'time_spent_seconds',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_earned' => 'integer',
        'time_spent_seconds' => 'integer',
        'answered_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Relationships

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExerciseSession::class, 'session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExerciseQuestion::class, 'question_id');
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(ExerciseResponse::class, 'response_id');
    }

    // Scopes

    public function scopeCorrect(Builder $query): Builder
    {
        return $query->where('is_correct', true);
    }

    public function scopeIncorrect(Builder $query): Builder
    {
        return $query->where('is_correct', false);
    }

    public function scopeForQuestion(Builder $query, int $questionId): Builder
    {
        return $query->where('question_id', $questionId);
    }
}
