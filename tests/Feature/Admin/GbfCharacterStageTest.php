<?php

use App\Models\GbfCharacter;
use App\Models\GbfCharacterStage;
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

test('admin can view stage names and order without character or material relations', function () {
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
        ->assertSee('Urutan')
        ->assertDontSee('Stage Test Character')
        ->assertDontSee('Material</th>');

    $this->get(route('admin.stages.create'))
        ->assertOk()
        ->assertSee('Nama Tahap')
        ->assertSee('Urutan')
        ->assertDontSee('character_id')
        ->assertDontSee('stage_key');

    $this->get(route('admin.stages.edit', $stage))
        ->assertOk()
        ->assertSee('Uncap 1')
        ->assertDontSee('Stage Test Character');
});

test('admin can create an unassigned character stage', function () {
    $admin = stageAdminUser();

    $response = $this->actingAs($admin)->post(route('admin.stages.store'), [
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ]);

    $response
        ->assertRedirect(route('admin.stages.index'))
        ->assertSessionHasNoErrors();

    $stage = GbfCharacterStage::where('name', 'Uncap 1')->firstOrFail();

    $this->assertNull($stage->character_id);
    $this->assertSame('uncap-1', $stage->stage_key);
    $this->assertDatabaseHas('gbf_character_stages', [
        'id' => $stage->id,
        'character_id' => null,
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ]);
});

test('admin can update an unassigned stage name and order', function () {
    $admin = stageAdminUser();
    $stage = GbfCharacterStage::create([
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($admin)->put(
        route('admin.stages.update', $stage),
        [
            'name' => 'Uncap 2',
            'sort_order' => 2,
        ]
    );

    $response
        ->assertRedirect(route('admin.stages.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('gbf_character_stages', [
        'id' => $stage->id,
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 2',
        'sort_order' => 2,
    ]);
    $this->assertDatabaseHas('gbf_character_stages', [
        'id' => $stage->id,
        'character_id' => null,
        'stage_key' => 'uncap-1',
    ]);
});

test('admin can delete an unassigned stage', function () {
    $admin = stageAdminUser();
    $stage = GbfCharacterStage::create([
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($admin)->delete(
        route('admin.stages.destroy', $stage)
    );

    $response->assertRedirect(route('admin.stages.index'));
    $this->assertDatabaseMissing('gbf_character_stages', ['id' => $stage->id]);
});

test('non-admin users cannot manage character stages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.stages.index'))
        ->assertForbidden();
});

test('admin can assign an unassigned stage from the character form', function () {
    $admin = stageAdminUser();
    $character = stageCharacter();
    $stage = GbfCharacterStage::create([
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.characters.edit', $character))
        ->assertOk()
        ->assertSee('Hubungkan Tahap yang Belum Terhubung')
        ->assertSee('Uncap 1');

    $response = $this->put(route('admin.characters.update', $character), [
        'name' => $character->name,
        'character_type' => $character->character_type,
        'element' => $character->element,
        'sort_order' => $character->sort_order,
        'unassigned_stage_ids' => [$stage->id],
    ]);

    $response
        ->assertRedirect(route('admin.characters.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('gbf_character_stages', [
        'id' => $stage->id,
        'character_id' => $character->id,
    ]);
});

test('deleting a character leaves its stages unassigned', function () {
    $admin = stageAdminUser();
    $character = stageCharacter();
    $stage = GbfCharacterStage::create([
        'character_id' => $character->id,
        'stage_key' => 'uncap-1',
        'name' => 'Uncap 1',
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.characters.destroy', $character))
        ->assertRedirect(route('admin.characters.index'));

    $this->assertDatabaseHas('gbf_character_stages', [
        'id' => $stage->id,
        'character_id' => null,
    ]);
});
