<?php

namespace App\Repositories;

use App\Mappers\ProductionRecordMapper;
use App\Models\ProductionRecordModel;
use Tpm\Production\ProductionRecord;
use Tpm\Production\ProductionRecordRepository;
use Tpm\Shared\ProductionRecordId;

final class EloquentProductionRecordRepository implements ProductionRecordRepository
{
    public function __construct(
        private readonly ProductionRecordMapper $mapper,
    ) {}

    public function findById(ProductionRecordId $id): ?ProductionRecord
    {
        $row = ProductionRecordModel::find($id->value);

        return $row === null ? null : $this->mapper->toEntity($row);
    }

    public function save(ProductionRecord $record): void
    {
        ProductionRecordModel::updateOrCreate(
            ['id' => $record->id()->value],
            [
                'machine_id' => $record->machineId()->value,
                'period_start' => $record->periodStart(),
                'period_end' => $record->periodEnd(),
                'produced_units' => $record->producedUnits(),
                'defective_units' => $record->defectiveUnits(),
                'ideal_cycle_time' => $record->idealCycleTime(),
            ],
        );
    }
}
