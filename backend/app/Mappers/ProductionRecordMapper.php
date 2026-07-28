<?php

namespace App\Mappers;

use App\Models\ProductionRecordModel;
use Tpm\Production\ProductionRecord;
use Tpm\Shared\MachineId;
use Tpm\Shared\ProductionRecordId;

final class ProductionRecordMapper
{
    public function toEntity(ProductionRecordModel $row): ProductionRecord
    {
        return ProductionRecord::reconstitute(
            new ProductionRecordId($row->id),
            new MachineId($row->machine_id),
            $row->period_start->toDateTimeImmutable(),
            $row->period_end->toDateTimeImmutable(),
            $row->produced_units,
            $row->defective_units,
            $row->ideal_cycle_time,
        );
    }
}
