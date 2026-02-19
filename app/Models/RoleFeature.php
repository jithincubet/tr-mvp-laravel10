<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RoleFeature Model
 * 
 * Pivot model linking roles to features.
 * Defines which features are accessible by which roles.
 * 
 * @property int $id
 * @property int $role_id
 * @property int $feature_id
 * @property \Carbon\Carbon $created_at
 */
class RoleFeature extends Model
{
    protected $table = 'tr2_role_features';

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'feature_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
