<?php

namespace App\Factories;

use App\Data\WorkOrderData;
use App\Models\WorkOrderModel;
use App\Repositories\WorkOrderMapper;
use App\Support\UserDirectory;
use Tpm\WorkOrder\WorkOrder;

final class WorkOrderDataFactory
{
    public function __construct(
        private readonly UserDirectory $users,
        private readonly WorkOrderMapper $mapper,
    ) {}

    public function fromEntity(WorkOrder $workOrder): WorkOrderData
    {
        $this->users->preload(array_values(array_filter([
            $workOrder->reportedBy()->value,
            $workOrder->assignedTo()?->value,
        ])));

        return new WorkOrderData(
            $workOrder,
            $this->users->name($workOrder->reportedBy()->value),
            $workOrder->assignedTo() !== null ? $this->users->name($workOrder->assignedTo()->value) : null,
        );
    }

    /**
     * @param  WorkOrderModel[]  $models
     * @return WorkOrderData[]
     */
    public function fromModels(array $models): array
    {
        $this->users->preload(
            collect($models)
                ->flatMap(fn (WorkOrderModel $m) => [$m->reported_by, $m->assigned_to])
                ->filter()
                ->unique()
                ->values()
                ->all(),
        );

        return array_map(
            fn (WorkOrderModel $m): WorkOrderData => new WorkOrderData(
                $this->mapper->toEntity($m),
                $this->users->name($m->reported_by),
                $m->assigned_to !== null ? $this->users->name($m->assigned_to) : null,
            ),
            $models,
        );
    }
}
