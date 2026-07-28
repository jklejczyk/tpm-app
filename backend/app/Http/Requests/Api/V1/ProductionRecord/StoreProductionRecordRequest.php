<?php

namespace App\Http\Requests\Api\V1\ProductionRecord;

use Illuminate\Foundation\Http\FormRequest;

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
}
