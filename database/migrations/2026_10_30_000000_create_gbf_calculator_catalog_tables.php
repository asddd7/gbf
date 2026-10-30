<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gbf_characters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('character_type', 20)->index(); // eternal or evoker
            $table->string('element', 20)->nullable()->index();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('gbf_materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category', 50)->nullable()->index();
            $table->string('unit', 30)->default('item');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('gbf_character_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('gbf_characters')->cascadeOnDelete();
            $table->string('stage_key', 80);
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['character_id', 'stage_key']);
            $table->index(['character_id', 'sort_order']);
        });

        Schema::create('gbf_character_stage_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_stage_id')->constrained('gbf_character_stages')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('gbf_materials')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['character_stage_id', 'material_id'], 'gbf_stage_material_unique');
            $table->index('material_id', 'gbf_stage_material_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gbf_character_stage_materials');
        Schema::dropIfExists('gbf_character_stages');
        Schema::dropIfExists('gbf_materials');
        Schema::dropIfExists('gbf_characters');
    }
};
