<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Block Model
 * 
 * Represents a section/block within a training form.
 * Contains block elements for grading.
 * 
 * @property int $id
 * @property int $client_id
 * @property int|null $form_id
 * @property int|null $tbt_id
 * @property string $name
 * @property bool $enabled
 * @property int|null $sortorder
 * @property string|null $guidance_text
 * @property string|null $syllabus_text
 * @property \Carbon\Carbon $created_at
 */
class Block extends Model
{
    protected $table = 'tr2_blocks';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'form_id',
        'tbt_id',
        'name',
        'enabled',
        'sortorder',
        'guidance_text',
        'syllabus_text',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'sortorder' => 'integer',
        'created_at' => 'datetime',
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

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function blockType(): BelongsTo
    {
        return $this->belongsTo(BlockType::class, 'tbt_id');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(BlockElement::class, 'parent_id')->orderBy('sortorder');
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeForForm(Builder $query, int $formId): Builder
    {
        return $query->where('form_id', $formId);
    }
}
