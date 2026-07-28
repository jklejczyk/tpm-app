<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MachineResource;
use App\Models\MachineModel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class MachineController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return MachineResource::collection(
            MachineModel::query()->orderBy('name')->get(),
        );
    }
}
