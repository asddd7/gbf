<?php

use App\Models\Role;
use App\Models\User;

function databaseStructureAdmin(): User
{
    $role = Role::create(['name' => 'admin']);

    return User::factory()->create(['role_id' => $role->id]);
}

test('admin can view database module relations and connected files', function () {
    $this->actingAs(databaseStructureAdmin())
        ->get(route('admin.database-structure.index', ['module' => 'progression']))
        ->assertOk()
        ->assertSee('Character Progression')
        ->assertSee('gbf_character_stage_materials')
        ->assertSee('app/Http/Controllers/Admin/GbfProgressionController.php')
        ->assertSee('resources/views/admin/progression/show.blade.php')
        ->assertSee('Teks untuk disalin')
        ->assertSee('Salin Keterangan');
});

test('database structure page can show different modules and rejects unknown modules', function () {
    $admin = databaseStructureAdmin();
    $this->actingAs($admin)
        ->get(route('admin.database-structure.index', ['module' => 'characters']))
        ->assertOk()
        ->assertSee('gbf_characters')
        ->assertSee('GbfCharacterController.php')
        ->assertDontSee('GbfProgressionController.php');

    $this->get(route('admin.database-structure.index', ['module' => 'unknown']))
        ->assertSessionHasErrors('module');
});

test('non-admin users cannot view database structure', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.database-structure.index'))
        ->assertForbidden();
});
