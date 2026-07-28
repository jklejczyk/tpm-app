<?php

namespace App\Repositories;

use App\Mappers\MachineMapper;
use App\Models\MachineModel;
use Tpm\Machine\Machine;
use Tpm\Shared\MachineId;
use Tpm\Machine\MachineRepository;


final class EloquentMachineRepository implements MachineRepository
{
    public function __construct(
        private readonly MachineMapper $mapper,
    ) {}

    public function save(Machine $machine): void
    {
        MachineModel::updateOrCreate(
            ['id' => $machine->id()->value],
            ['name' => $machine->name()],
        );
    }

    public function findById(MachineId $id): ?Machine
    {
        $row = MachineModel::find($id->value);

        return $row === null ? null : $this->mapper->toEntity($row);
    }
}
