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

function progressionStage(string $name, int $sortOrder = 1): GbfCharacterStage
{
    return GbfCharacterStage::create([
        'character_id' => null,
        'stage_key' => str($name)->slug(),
        'name' => $name,
        'sort_order' => $sortOrder,
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

test('progression index only lists characters already configured', function () {
    $admin = progressionAdmin();
    $configuredCharacter = progressionCharacter();
    $configuredCharacter->stages()->create([
        'stage_key' => 'stage-one',
        'name' => 'Stage One',
        'sort_order' => 1,
    ]);
    progressionCharacter('Not Configured Character');
    progressionStage('Unassigned Stage');

    $this->actingAs($admin)
        ->get(route('admin.progression.index'))
        ->assertOk()
        ->assertSee($configuredCharacter->name)
        ->assertDontSee('Not Configured Character')
        ->assertDontSee('Unassigned Stage');
});

test('admin can open create page and select existing characters stages and items', function () {
    $admin = progressionAdmin();
    $character = progressionCharacter();
    $stage = progressionStage('Transcendance 1');
    $material = progressionMaterial('Silver Centrum');

    $this->actingAs($admin)
        ->get(route('admin.progression.create'))
        ->assertOk()
        ->assertSee($character->name)
        ->assertSee($stage->name)
        ->assertSee($material->name);
});

test('admin can assign multiple existing stages and item quantities to a character', function () {
    $admin = progressionAdmin();
    $character = progressionCharacter();
    $firstStage = progressionStage('Transcendance 1', 1);
    $secondStage = progressionStage('Transcendance 2', 2);
    $firstMaterial = progressionMaterial('Silver Centrum');
    $secondMaterial = progressionMaterial('Rainbow Prism');

    $response = $this->actingAs($admin)->post(route('admin.progression.store'), [
        'character_id' => $character->id,
        'stages' => [
            [
                'stage_id' => $firstStage->id,
                'materials' => [
                    ['material_id' => $firstMaterial->id, 'quantity' => 10],
                    ['material_id' => $secondMaterial->id, 'quantity' => 20],
                ],
            ],
            [
                'stage_id' => $secondStage->id,
                'materials' => [
                    ['material_id' => $firstMaterial->id, 'quantity' => 30],
                ],
            ],
        ],
    ]);

    $response
        ->assertRedirect(route('admin.progression.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('gbf_character_stages', [
        'id' => $firstStage->id,
        'character_id' => $character->id,
    ]);
    $this->assertDatabaseHas('gbf_character_stages', [
        'id' => $secondStage->id,
        'character_id' => $character->id,
    ]);
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

test('progression rejects stages owned by another character and duplicate stages or items', function () {
    $admin = progressionAdmin();
    $character = progressionCharacter();
    $otherCharacter = progressionCharacter('Other Character');
    $otherStage = $otherCharacter->stages()->create([
        'stage_key' => 'other-stage',
        'name' => 'Other Stage',
        'sort_order' => 1,
    ]);
    $stage = progressionStage('Available Stage');
    $material = progressionMaterial('Silver Centrum');

    $this->actingAs($admin)
        ->post(route('admin.progression.store'), [
            'character_id' => $character->id,
            'stages' => [
                [
                    'stage_id' => $otherStage->id,
                    'materials' => [
                        ['material_id' => $material->id, 'quantity' => 1],
                    ],
                ],
            ],
        ])
        ->assertSessionHasErrors('stages.0.stage_id');

    $this->post(route('admin.progression.store'), [
        'character_id' => $character->id,
        'stages' => [
            [
                'stage_id' => $stage->id,
                'materials' => [
                    ['material_id' => $material->id, 'quantity' => 1],
                    ['material_id' => $material->id, 'quantity' => 2],
                ],
            ],
        ],
    ])->assertSessionHasErrors('stages.0.materials');

    $this->post(route('admin.progression.store'), [
        'character_id' => $character->id,
        'stages' => [
            [
                'stage_id' => $stage->id,
                'materials' => [
                    ['material_id' => $material->id, 'quantity' => 1],
                ],
            ],
            [
                'stage_id' => $stage->id,
                'materials' => [
                    ['material_id' => $material->id, 'quantity' => 2],
                ],
            ],
        ],
    ])->assertSessionHasErrors('stages.1.stage_id');

    $this->assertDatabaseHas('gbf_character_stages', [
        'id' => $stage->id,
        'character_id' => null,
    ]);
});

test('non-admin cannot manage progression', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.progression.create'))
        ->assertForbidden();
});
