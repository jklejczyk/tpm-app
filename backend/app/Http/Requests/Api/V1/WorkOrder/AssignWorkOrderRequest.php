<?php

namespace App\Http\Requests\Api\V1\WorkOrder;

use Illuminate\Foundation\Http\FormRequest;
use Tpm\Shared\UserId;

final class AssignWorkOrderRequest extends FormRequest
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
            'technician_id' => ['required', 'string', 'exists:users,id'],
        ];
    }

    public function technicianId(): UserId
    {
        return new UserId((string) $this->string('technician_id'));
    }
}
