<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array{
 *     machineId: string,
 *     periodStart: string,
 *     periodEnd: string,
 *     plannedSeconds: int,
 *     downtimeSeconds: int,
 *     producedUnits: int,
 *     defectiveUnits: int,
 *     availability: float,
 *     performance: float,
 *     quality: float,
 *     oee: float
 * } $resource
 */
class OeeResource extends JsonResource
{
    /**
     * @return array<string, string|int|float>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }

    /**
     * Preserve the trailing ".0" on whole-number ratios (e.g. availability 1.0) —
     * PHP's default json_encode renders 1.0 as `1`, indistinguishable from an int.
     */
    public function jsonOptions(): int
    {
        return JSON_PRESERVE_ZERO_FRACTION;
    }
}
