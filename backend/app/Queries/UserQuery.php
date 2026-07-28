<?php

namespace App\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tpm\Shared\Role;

final class UserQuery
{
    /**
     * @return Collection<int, User>
     */
    public function byRole(Role $role): Collection
    {
        return User::query()
            ->where('role', $role->value)
            ->orderBy('name')
            ->get();
    }
}
