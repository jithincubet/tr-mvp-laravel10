<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FormEndorsement Model
 * 
 * Pivot model linking forms to trainee endorsements.
 * Defines which endorsements trainees can earn from a form.
 * 
 * @property int $id
 * @property int $form_id
 * @property int $endorsement_id
 * @property \Carbon\Carbon $created_at
 */
class FormEndorsement extends Model
{
    protected $table = 'tr2_form_endorsements';

    public $timestamps = false;

    protected $fillable = [
        'form_id',
        'endorsement_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function endorsement(): BelongsTo
    {
        return $this->belongsTo(Endorsement::class);
    }
}
