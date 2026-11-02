<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GbfCharacterStageMaterial extends Model
{
    protected $table = 'gbf_character_stage_materials';

    protected $fillable = [
        'character_stage_id',
        'material_id',
        'quantity',
        'notes',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(
            GbfCharacterStage::class,
            'character_stage_id'
        );
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(GbfMaterial::class, 'material_id');
    }
}
