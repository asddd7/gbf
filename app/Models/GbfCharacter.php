<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GbfCharacter extends Model
{
    protected $table = 'gbf_characters';

    protected $fillable = [
        'name',
        'slug',
        'character_type',
        'element',
        'description',
        'image_path',
        'sort_order',
    ];

    public function stageAssignments(): HasMany
    {
        return $this->hasMany(
            GbfCharacterStageAssignment::class,
            'character_id'
        )->orderBy('sort_order');
    }

    /**
     * Relasi ini mempertahankan akses ke daftar stage,
     * tetapi detail progression menggunakan stageAssignments().
     */
    public function stages()
    {
        return $this->belongsToMany(
            GbfCharacterStage::class,
            'gbf_character_stage_assignments',
            'character_id',
            'character_stage_id'
        )
            ->withPivot(['id', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}
