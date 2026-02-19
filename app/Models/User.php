<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

/**
 * User Model
 * 
 * Represents a user in the system (trainee, instructor, admin, etc.)
 * 
 * @property int $id
 * @property int $client_id
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property int|null $division_id
 * @property string|null $employment_code
 * @property bool $disabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class User extends Model
{
    use SoftDeletes;

    protected $table = 'tr2_users';

    protected $fillable = [
        'client_id',
        'email',
        'first_name',
        'last_name',
        'division_id',
        'employment_code',
        'phone',
        'address',
        'city',
        'postal_code',
        'country',
        'date_of_birth',
        'nationality',
        'language',
        'timezone',
        'avatar',
        'disabled',
        'notes',
        'last_login_at',
        'email_verified_at',
    ];

    protected $casts = [
        'disabled' => 'boolean',
        'date_of_birth' => 'date',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
    ];

    // Global Scope for Multi-Tenancy
    protected static function booted()
    {
        static::addGlobalScope('client', function (Builder $query) {
            if (auth()->check()) {
                $query->where('client_id', auth()->user()->client_id);
            }
        });
    }

    // Accessors

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function credential(): HasOne
    {
        return $this->hasOne(UserCredential::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function currencies(): HasMany
    {
        return $this->hasMany(Currency::class);
    }

    public function eventSessions(): HasMany
    {
        return $this->hasMany(EventSession::class);
    }

    public function eventParticipations(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function traineeProfile(): HasOne
    {
        return $this->hasOne(TraineeProfile::class);
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function teamMemberships(): HasMany
    {
        return $this->hasMany(TeamUser::class);
    }

    public function instructedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'instructor_id');
    }

    public function instructedSessions(): HasMany
    {
        return $this->hasMany(EventSession::class, 'instructor_id');
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('disabled', false);
    }

    public function scopeInstructors(Builder $query): Builder
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('role', 'instructor');
        });
    }

    // Helper Methods

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('role', $role)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isInstructor(): bool
    {
        return $this->hasRole('instructor');
    }
}
