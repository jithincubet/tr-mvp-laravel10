<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BlockElement Model
 * 
 * Represents an individual gradable element within a block.
 * 
 * @property int $id
 * @property int $parent_id
 * @property int|null $grading_id
 * @property string $description
 * @property bool $enabled
 * @property bool $mandatory
 * @property bool|null $is_critical
 * @property bool|null $fail_triggers_additional_training
 * @property int $sortorder
 * @property string|null $guidance_text
 * @property string|null $syllabus_text
 * @property \Carbon\Carbon $created_at
 */
class BlockElement extends Model
{
    protected $table = 'tr2_block_elements';

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'grading_id',
        'description',
        'enabled',
        'mandatory',
        'is_critical',
        'fail_triggers_additional_training',
        'sortorder',
        'guidance_text',
        'syllabus_text',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'mandatory' => 'boolean',
        'is_critical' => 'boolean',
        'fail_triggers_additional_training' => 'boolean',
        'sortorder' => 'integer',
        'created_at' => 'datetime',
    ];

    // Relationships

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class, 'parent_id');
    }

    public function grading(): BelongsTo
    {
        return $this->belongsTo(Grading::class);
    }

    public function sessionGrades(): HasMany
    {
        return $this->hasMany(EventSessionGrade::class);
    }

    // Scopes

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeMandatory($query)
    {
        return $query->where('mandatory', true);
    }

    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    public function scopeForBlock($query, int $blockId)
    {
        return $query->where('parent_id', $blockId);
    }
}
