<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('lists users of a given role, ordered by name', function () {
    User::factory()->technician()->create(['name' => 'Bob']);
    User::factory()->technician()->create(['name' => 'Alice']);
    User::factory()->manager()->create(['name' => 'Mark']);
    User::factory()->create(['name' => 'Olivia']); // operator

    Sanctum::actingAs(User::factory()->manager()->create());

    $this->getJson('/api/v1/users/role/technician')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Alice')
        ->assertJsonPath('data.1.name', 'Bob');
});

it('filters by the manager role', function () {
    User::factory()->technician()->create(['name' => 'Tina']);
    User::factory()->manager()->create(['name' => 'Mark']);

    Sanctum::actingAs(User::factory()->manager()->create(['name' => 'Zoe']));

    $this->getJson('/api/v1/users/role/manager')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Mark')
        ->assertJsonPath('data.1.name', 'Zoe');
});

it('rejects an unknown role with 422', function () {
    Sanctum::actingAs(User::factory()->manager()->create());

    $this->getJson('/api/v1/users/role/wizard')->assertStatus(422);
});

it('rejects unauthenticated listing with 401', function () {
    $this->getJson('/api/v1/users/role/technician')->assertUnauthorized();
});
