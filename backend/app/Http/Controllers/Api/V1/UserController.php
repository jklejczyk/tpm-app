<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\ListUsersRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Queries\UserQuery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class UserController extends Controller
{
    public function __construct(private readonly UserQuery $query) {}

    public function byRole(ListUsersRequest $request): AnonymousResourceCollection
    {
        return UserResource::collection($this->query->byRole($request->role()));
    }
}
