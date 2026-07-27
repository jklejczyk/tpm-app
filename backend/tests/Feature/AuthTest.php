<?php

declare(strict_types=1);

use App\Models\User;

function fromSpa(): array
{
    return ['Origin' => 'http://localhost:5173'];
}

it('logs in with valid credentials and starts a session', function () {
    $user = User::factory()->manager()->create();

    $this->withHeaders(fromSpa())
        ->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'password'])
        ->assertOk()
        ->assertJsonStructure(['user' => ['id', 'name', 'role']])
        ->assertJsonPath('user.role', 'manager');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials with 422', function () {
    $user = User::factory()->create();

    $this->withHeaders(fromSpa())
        ->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertStatus(422);

    $this->assertGuest();
});

it('logs out an authenticated user', function () {
    $user = User::factory()->manager()->create();

    $this->actingAs($user)
        ->withHeaders(fromSpa())
        ->postJson('/api/v1/logout')
        ->assertOk()
        ->assertJson(['message' => 'Logged out.']);
});
