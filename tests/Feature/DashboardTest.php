<?php

use App\Models\GbfCharacter;
use App\Models\User;

function dashboardCharacter(
    string $name,
    string $type,
    int $sortOrder = 0
): GbfCharacter {
    return GbfCharacter::create([
        'name' => $name,
        'slug' => str($name)->slug(),
        'character_type' => $type,
        'element' => 'fire',
        'description' => $name.' progression materials',
        'sort_order' => $sortOrder,
    ]);
}

test('guest dashboard shows eternal and evoker character tabs', function () {
    dashboardCharacter('Seofon', 'eternal');
    dashboardCharacter('Maria Theresa', 'evoker');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Rencanakan perjalananmu.')
        ->assertSee('Eternals')
        ->assertSee('Evokers')
        ->assertSee('Seofon')
        ->assertSee('Maria Theresa')
        ->assertSee('aria-selected="true"', false)
        ->assertSee('aria-selected="false"', false)
        ->assertSee('hidden', false);
});

test('signed-in user dashboard shows their welcome and available characters', function () {
    $user = User::factory()->create(['name' => 'Granblue Player']);
    dashboardCharacter('Siete', 'eternal', 1);
    dashboardCharacter('Nier', 'evoker', 2);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Selamat datang, Granblue Player.')
        ->assertSee('Siete')
        ->assertSee('Nier');
});

test('dashboard displays a helpful empty state for character categories without entries', function () {
    dashboardCharacter('Seofon', 'eternal');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Belum ada Evoker')
        ->assertSee('1 karakter')
        ->assertSee('0 karakter');
});
