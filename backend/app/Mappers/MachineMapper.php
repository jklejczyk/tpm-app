<?php

namespace App\Mappers;

use App\Models\MachineModel;
use Tpm\Machine\Machine;
use Tpm\Shared\MachineId;

final class MachineMapper
{
    public function toEntity(MachineModel $row): Machine
    {
        return Machine::reconstitute(
            new MachineId($row->id),
            $row->name,
        );
    }
}
