<?php

use App\Models\GbfCharacter;
use App\Models\GbfCharacterStage;
use App\Models\GbfMaterial;
use App\Models\Role;
use App\Models\User;

function progressionAdmin(): User
{
    $role = Role::create(['name' => 'admin']);

    return User::factory()->create(['role_id' => $role->id]);
}

function progressionCharacter(string $name = 'Progression Character'): GbfCharacter
{
    return GbfCharacter::create([
        'name' => $name,
        'slug' => str($name)->slug(),
        'character_type' => 'eternal',
        'element' => 'fire',
        'sort_order' => 0,
    ]);
}

function progressionMaterial(string $name): GbfMaterial
{
    return GbfMaterial::create([
        'name' => $name,
        'slug' => str($name)->slug(),
        'unit' => 'item',
    ]);
}

test('admin can select a character and view its stages and materials', function () {
    $admin = progressionAdmin();
    $character = progressionCharacter();
    $stage = $character->stages()->create([
        'stage_key' => 'transcendance-1',
        'name' => 'Transcendance 1',
        'sort_order' => 1,
    ]);
    $material = progressionMaterial('Silver Centrum');
    $stage->materials()->create([
        'material_id' => $material->id,
        'quantity' => 12,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.progression.index'))
        ->assertOk()
        ->assertSee($character->name)
        ->assertDontSee('Transcendance 1');

    $this->get(route('admin.progression.index', ['character_id' => $character->id]))
        ->assertOk()
        ->assertSee('Transcendance 1')
        ->assertSee('Silver Centrum')
        ->assertSee('value="12"', false);
});

test('admin can save multiple stages and material quantities for one character', function () {
    $admin = progressionAdmin();
    $character = progressionCharacter();
    $firstMaterial = progressionMaterial('Silver Centrum');
    $secondMaterial = progressionMaterial('Rainbow Prism');

    $response = $this->actingAs($admin)->put(route('admin.progression.update'), [
        'character_id' => $character->id,
        'stages' => [
            [
                'name' => 'Transcendance 1',
                'sort_order' => 1,
                'materials' => [
                    ['material_id' => $firstMaterial->id, 'quantity' => 10],
                    ['material_id' => $secondMaterial->id, 'quantity' => 20],
                ],
            ],
            [
                'name' => 'Transcendance 2',
                'sort_order' => 2,
                'materials' => [
                    ['material_id' => $firstMaterial->id, 'quantity' => 30],
                ],
            ],
        ],
    ]);

    $response
        ->assertRedirect(route('admin.progression.index', [
            'character_id' => $character->id,
        ]))
        ->assertSessionHasNoErrors();

    $firstStage = $character->stages()->where('name', 'Transcendance 1')->firstOrFail();
    $secondStage = $character->stages()->where('name', 'Transcendance 2')->firstOrFail();

    $this->assertDatabaseHas('gbf_character_stage_materials', [
        'character_stage_id' => $firstStage->id,
        'material_id' => $firstMaterial->id,
        'quantity' => 10,
    ]);
    $this->assertDatabaseHas('gbf_character_stage_materials', [
        'character_stage_id' => $firstStage->id,
        'material_id' => $secondMaterial->id,
        'quantity' => 20,
    ]);
    $this->assertDatabaseHas('gbf_character_stage_materials', [
        'character_stage_id' => $secondStage->id,
        'material_id' => $firstMaterial->id,
        'quantity' => 30,
    ]);
});

test('saving progression updates existing stages and removes omitted stage assignments', function () {
    $admin = progressionAdmin();
    $character = progressionCharacter();
    $material = progressionMaterial('Silver Centrum');
    $stageToKeep = $character->stages()->create([
        'stage_key' => 'stage-one',
        'name' => 'Stage One',
        'sort_order' => 1,
    ]);
    $stageToRemove = $character->stages()->create([
        'stage_key' => 'stage-two',
        'name' => 'Stage Two',
        'sort_order' => 2,
    ]);
    $stageToKeep->materials()->create([
        'material_id' => $material->id,
        'quantity' => 2,
    ]);
    $stageToRemove->materials()->create([
        'material_id' => $material->id,
        'quantity' => 4,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.progression.update'), [
        'character_id' => $character->id,
        'stages' => [
            [
                'id' => $stageToKeep->id,
                'name' => 'Updated Stage',
                'sort_order' => 5,
                'materials' => [
                    ['material_id' => $material->id, 'quantity' => 9],
                ],
            ],
        ],
    ]);

    $response
        ->assertRedirect(route('admin.progression.index', [
            'character_id' => $character->id,
        ]))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('gbf_character_stages', [
        'id' => $stageToKeep->id,
        'name' => 'Updated Stage',
        'sort_order' => 5,
    ]);
    $this->assertDatabaseMissing('gbf_character_stages', ['id' => $stageToRemove->id]);
    $this->assertDatabaseHas('gbf_character_stage_materials', [
        'character_stage_id' => $stageToKeep->id,
        'material_id' => $material->id,
        'quantity' => 9,
    ]);
    $this->assertDatabaseMissing('gbf_character_stage_materials', [
        'character_stage_id' => $stageToRemove->id,
    ]);
});

test('progression rejects stages owned by another character and duplicate materials in a stage', function () {
    $admin = progressionAdmin();
    $character = progressionCharacter();
    $otherCharacter = progressionCharacter('Other Character');
    $otherStage = $otherCharacter->stages()->create([
        'stage_key' => 'other-stage',
        'name' => 'Other Stage',
        'sort_order' => 1,
    ]);
    $material = progressionMaterial('Silver Centrum');

    $this->actingAs($admin)
        ->put(route('admin.progression.update'), [
            'character_id' => $character->id,
            'stages' => [
                [
                    'id' => $otherStage->id,
                    'name' => 'Invalid Stage',
                    'sort_order' => 1,
                    'materials' => [],
                ],
            ],
        ])
        ->assertSessionHasErrors('stages.0.id');

    $this->put(route('admin.progression.update'), [
        'character_id' => $character->id,
        'stages' => [
            [
                'name' => 'Duplicate Material Stage',
                'sort_order' => 1,
                'materials' => [
                    ['material_id' => $material->id, 'quantity' => 1],
                    ['material_id' => $material->id, 'quantity' => 2],
                ],
            ],
        ],
    ])->assertSessionHasErrors();

    $this->assertDatabaseMissing('gbf_character_stages', [
        'character_id' => $character->id,
        'name' => 'Duplicate Material Stage',
    ]);
});

test('admin can remove all progression stages and non-admin cannot manage progression', function () {
    $admin = progressionAdmin();
    $character = progressionCharacter();
    $character->stages()->create([
        'stage_key' => 'stage-one',
        'name' => 'Stage One',
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.progression.update'), [
            'character_id' => $character->id,
        ])
        ->assertRedirect(route('admin.progression.index', [
            'character_id' => $character->id,
        ]))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('gbf_character_stages', [
        'character_id' => $character->id,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.progression.index'))
        ->assertForbidden();
});
