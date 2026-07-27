<?php

namespace App\Http\Resources\Api\V1;

use App\Models\WorkOrderModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
{
    /**
     * @return array<string, string|null>
     */
    public function toArray(Request $request): array
    {
        /** @var WorkOrderModel $wo */
        $wo = $this->resource;

        return [
            'id' => $wo->id,
            'machineId' => $wo->machine_id,
            'status' => $wo->status,
            'reason' => $wo->reason,
            'reportedBy' => (string) $wo->reported_by,
            'reportedByName' => $wo->reporter->name,
            'assignedTo' => $wo->assigned_to !== null ? (string) $wo->assigned_to : null,
            'assignedToName' => $wo->assignee?->name,
            'resolution' => $wo->resolution,
            'holdReason' => $wo->hold_reason,
            'reportedAt' => $wo->reported_at->format(\DateTimeInterface::ATOM),
        ];
    }
}
