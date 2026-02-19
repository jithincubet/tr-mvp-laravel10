<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EventSessionGrade Model
 * 
 * Stores individual grade entries for session block elements.
 * 
 * @property int $id
 * @property int $session_id
 * @property int $block_element_id
 * @property string|null $grade_value
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EventSessionGrade extends Model
{
    protected $table = 'tr2_event_session_grades';

    protected $fillable = [
        'session_id',
        'block_element_id',
        'grade_value',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'session_id');
    }

    public function blockElement(): BelongsTo
    {
        return $this->belongsTo(BlockElement::class);
    }
}
