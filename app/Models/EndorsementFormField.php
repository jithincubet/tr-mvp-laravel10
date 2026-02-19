<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EndorsementFormField Model
 * 
 * Defines individual fields within an endorsement form.
 * 
 * @property int $id
 * @property int $form_id
 * @property string $name
 * @property string|null $description
 * @property string $field_type
 * @property string|null $placeholder
 * @property bool $required
 * @property bool $disabled
 * @property int $sort_order
 * @property array|null $properties
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EndorsementFormField extends Model
{
    protected $table = 'tr2_endorsement_form_fields';

    protected $fillable = [
        'form_id',
        'name',
        'description',
        'field_type',
        'placeholder',
        'required',
        'disabled',
        'sort_order',
        'properties',
    ];

    protected $casts = [
        'required' => 'boolean',
        'disabled' => 'boolean',
        'sort_order' => 'integer',
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships

    public function form(): BelongsTo
    {
        return $this->belongsTo(EndorsementForm::class, 'form_id');
    }

    // Scopes

    public function scopeEnabled($query)
    {
        return $query->where('disabled', false);
    }

    public function scopeRequired($query)
    {
        return $query->where('required', true);
    }

    public function scopeForForm($query, int $formId)
    {
        return $query->where('form_id', $formId);
    }
}
