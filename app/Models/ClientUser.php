<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Client User Model
 * 
 * Represents a billing contact / user associated with a client.
 * 
 * @property int $id
 * @property int|null $client_id
 * @property string $name
 * @property string|null $email
 * @property \Carbon\Carbon $created_at
 */
class ClientUser extends Model
{
    protected $table = 'b_client_users';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'name',
        'email',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}