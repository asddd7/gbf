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

test('guest dashboard presents dashboard and login navigation without listing characters', function () {
    dashboardCharacter('Seofon', 'eternal');
    dashboardCharacter('Maria Theresa', 'evoker');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Eternal')
        ->assertSee('Evoker')
        ->assertSee(route('characters.eternals'))
        ->assertSee(route('characters.evokers'))
        ->assertSee('Navigasi utama')
        ->assertSee('aria-label="Dashboard"', false)
        ->assertSee('aria-label="Login"', false)
        ->assertSee(route('login'))
        ->assertDontSee(route('register'))
        ->assertDontSee('Seofon')
        ->assertDontSee('Maria Theresa');
});

test('eternal and evoker options open separate pages with only their characters', function () {
    dashboardCharacter('Seofon', 'eternal');
    dashboardCharacter('Siete', 'eternal', 1);
    dashboardCharacter('Maria Theresa', 'evoker');
    dashboardCharacter('Nier', 'evoker', 1);

    $this->get(route('characters.eternals'))
        ->assertOk()
        ->assertSee('Eternals')
        ->assertSee('Seofon')
        ->assertSee('Siete')
        ->assertDontSee('Maria Theresa')
        ->assertDontSee('Nier');

    $this->get(route('characters.evokers'))
        ->assertOk()
        ->assertSee('Evokers')
        ->assertSee('Maria Theresa')
        ->assertSee('Nier')
        ->assertDontSee('Seofon')
        ->assertDontSee('Siete');
});

test('dashboard category pages show empty state when category has no characters', function () {
    dashboardCharacter('Seofon', 'eternal');

    $this->get(route('characters.evokers'))
        ->assertOk()
        ->assertSee('Belum ada karakter Evoker');
});

test('signed-in users can access the same category pages', function () {
    $user = User::factory()->create(['name' => 'Granblue Player']);
    dashboardCharacter('Nier', 'evoker');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Evoker')
        ->assertSee(route('characters.eternals'))
        ->assertSee(route('characters.evokers'))
        ->assertSee('aria-label="Profil"', false)
        ->assertSee(route('profile.edit'))
        ->assertDontSee('aria-label="Login"', false)
        ->assertDontSee(route('register'))
        ->assertDontSee('Nier');

    $this->get(route('characters.evokers'))
        ->assertOk()
        ->assertSee('Nier');
});
