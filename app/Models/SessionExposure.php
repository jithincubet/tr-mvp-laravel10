<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SessionExposure Model
 * 
 * Records operational exposures achieved during a session.
 * Links session to exposure types with counts.
 * 
 * @property int $id
 * @property int $session_id
 * @property int $exposure_type_id
 * @property int $count
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SessionExposure extends Model
{
    protected $table = 'tr2_session_exposures';

    protected $fillable = [
        'session_id',
        'exposure_type_id',
        'count',
        'notes',
    ];

    protected $casts = [
        'count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'session_id');
    }

    public function exposureType(): BelongsTo
    {
        return $this->belongsTo(ExposureType::class);
    }
}
