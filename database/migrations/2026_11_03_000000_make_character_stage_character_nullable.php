<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gbf_character_stages', function (Blueprint $table) {
            $table->dropForeign(['character_id']);
            $table->unsignedBigInteger('character_id')->nullable()->change();
            $table->foreign('character_id')
                ->references('id')
                ->on('gbf_characters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('gbf_character_stages')->whereNull('character_id')->exists()) {
            throw new RuntimeException(
                'Cannot make character_id required while unassigned character stages exist.'
            );
        }

        Schema::table('gbf_character_stages', function (Blueprint $table) {
            $table->dropForeign(['character_id']);
            $table->unsignedBigInteger('character_id')->nullable(false)->change();
            $table->foreign('character_id')
                ->references('id')
                ->on('gbf_characters')
                ->cascadeOnDelete();
        });
    }
};
