<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GbfCharacterStageAssignment extends Model
{
    protected $table = 'gbf_character_stage_assignments';

    protected $fillable = [
        'character_id',
        'character_stage_id',
        'sort_order',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(GbfCharacter::class, 'character_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(
            GbfCharacterStage::class,
            'character_stage_id'
        );
    }

    public function materials(): HasMany
    {
        return $this->hasMany(
            GbfCharacterStageMaterial::class,
            'assignment_id'
        );
    }
}