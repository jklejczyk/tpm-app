<?php

declare(strict_types=1);

use App\Models\User;

function fromSpaLoginThrottle(): array
{
    return ['Origin' => 'http://localhost:5173'];
}

it('throttles repeated failed logins with 429', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->withHeaders(fromSpaLoginThrottle())
            ->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertStatus(422);
    }

    $this->withHeaders(fromSpaLoginThrottle())
        ->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertStatus(429);
});

it('does not throttle a different email from the same IP', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->manager()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->withHeaders(fromSpaLoginThrottle())
            ->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertStatus(422);
    }

    $this->withHeaders(fromSpaLoginThrottle())
        ->postJson('/api/v1/login', ['email' => $otherUser->email, 'password' => 'password'])
        ->assertOk();
});
