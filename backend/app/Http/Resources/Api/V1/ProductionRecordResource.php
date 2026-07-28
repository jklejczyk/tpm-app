<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ProductionRecordModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionRecordResource extends JsonResource
{
    /**
     * @return array<string, string>
     */
    public function toArray(Request $request): array
    {
        /** @var ProductionRecordModel $record */
        $record = $this->resource;

        return [
            'machineId' => $record->machine_id,
            'machineName' => $record->machine->name,
            'periodStart' => $record->period_start->format(\DateTimeInterface::ATOM),
            'periodEnd' => $record->period_end->format(\DateTimeInterface::ATOM),
        ];
    }
}
