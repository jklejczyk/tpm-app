<?php

namespace App\Http\Resources\Api\V1;

use App\Models\MachineModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MachineResource extends JsonResource
{
    /**
     * @return array<string, string>
     */
    public function toArray(Request $request): array
    {
        /** @var MachineModel $machine */
        $machine = $this->resource;

        return [
            'id' => $machine->id,
            'name' => $machine->name,
        ];
    }
}
