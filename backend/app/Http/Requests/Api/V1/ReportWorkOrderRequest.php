<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Tpm\WorkOrder\WorkOrderReason;

final class ReportWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'machine_id' => ['required', 'string'],
            'reason' => ['required', Rule::enum(WorkOrderReason::class)],
        ];
    }
}