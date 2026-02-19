<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserCredential Model
 * 
 * Stores user authentication credentials (password hash, PIN).
 * Separated from User model for security.
 * 
 * @property int $id
 * @property int $user_id
 * @property string|null $password_hash
 * @property string|null $pin_code
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserCredential extends Model
{
    protected $table = 'tr2_user_credentials';

    protected $fillable = [
        'user_id',
        'password_hash',
        'pin_code',
    ];

    protected $hidden = [
        'password_hash',
        'pin_code',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
