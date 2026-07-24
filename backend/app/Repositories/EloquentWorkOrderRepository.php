<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\WorkOrderModel;
use Tpm\Shared\MachineId;
use Tpm\Shared\UserId;
use Tpm\Shared\WorkOrderId;
use Tpm\WorkOrder\WorkOrder;
use Tpm\WorkOrder\WorkOrderReason;
use Tpm\WorkOrder\WorkOrderRepository;
use Tpm\WorkOrder\WorkOrderStatus;

final class EloquentWorkOrderRepository implements WorkOrderRepository
{
    public function findById(WorkOrderId $id): ?WorkOrder
    {
        $row = WorkOrderModel::find($id->value);

        if ($row === null) {
            return null;
        }

        return WorkOrder::reconstitute(
            new WorkOrderId($row->id),
            new MachineId($row->machine_id),
            WorkOrderStatus::from($row->status),
            WorkOrderReason::from($row->reason),
            new UserId($row->reported_by),
            $row->assigned_to !== null ? new UserId($row->assigned_to) : null,
            $row->resolution,
            $row->hold_reason,
        );
    }

    public function save(WorkOrder $workOrder): void
    {
        WorkOrderModel::updateOrCreate(
            ['id' => $workOrder->id()->value],
            [
                'machine_id' => $workOrder->machineId()->value,
                'status' => $workOrder->status()->value,
                'reason' => $workOrder->reason()->value,
                'reported_by' => $workOrder->reportedBy()->value,
                'assigned_to' => $workOrder->assignedTo()?->value,
                'resolution' => $workOrder->resolution(),
                'hold_reason' => $workOrder->holdReason(),
            ],
        );
    }
}
