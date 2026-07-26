<?php

namespace App\Http\Requests\Api\V1\WorkOrder;

use App\Queries\WorkOrderQuery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListWorkOrdersRequest extends FormRequest
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
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:100'],
            'sort' => ['string', Rule::in(WorkOrderQuery::SORTABLE)],
            'direction' => ['string', Rule::in(['asc', 'desc'])],
        ];
    }

    public function perPage(): int
    {
        return (int) $this->query('per_page', 10);
    }

    public function sort(): string
    {
        return (string) $this->query('sort', 'reported_at');
    }

    public function direction(): string
    {
        return (string) $this->query('direction', 'desc');
    }
}
