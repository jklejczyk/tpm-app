<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\ListUsersRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Tpm\Shared\Role;

final class UserController extends Controller
{
    public function byRole(ListUsersRequest $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->where('role', $request->enum('role', Role::class))
            ->orderBy('name')
            ->get();

        return UserResource::collection($users);
    }
}
