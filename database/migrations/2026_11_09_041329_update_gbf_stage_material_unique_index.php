
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gbf_character_stage_materials', function (Blueprint $table) {
            // Tambahkan index pengganti untuk foreign key character_stage_id.
            $table->index(
                'character_stage_id',
                'gbf_stage_material_character_stage_index'
            );

            // Hapus unique index lama.
            $table->dropUnique('gbf_stage_material_unique');

            // Setiap assignment boleh memiliki material yang sama
            // dengan quantity yang berbeda dari assignment lain.
            $table->unique(
                ['assignment_id', 'material_id'],
                'gbf_assignment_material_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('gbf_character_stage_materials', function (Blueprint $table) {
            $table->dropUnique('gbf_assignment_material_unique');

            // Pulihkan unique index lama.
            $table->unique(
                ['character_stage_id', 'material_id'],
                'gbf_stage_material_unique'
            );

            // Index ini tidak lagi diperlukan karena sudah
            // didukung oleh unique index lama.
            $table->dropIndex(
                'gbf_stage_material_character_stage_index'
            );
        });
    }
};
