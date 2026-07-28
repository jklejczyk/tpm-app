<?php

namespace App\Queries;

use App\Mappers\WorkOrderMapper;
use App\Models\WorkOrderModel;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Tpm\Shared\Duration;
use Tpm\Shared\MachineId;

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

        $total = Duration::zero();

        foreach ($rows as $row) {
            $total = $total->plus($this->mapper->toEntity($row)->downtimeWithin($from, $to));
        }

        return $total;
    }
}
