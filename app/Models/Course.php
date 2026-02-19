<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Course Model
 * 
 * Represents a course used in billing records.
 * IDs are user-supplied (not auto-generated).
 * 
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 */
class Course extends Model
{
    protected $table = 'b_courses';

    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships

    public function billingRecords(): HasMany
    {
        return $this->hasMany(BillingRecord::class, 'course_id');
    }
}