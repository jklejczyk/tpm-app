<?php

use App\Models\MachineModel;
use App\Models\User;

it('lists machines for an authenticated user, ordered by name', function () {
    $user = User::factory()->create();
    MachineModel::factory()->create(['id' => 'press-01', 'name' => 'Hydraulic Press #1']);
    MachineModel::factory()->create(['id' => 'cnc-01', 'name' => 'CNC Mill #1']);

    $this->actingAs($user)
        ->getJson('/api/v1/machines')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', 'cnc-01')
        ->assertJsonPath('data.0.name', 'CNC Mill #1')
        ->assertJsonPath('data.1.id', 'press-01')
        ->assertJsonStructure(['data' => [['id', 'name']]]);
});

it('requires authentication', function () {
    $this->getJson('/api/v1/machines')->assertUnauthorized();
});
