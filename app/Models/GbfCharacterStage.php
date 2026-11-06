<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GbfCharacterStage extends Model
{
    protected $table = 'gbf_character_stages';

    protected $fillable = [
        'stage_key',
        'name',
        'description',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(
            GbfCharacterStageAssignment::class,
            'character_stage_id'
        );
    }

    /**
     * Relasi legacy. Material progression baru harus diakses
     * melalui assignment->materials, bukan relasi ini.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(
            GbfCharacterStageMaterial::class,
            'character_stage_id'
        );
    }
}
