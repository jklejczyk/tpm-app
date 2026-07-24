<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Tpm\Shared\WorkOrderId;

final class WorkOrderNotFound extends RuntimeException
{
    public static function withId(WorkOrderId $id): self
    {
        return new self("Work order '{$id->value}' was not found.");
    }
}
