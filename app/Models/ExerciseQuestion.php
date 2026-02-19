<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * ExerciseQuestion Model
 * 
 * Question bank for exercises.
 * 
 * @property int $id
 * @property int $exercise_id
 * @property string $question_text
 * @property string $question_type
 * @property string|null $explanation
 * @property string|null $media_url
 * @property int $points
 * @property int $sort_order
 * @property bool $enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class ExerciseQuestion extends Model
{
    use SoftDeletes;

    protected $table = 'tr2_exercise_questions';

    protected $fillable = [
        'exercise_id',
        'question_text',
        'question_type',
        'explanation',
        'media_url',
        'points',
        'sort_order',
        'enabled',
    ];

    protected $casts = [
        'points' => 'integer',
        'sort_order' => 'integer',
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ExerciseResponse::class, 'question_id')->orderBy('sort_order');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ExerciseEvent::class, 'question_id');
    }

    // Scopes

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('question_type', $type);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    // Accessors

    public function getCorrectResponsesAttribute()
    {
        return $this->responses()->where('is_correct', true)->get();
    }
}
