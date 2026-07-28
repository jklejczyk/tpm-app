<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Oee\ShowOeeRequest;
use App\Http\Resources\Api\V1\OeeResource;
use App\Queries\MachineDowntimeQuery;
use App\Queries\ProductionRecordQuery;
use DateTimeInterface;
use Tpm\Oee\OeeCalculator;
use Tpm\Shared\MachineId;

final class OeeController extends Controller
{
    public function __construct(
        private readonly ProductionRecordQuery $records,
        private readonly MachineDowntimeQuery $downtimes,
        private readonly OeeCalculator $calculator,
    ) {}

    public function show(ShowOeeRequest $request, string $id): OeeResource
    {
        $machineId = new MachineId($id);

        $record = $this->records->forWindow($machineId, $request->from(), $request->to());
        $downtime = $this->downtimes->within($machineId, $record->periodStart(), $record->periodEnd());
        $oee = $this->calculator->calculate($record, $downtime);

        return OeeResource::make([
            'machineId' => $record->machineId()->value,
            'periodStart' => $record->periodStart()->format(DateTimeInterface::ATOM),
            'periodEnd' => $record->periodEnd()->format(DateTimeInterface::ATOM),
            'plannedSeconds' => $record->plannedTime()->seconds,
            'downtimeSeconds' => $downtime->seconds,
            'producedUnits' => $record->producedUnits(),
            'defectiveUnits' => $record->defectiveUnits(),
            'availability' => $oee->availability->ratio,
            'performance' => $oee->performance->ratio,
            'quality' => $oee->quality->ratio,
            'oee' => $oee->value(),
        ]);
    }
}
