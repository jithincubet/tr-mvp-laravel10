<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * EmsEndorsementsGroup Model
 * 
 * Links endorsements to user groups/teams.
 * 
 * @property int $id
 * @property int $endorsement_id
 * @property int $group_id
 * @property int|null $relation_id
 * @property int $disabled
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class EmsEndorsementsGroup extends Model
{
    use SoftDeletes;

    protected $table = 'tr2_ems_endorsements_groups';

    protected $fillable = [
        'endorsement_id',
        'group_id',
        'relation_id',
        'disabled',
    ];

    protected $casts = [
        'disabled' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    public function endorsement(): BelongsTo
    {
        return $this->belongsTo(Endorsement::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'group_id');
    }

    // Scopes

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('disabled', 0);
    }

    public function scopeForEndorsement(Builder $query, int $endorsementId): Builder
    {
        return $query->where('endorsement_id', $endorsementId);
    }

    public function scopeForGroup(Builder $query, int $groupId): Builder
    {
        return $query->where('group_id', $groupId);
    }
}
