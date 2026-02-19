<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * ExerciseResponse Model
 * 
 * Answer options for exercise questions.
 * 
 * @property int $id
 * @property int $question_id
 * @property string $content
 * @property string|null $label
 * @property bool $correct
 * @property bool $published
 * @property int $sort_order
 * @property string|null $synced_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ExerciseResponse extends Model
{
    protected $table = 'tr2_exercise_responses';

    protected $fillable = [
        'question_id',
        'content',
        'label',
        'correct',
        'published',
        'sort_order',
    ];

    protected $casts = [
        'correct' => 'boolean',
        'published' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    // Relationships

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExerciseQuestion::class, 'question_id');
    }

    // Scopes

    public function scopeCorrect(Builder $query): Builder
    {
        return $query->where('correct', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
