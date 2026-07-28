<?php

namespace App\Exceptions;

use DateTimeImmutable;
use RuntimeException;
use Tpm\Shared\MachineId;

final class ProductionRecordNotFound extends RuntimeException
{
    public static function forWindow(MachineId $machineId, DateTimeImmutable $from, DateTimeImmutable $to): self
    {
        return new self(sprintf(
            "No production record for machine '%s' between %s and %s.",
            $machineId->value,
            $from->format(DateTimeImmutable::ATOM),
            $to->format(DateTimeImmutable::ATOM),
        ));
    }
}
