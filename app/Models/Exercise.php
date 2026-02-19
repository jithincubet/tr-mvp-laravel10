<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Exercise Model
 * 
 * Defines exercise/quiz definitions for LMS training.
 * 
 * @property int $id
 * @property int $client_id
 * @property int|null $training_id
 * @property string $name
 * @property string|null $description
 * @property string $exercise_type
 * @property int $pass_percentage
 * @property int|null $time_limit_minutes
 * @property bool $randomize_questions
 * @property int|null $question_count
 * @property bool $show_correct_answers
 * @property bool $allow_retakes
 * @property int|null $max_attempts
 * @property bool $enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Exercise extends Model
{
    use SoftDeletes;

    protected $table = 'tr2_exercises';

    protected $fillable = [
        'client_id',
        'training_id',
        'name',
        'description',
        'exercise_type',
        'pass_percentage',
        'time_limit_minutes',
        'randomize_questions',
        'question_count',
        'show_correct_answers',
        'allow_retakes',
        'max_attempts',
        'enabled',
    ];

    protected $casts = [
        'pass_percentage' => 'integer',
        'time_limit_minutes' => 'integer',
        'randomize_questions' => 'boolean',
        'question_count' => 'integer',
        'show_correct_answers' => 'boolean',
        'allow_retakes' => 'boolean',
        'max_attempts' => 'integer',
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Global Scope for Multi-Tenancy
    protected static function booted()
    {
        static::addGlobalScope('client', function (Builder $query) {
            if (auth()->check()) {
                $query->where('client_id', auth()->user()->client_id);
            }
        });
    }

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExerciseQuestion::class)->orderBy('sort_order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExerciseSession::class);
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('exercise_type', $type);
    }

    // Accessors

    public function getQuestionsTotalAttribute(): int
    {
        return $this->questions()->count();
    }
}
