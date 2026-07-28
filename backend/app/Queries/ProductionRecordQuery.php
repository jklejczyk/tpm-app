<?php

namespace App\Queries;

use App\Exceptions\ProductionRecordNotFound;
use App\Mappers\ProductionRecordMapper;
use App\Models\ProductionRecordModel;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Collection;
use Tpm\Production\ProductionRecord;
use Tpm\Shared\MachineId;

final class ProductionRecordQuery
{
    /**
     * @var list<string>
     */
    private const RELATIONS = ['machine'];

    public function __construct(
        private readonly ProductionRecordMapper $mapper,
    ) {}

    public function forWindow(MachineId $machineId, DateTimeImmutable $from, DateTimeImmutable $to): ProductionRecord
    {
        $row = ProductionRecordModel::query()
            ->where('machine_id', $machineId->value)
            ->where('period_start', $from)
            ->where('period_end', $to)
            ->first();

        if ($row === null) {
            throw ProductionRecordNotFound::forWindow($machineId, $from, $to);
        }

        return $this->mapper->toEntity($row);
    }

    /**
     * @return Collection<int, ProductionRecordModel>
     */
    public function available(): Collection
    {
        return ProductionRecordModel::query()
            ->with(self::RELATIONS)
            ->orderBy('machine_id')
            ->orderByDesc('period_start')
            ->get();
    }

    public function find(string $id): ProductionRecordModel
    {
        return ProductionRecordModel::query()->with(self::RELATIONS)->findOrFail($id);
    }
}
