<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EventParticipant Model
 * 
 * Pivot model linking users to events as participants.
 * 
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 */
class EventParticipant extends Model
{
    protected $table = 'tr2_event_participants';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
