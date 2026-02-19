<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FormExposureRequirement Model
 * 
 * Defines exposure requirements for form completion.
 * Links forms to exposure types with minimum counts.
 * 
 * @property int $id
 * @property int $form_id
 * @property int $exposure_type_id
 * @property int $minimum_count
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class FormExposureRequirement extends Model
{
    protected $table = 'tr2_form_exposure_requirements';

    protected $fillable = [
        'form_id',
        'exposure_type_id',
        'minimum_count',
    ];

    protected $casts = [
        'minimum_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function exposureType(): BelongsTo
    {
        return $this->belongsTo(ExposureType::class);
    }
}
