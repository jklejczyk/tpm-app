<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\UserDirectory;
use Illuminate\Support\Facades\DB;

it('resolves a user id to its display name', function () {
    $user = User::factory()->create(['name' => 'Alice']);

    expect((new UserDirectory)->name((string) $user->id))->toBe('Alice');
});

it('returns null for an unknown user id', function () {
    expect((new UserDirectory)->name('999999'))->toBeNull();
});

it('returns null for a non-numeric id without crashing the query', function () {
    expect((new UserDirectory)->name('not-a-number'))->toBeNull();
});

it('resolves many ids in a single query and then serves from cache', function () {
    $users = User::factory()->count(3)->create();
    $directory = new UserDirectory;

    $queries = 0;
    DB::listen(function () use (&$queries): void {
        $queries++;
    });

    $directory->preload($users->map(fn (User $u) => (string) $u->id)->all());
    foreach ($users as $user) {
        expect($directory->name((string) $user->id))->toBe($user->name);
    }

    expect($queries)->toBe(1);
});

it('does not query again on a repeated lookup', function () {
    $user = User::factory()->create();
    $directory = new UserDirectory;
    $directory->name((string) $user->id);

    $queries = 0;
    DB::listen(function () use (&$queries): void {
        $queries++;
    });

    $directory->name((string) $user->id);

    expect($queries)->toBe(0);
});
