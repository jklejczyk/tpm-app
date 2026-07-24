<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tpm\WorkOrder\WorkOrder;

class WorkOrderResource extends JsonResource
{
    /**
     * @return array<string, string|null>
     */
    public function toArray(Request $request): array
    {
        /** @var WorkOrder $workOrder */
        $workOrder = $this->resource;

        return [
            'id' => $workOrder->id()->value,
            'machineId' => $workOrder->machineId()->value,
            'status' => $workOrder->status()->value,
            'reason' => $workOrder->reason()->value,
            'reportedBy' => $workOrder->reportedBy()->value,
            'assignedTo' => $workOrder->assignedTo()?->value,
            'resolution' => $workOrder->resolution(),
            'holdReason' => $workOrder->holdReason(),
        ];
    }
}