<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GbfCharacterStage extends Model
{
    protected $table = 'gbf_character_stages';

    protected $fillable = [
        'character_id',
        'stage_key',
        'name',
        'description',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(GbfCharacter::class, 'character_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(
            GbfCharacterStageMaterial::class,
            'character_stage_id'
        );
    }
}
