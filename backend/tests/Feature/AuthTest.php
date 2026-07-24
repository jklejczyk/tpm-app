<?php

declare(strict_types=1);

use App\Models\User;

it('issues a token on valid login', function () {
    $user = User::factory()->manager()->create();

    $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'password'])
        ->assertOk()
        ->assertJsonStructure(['token', 'role'])
        ->assertJsonPath('role', 'manager');
});

it('rejects invalid credentials with 422', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertStatus(422);
});

it('revokes the token on logout', function () {
    $user = User::factory()->manager()->create();
    $token = $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'password'])
        ->json('token');

    expect($user->tokens()->count())->toBe(1);

    $this->withToken($token)->postJson('/api/v1/logout')->assertOk();

    expect($user->fresh()->tokens()->count())->toBe(0);
});
