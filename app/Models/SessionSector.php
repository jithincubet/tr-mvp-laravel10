<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SessionSector Model
 * 
 * Records flight sector information for line training sessions.
 * 
 * @property int $id
 * @property int $session_id
 * @property string|null $departure
 * @property string|null $arrival
 * @property string|null $flight_number
 * @property string|null $aircraft_registration
 * @property string|null $sector_type
 * @property \Carbon\Carbon|null $departure_time
 * @property \Carbon\Carbon|null $arrival_time
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SessionSector extends Model
{
    protected $table = 'tr2_session_sectors';

    protected $fillable = [
        'session_id',
        'departure',
        'arrival',
        'flight_number',
        'aircraft_registration',
        'sector_type',
        'departure_time',
        'arrival_time',
        'notes',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'session_id');
    }
}
