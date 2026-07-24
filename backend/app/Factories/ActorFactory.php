<?php

declare(strict_types=1);

namespace App\Factories;

use App\Models\User;
use Tpm\Shared\Actor;
use Tpm\Shared\UserId;

final class ActorFactory
{
    public function fromUser(User $user): Actor
    {
        return new Actor(
            new UserId((string) $user->id),
            $user->role,
        );
    }
}
