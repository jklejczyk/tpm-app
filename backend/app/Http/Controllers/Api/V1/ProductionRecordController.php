<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProductionRecord\StoreProductionRecordRequest;
use App\Http\Resources\Api\V1\ProductionRecordResource;
use App\Queries\ProductionRecordQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Tpm\Production\ProductionRecord;
use Tpm\Production\ProductionRecordRepository;
use Tpm\Shared\ProductionRecordId;

final class ProductionRecordController extends Controller
{
    public function __construct(
        private readonly ProductionRecordQuery $query,
        private readonly ProductionRecordRepository $repository,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return ProductionRecordResource::collection($this->query->available());
    }

    public function store(StoreProductionRecordRequest $request): JsonResponse
    {
        $record = ProductionRecord::record(
            new ProductionRecordId((string) Str::ulid()),
            $request->machineId(),
            $request->periodStart(),
            $request->periodEnd(),
            $request->producedUnits(),
            $request->defectiveUnits(),
            $request->idealCycleTime(),
        );

        $this->repository->save($record);

        return ProductionRecordResource::make($this->query->find($record->id()->value))
            ->response()
            ->setStatusCode(201);
    }
}
