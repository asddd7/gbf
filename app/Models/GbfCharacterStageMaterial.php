<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GbfCharacterStageMaterial extends Model
{
    protected $table = 'gbf_character_stage_materials';

    protected $fillable = [
        'character_stage_id',
        'assignment_id',
        'material_id',
        'quantity',
        'notes',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(
            GbfCharacterStageAssignment::class,
            'assignment_id'
        );
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(GbfMaterial::class, 'material_id');
    }
}