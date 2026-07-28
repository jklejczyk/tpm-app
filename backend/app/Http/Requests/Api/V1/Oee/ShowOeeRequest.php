<?php

namespace App\Http\Requests\Api\V1\Oee;

use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class ShowOeeRequest extends FormRequest
{
    private const MAX_WINDOW_DAYS = 31;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->hasAny(['from', 'to'])) {
                return;
            }

            $from = Carbon::parse((string) $this->query('from'));
            $to = Carbon::parse((string) $this->query('to'));

            if ($from->diffInDays($to) > self::MAX_WINDOW_DAYS) {
                $validator->errors()->add(
                    'to',
                    'The window between from and to must not exceed '.self::MAX_WINDOW_DAYS.' days.',
                );
            }
        });
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
