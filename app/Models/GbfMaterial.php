<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GbfMaterial extends Model
{
    protected $table = 'gbf_materials';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'unit',
        'image_path',
    ];

    public function stageMaterials(): HasMany
    {
        return $this->hasMany(
            GbfCharacterStageMaterial::class,
            'material_id'
        );
    }
}
