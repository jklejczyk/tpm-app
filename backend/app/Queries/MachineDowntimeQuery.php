<?php

namespace App\Queries;

use App\Mappers\WorkOrderMapper;
use App\Models\WorkOrderModel;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Tpm\Shared\Duration;
use Tpm\Shared\MachineId;
use Tpm\WorkOrder\WorkOrder;

final class MachineDowntimeQuery
{
    public function __construct(
        private readonly WorkOrderMapper $mapper,
    ) {}

    public function within(MachineId $machineId, DateTimeImmutable $from, DateTimeImmutable $to): Duration
    {
        $rows = WorkOrderModel::query()
            ->where('machine_id', $machineId->value)
            ->where('reported_at', '<', $to)
            ->where(function (Builder $query) use ($from): void {
                $query->whereNull('resolved_at')->orWhere('resolved_at', '>', $from);
            })
            ->get();

        $workOrders = array_values($rows->map(fn (WorkOrderModel $row): WorkOrder => $this->mapper->toEntity($row))->all());

        return WorkOrder::totalDowntimeWithin($workOrders, $from, $to);
    }
}
