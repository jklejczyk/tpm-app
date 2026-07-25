<?php

namespace App\Data;

use Tpm\WorkOrder\WorkOrder;

final readonly class WorkOrderData
{
    public function __construct(
        public WorkOrder $workOrder,
        public ?string $reportedByName,
        public ?string $assignedToName,
    ) {}
}
