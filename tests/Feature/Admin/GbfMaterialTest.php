<?php

use App\Models\GbfCharacter;
use App\Models\GbfCharacterStage;
use App\Models\GbfCharacterStageMaterial;
use App\Models\GbfMaterial;
use App\Models\Role;
use App\Models\User;

function materialAdmin(): User
{
    $role = Role::create(['name' => 'admin']);

    return User::factory()->create(['role_id' => $role->id]);
}

function materialStage(string $key = 'awakening'): GbfCharacterStage
{
    $character = GbfCharacter::create([
        'name' => 'Test Character',
        'slug' => 'test-character-'.$key,
        'character_type' => 'eternal',
        'element' => 'fire',
        'sort_order' => 0,
    ]);

    return GbfCharacterStage::create([
        'character_id' => $character->id,
        'stage_key' => $key,
        'name' => 'Awakening',
        'sort_order' => 0,
    ]);
}

test('admin can create a material and associate it with a character stage', function () {
    $admin = materialAdmin();
    $stage = materialStage();

    $response = $this->actingAs($admin)->post(route('admin.materials.store'), [
        'name' => 'Rusted Weapon',
        'category' => 'Weapon',
        'unit' => 'piece',
        'stage_ids' => [$stage->id],
        'quantities' => [$stage->id => 12],
        'notes' => [$stage->id => 'Any weapon type'],
    ]);

    $material = GbfMaterial::where('slug', 'rusted-weapon')->firstOrFail();

    $response
        ->assertRedirect(route('admin.materials.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('gbf_character_stage_materials', [
        'material_id' => $material->id,
        'character_stage_id' => $stage->id,
        'quantity' => 12,
        'notes' => 'Any weapon type',
    ]);
});

test('admin can update a material and synchronize its character stage associations', function () {
    $admin = materialAdmin();
    $stage = materialStage();
    $otherStage = materialStage('uncap');
    $material = GbfMaterial::create([
        'name' => 'Old Material',
        'slug' => 'old-material',
        'unit' => 'item',
    ]);
    GbfCharacterStageMaterial::create([
        'material_id' => $material->id,
        'character_stage_id' => $stage->id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($admin)->put(
        route('admin.materials.update', $material),
        [
            'name' => 'Updated Material',
            'category' => 'Treasure',
            'unit' => 'piece',
            'stage_ids' => [$otherStage->id],
            'quantities' => [$otherStage->id => 7],
            'notes' => [$otherStage->id => 'Updated note'],
        ]
    );

    $response
        ->assertRedirect(route('admin.materials.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('gbf_materials', [
        'id' => $material->id,
        'slug' => 'updated-material',
        'category' => 'Treasure',
        'unit' => 'piece',
    ]);
    $this->assertDatabaseMissing('gbf_character_stage_materials', [
        'material_id' => $material->id,
        'character_stage_id' => $stage->id,
    ]);
    $this->assertDatabaseHas('gbf_character_stage_materials', [
        'material_id' => $material->id,
        'character_stage_id' => $otherStage->id,
        'quantity' => 7,
        'notes' => 'Updated note',
    ]);
});

test('admin can delete a material and its stage associations', function () {
    $admin = materialAdmin();
    $stage = materialStage();
    $material = GbfMaterial::create([
        'name' => 'Material to Delete',
        'slug' => 'material-to-delete',
        'unit' => 'item',
    ]);
    GbfCharacterStageMaterial::create([
        'material_id' => $material->id,
        'character_stage_id' => $stage->id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($admin)->delete(
        route('admin.materials.destroy', $material)
    );

    $response->assertRedirect(route('admin.materials.index'));
    $this->assertDatabaseMissing('gbf_materials', ['id' => $material->id]);
    $this->assertDatabaseMissing('gbf_character_stage_materials', [
        'material_id' => $material->id,
    ]);
});

test('non-admin users cannot manage materials', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.materials.index'))
        ->assertForbidden();
});

test('admin can view material list and assignment forms', function () {
    $admin = materialAdmin();
    $stage = materialStage();
    $material = GbfMaterial::create([
        'name' => 'Stage Material',
        'slug' => 'stage-material',
        'unit' => 'item',
    ]);

    $material->stageMaterials()->create([
        'character_stage_id' => $stage->id,
        'quantity' => 3,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.materials.index'))
        ->assertOk()
        ->assertSee('Stage Material')
        ->assertSee('Test Character');

    $this->get(route('admin.materials.create'))
        ->assertOk()
        ->assertSee('Relasi Tahap Character')
        ->assertSee('Awakening');

    $this->get(route('admin.materials.edit', $material))
        ->assertOk()
        ->assertSee('Stage Material')
        ->assertSee('value="3"', false);
});

test('admin can create a character with stages for material assignments', function () {
    $admin = materialAdmin();

    $response = $this->actingAs($admin)->post(route('admin.characters.store'), [
        'name' => 'New Character',
        'character_type' => 'eternal',
        'sort_order' => 0,
        'stages' => [
            [
                'stage_key' => 'uncap-1',
                'name' => 'Uncap 1',
                'sort_order' => 1,
                'description' => 'First uncap stage',
            ],
        ],
    ]);

    $character = GbfCharacter::where('slug', 'new-character')->firstOrFail();

    $response
        ->assertRedirect(route('admin.characters.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('gbf_character_stages', [
        'character_id' => $character->id,
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ]);
});

test('admin can update and remove character stages', function () {
    $admin = materialAdmin();
    $stage = materialStage();
    $material = GbfMaterial::create([
        'name' => 'Stage-linked Material',
        'slug' => 'stage-linked-material',
        'unit' => 'item',
    ]);
    GbfCharacterStageMaterial::create([
        'material_id' => $material->id,
        'character_stage_id' => $stage->id,
        'quantity' => 2,
    ]);

    $character = $stage->character;
    $updateData = [
        'name' => $character->name,
        'character_type' => $character->character_type,
        'element' => $character->element,
        'sort_order' => $character->sort_order,
        'stages' => [
            [
                'id' => $stage->id,
                'stage_key' => $stage->stage_key,
                'name' => 'Updated Stage',
                'sort_order' => 1,
            ],
        ],
    ];

    $this->actingAs($admin)
        ->put(route('admin.characters.update', $character), $updateData)
        ->assertRedirect(route('admin.characters.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('gbf_character_stages', [
        'id' => $stage->id,
        'name' => 'Updated Stage',
    ]);

    unset($updateData['stages']);

    $this->put(route('admin.characters.update', $character), $updateData)
        ->assertRedirect(route('admin.characters.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('gbf_character_stages', ['id' => $stage->id]);
    $this->assertDatabaseMissing('gbf_character_stage_materials', [
        'material_id' => $material->id,
        'character_stage_id' => $stage->id,
    ]);
});
