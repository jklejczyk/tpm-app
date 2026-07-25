<?php

namespace App\Repositories;

use App\Models\WorkOrderModel;
use Tpm\Shared\MachineId;
use Tpm\Shared\UserId;
use Tpm\Shared\WorkOrderId;
use Tpm\WorkOrder\WorkOrder;
use Tpm\WorkOrder\WorkOrderReason;
use Tpm\WorkOrder\WorkOrderStatus;


final class WorkOrderMapper
{
    public function toEntity(WorkOrderModel $row): WorkOrder
    {
        return WorkOrder::reconstitute(
            new WorkOrderId($row->id),
            new MachineId($row->machine_id),
            WorkOrderStatus::from($row->status),
            WorkOrderReason::from($row->reason),
            new UserId($row->reported_by),
            ($row->reported_at ?? now())->toDateTimeImmutable(),
            $row->assigned_to !== null ? new UserId($row->assigned_to) : null,
            $row->resolution,
            $row->hold_reason,
        );
    }
}
