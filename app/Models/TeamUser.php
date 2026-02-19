<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TeamUser Model
 * 
 * Pivot model linking users to teams.
 * 
 * @property int $id
 * @property int $team_id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class TeamUser extends Model
{
    use SoftDeletes;

    protected $table = 'tr2_team_users';

    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
