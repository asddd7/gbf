<?php

use App\Models\GbfCharacter;
use App\Models\GbfCharacterStage;
use App\Models\GbfCharacterStageMaterial;
use App\Models\GbfMaterial;
use App\Models\Role;
use App\Models\User;

function stageAdminUser(): User
{
    $role = Role::create(['name' => 'admin']);

    return User::factory()->create(['role_id' => $role->id]);
}

function stageCharacter(string $name = 'Stage Test Character'): GbfCharacter
{
    return GbfCharacter::create([
        'name' => $name,
        'slug' => str($name)->slug(),
        'character_type' => 'eternal',
        'element' => 'fire',
        'sort_order' => 0,
    ]);
}

test('admin can view character stages and create and edit forms', function () {
    $admin = stageAdminUser();
    $character = stageCharacter();
    $stage = GbfCharacterStage::create([
        'character_id' => $character->id,
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.stages.index'))
        ->assertOk()
        ->assertSee('Uncap 1')
        ->assertSee('Stage Test Character')
        ->assertSee('uncap-1');

    $this->get(route('admin.stages.create'))
        ->assertOk()
        ->assertSee('Pilih character')
        ->assertSee('Stage Test Character');

    $this->get(route('admin.stages.edit', $stage))
        ->assertOk()
        ->assertSee('Uncap 1')
        ->assertSee('value="'.$character->id.'"', false);
});

test('admin can create a character stage', function () {
    $admin = stageAdminUser();
    $character = stageCharacter();

    $response = $this->actingAs($admin)->post(route('admin.stages.store'), [
        'character_id' => $character->id,
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'description' => 'First uncap stage',
        'sort_order' => 1,
    ]);

    $response
        ->assertRedirect(route('admin.stages.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('gbf_character_stages', [
        'character_id' => $character->id,
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'description' => 'First uncap stage',
        'sort_order' => 1,
    ]);
});

test('stage keys must be unique per character but may repeat across characters', function () {
    $admin = stageAdminUser();
    $firstCharacter = stageCharacter();
    $secondCharacter = stageCharacter('Another Character');

    GbfCharacterStage::create([
        'character_id' => $firstCharacter->id,
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stages.store'), [
            'character_id' => $firstCharacter->id,
            'stage_key' => 'uncap-1',
            'name' => 'Duplicate',
            'sort_order' => 2,
        ])
        ->assertSessionHasErrors('stage_key');

    $this->post(route('admin.stages.store'), [
        'character_id' => $secondCharacter->id,
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ])
        ->assertRedirect(route('admin.stages.index'))
        ->assertSessionHasNoErrors();
});

test('admin can update a stage and keep its material associations', function () {
    $admin = stageAdminUser();
    $character = stageCharacter();
    $stage = GbfCharacterStage::create([
        'character_id' => $character->id,
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ]);
    $material = GbfMaterial::create([
        'name' => 'Stage Material',
        'slug' => 'stage-material',
        'unit' => 'item',
    ]);
    GbfCharacterStageMaterial::create([
        'character_stage_id' => $stage->id,
        'material_id' => $material->id,
        'quantity' => 4,
    ]);

    $response = $this->actingAs($admin)->put(
        route('admin.stages.update', $stage),
        [
            'character_id' => $character->id,
            'stage_key' => 'uncap-2',
            'name' => 'Uncap 2',
            'description' => 'Updated stage',
            'sort_order' => 2,
        ]
    );

    $response
        ->assertRedirect(route('admin.stages.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('gbf_character_stages', [
        'id' => $stage->id,
        'stage_key' => 'uncap-2',
        'name' => 'Uncap 2',
        'sort_order' => 2,
    ]);
    $this->assertDatabaseHas('gbf_character_stage_materials', [
        'character_stage_id' => $stage->id,
        'material_id' => $material->id,
        'quantity' => 4,
    ]);
});

test('admin can delete a stage and its material associations', function () {
    $admin = stageAdminUser();
    $character = stageCharacter();
    $stage = GbfCharacterStage::create([
        'character_id' => $character->id,
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ]);
    $material = GbfMaterial::create([
        'name' => 'Stage Material',
        'slug' => 'stage-material',
        'unit' => 'item',
    ]);
    GbfCharacterStageMaterial::create([
        'character_stage_id' => $stage->id,
        'material_id' => $material->id,
        'quantity' => 4,
    ]);

    $response = $this->actingAs($admin)->delete(
        route('admin.stages.destroy', $stage)
    );

    $response->assertRedirect(route('admin.stages.index'));
    $this->assertDatabaseMissing('gbf_character_stages', ['id' => $stage->id]);
    $this->assertDatabaseMissing('gbf_character_stage_materials', [
        'character_stage_id' => $stage->id,
    ]);
    $this->assertDatabaseHas('gbf_materials', ['id' => $material->id]);
});

test('non-admin users cannot manage character stages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.stages.index'))
        ->assertForbidden();
});
