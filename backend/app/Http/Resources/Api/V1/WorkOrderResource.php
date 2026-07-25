<?php

namespace App\Http\Resources\Api\V1;

use App\Data\WorkOrderData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
{
    /**
     * @return array<string, string|null>
     */
    public function toArray(Request $request): array
    {
        /** @var WorkOrderData $data */
        $data = $this->resource;
        $workOrder = $data->workOrder;

        return [
            'id' => $workOrder->id()->value,
            'machineId' => $workOrder->machineId()->value,
            'status' => $workOrder->status()->value,
            'reason' => $workOrder->reason()->value,
            'reportedBy' => $workOrder->reportedBy()->value,
            'reportedByName' => $data->reportedByName,
            'assignedTo' => $workOrder->assignedTo()?->value,
            'assignedToName' => $data->assignedToName,
            'resolution' => $workOrder->resolution(),
            'holdReason' => $workOrder->holdReason(),
            'reportedAt' => $workOrder->reportedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
