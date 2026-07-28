<?php

namespace App\Http\Requests\Api\V1\WorkOrder;

use Illuminate\Foundation\Http\FormRequest;

final class ResolveWorkOrderRequest extends FormRequest
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
            'resolution' => ['required', 'string'],
        ];
    }

    public function resolution(): string
    {
        return (string) $this->string('resolution');
    }
}
