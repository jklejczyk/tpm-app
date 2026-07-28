<?php

namespace App\Http\Requests\Api\V1\Oee;

use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;

final class ShowOeeRequest extends FormRequest
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
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
        ];
    }

    public function from(): DateTimeImmutable
    {
        return new DateTimeImmutable((string) $this->query('from'));
    }

    public function to(): DateTimeImmutable
    {
        return new DateTimeImmutable((string) $this->query('to'));
    }
}
