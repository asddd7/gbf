
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gbf_character_stage_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')
                ->constrained('gbf_characters')
                ->cascadeOnDelete();

            $table->foreignId('character_stage_id')
                ->constrained('gbf_character_stages')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(
                ['character_id', 'character_stage_id'],
                'gbf_stage_assignment_unique'
            );

            $table->index(
                ['character_id', 'sort_order'],
                'gbf_stage_assignment_order_index'
            );
        });

        Schema::table('gbf_character_stage_materials', function (Blueprint $table) {
            $table->foreignId('assignment_id')
                ->nullable()
                ->after('character_stage_id')
                ->constrained('gbf_character_stage_assignments')
                ->cascadeOnDelete();
        });

        /*
         * Salin relasi progression lama ke tabel assignment.
         * Satu stage lama yang dimiliki satu character menjadi satu assignment.
         */
        $stages = DB::table('gbf_character_stages')
            ->whereNotNull('character_id')
            ->orderBy('character_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($stages as $stage) {
            $assignmentId = DB::table('gbf_character_stage_assignments')
                ->insertGetId([
                    'character_id' => $stage->character_id,
                    'character_stage_id' => $stage->id,
                    'sort_order' => $stage->sort_order,
                    'created_at' => $stage->created_at,
                    'updated_at' => $stage->updated_at,
                ]);

            DB::table('gbf_character_stage_materials')
                ->where('character_stage_id', $stage->id)
                ->update([
                    'assignment_id' => $assignmentId,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('gbf_character_stage_materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignment_id');
        });

        Schema::dropIfExists('gbf_character_stage_assignments');
    }
};
