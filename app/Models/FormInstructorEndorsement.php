<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FormInstructorEndorsement Model
 * 
 * Pivot model linking forms to instructor endorsements.
 * Defines which endorsements instructors need to teach a form.
 * 
 * @property int $id
 * @property int $form_id
 * @property int $endorsement_id
 * @property \Carbon\Carbon $created_at
 */
class FormInstructorEndorsement extends Model
{
    protected $table = 'tr2_form_instructor_endorsements';

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
