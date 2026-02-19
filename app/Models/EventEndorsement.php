<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EventEndorsement Model
 * 
 * Pivot model linking events to endorsements.
 * Defines which endorsements are awarded upon event completion.
 * 
 * @property int $id
 * @property int $event_id
 * @property int $endorsement_id
 * @property \Carbon\Carbon $created_at
 */
class EventEndorsement extends Model
{
    protected $table = 'tr2_event_endorsements';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'endorsement_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function endorsement(): BelongsTo
    {
        return $this->belongsTo(Endorsement::class);
    }
}
