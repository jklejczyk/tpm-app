<?php

namespace App\Repositories;

use App\Mappers\WorkOrderMapper;
use App\Models\WorkOrderModel;
use Tpm\Shared\WorkOrderId;
use Tpm\WorkOrder\WorkOrder;
use Tpm\WorkOrder\WorkOrderRepository;

final class EloquentWorkOrderRepository implements WorkOrderRepository
{
    public function __construct(
        private readonly WorkOrderMapper $mapper,
    ) {}

    public function findById(WorkOrderId $id): ?WorkOrder
    {
        $row = WorkOrderModel::find($id->value);

        return $row === null ? null : $this->mapper->toEntity($row);
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
                'reported_at' => $workOrder->reportedAt(),
                'resolved_at' => $workOrder->resolvedAt(),
            ],
        );
    }
}
