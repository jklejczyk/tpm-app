<?php

namespace App\Http\Requests\Api\V1\ProductionRecord;

use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Tpm\Shared\MachineId;

final class StoreProductionRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'machine_id' => ['required', 'string', 'exists:machines,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after:period_start'],
            'produced_units' => ['required', 'integer', 'min:0'],
            'defective_units' => ['required', 'integer', 'min:0', 'lte:produced_units'],
            'ideal_cycle_time' => ['required', 'integer', 'min:1'],
        ];
    }

    public function machineId(): MachineId
    {
        return new MachineId((string) $this->string('machine_id'));
    }

    public function periodStart(): DateTimeImmutable
    {
        return new DateTimeImmutable((string) $this->string('period_start'));
    }

    public function periodEnd(): DateTimeImmutable
    {
        return new DateTimeImmutable((string) $this->string('period_end'));
    }

    public function producedUnits(): int
    {
        return $this->integer('produced_units');
    }

    public function defectiveUnits(): int
    {
        return $this->integer('defective_units');
    }

    public function idealCycleTime(): int
    {
        return $this->integer('ideal_cycle_time');
    }
}
