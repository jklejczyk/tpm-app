<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\WorkOrderNotFound;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WorkOrderResource;
use App\Factories\ActorFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tpm\Shared\MachineId;
use Tpm\Shared\UserId;
use Tpm\Shared\WorkOrderId;
use Tpm\WorkOrder\WorkOrder;
use Tpm\WorkOrder\WorkOrderReason;
use Tpm\WorkOrder\WorkOrderRepository;

final class WorkOrderController extends Controller
{
    public function __construct(
        private readonly WorkOrderRepository $repository,
        private readonly ActorFactory $actors,
    ) {
    }

    public function report(Request $request): JsonResponse
    {
        $workOrder = WorkOrder::report(
            new WorkOrderId((string) Str::ulid()),
            new MachineId((string) $request->string('machine_id')),
            WorkOrderReason::from((string) $request->string('reason')),
            new UserId((string) $request->user()->id),
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($workOrder)->response()->setStatusCode(201);
    }

    public function show(string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        return WorkOrderResource::make($workOrder);
    }

    public function assign(Request $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $workOrder->assign(
            $this->actors->fromUser($request->user()),
            new UserId((string) $request->string('technician_id')),
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($workOrder);
    }

    public function start(Request $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $workOrder->start($this->actors->fromUser($request->user()));

        $this->repository->save($workOrder);

        return WorkOrderResource::make($workOrder);
    }

    public function hold(Request $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $workOrder->hold(
            $this->actors->fromUser($request->user()),
            (string) $request->string('reason'),
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($workOrder);
    }

    public function resume(Request $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $workOrder->resume($this->actors->fromUser($request->user()));

        $this->repository->save($workOrder);

        return WorkOrderResource::make($workOrder);
    }

    public function resolve(Request $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $workOrder->resolve(
            $this->actors->fromUser($request->user()),
            (string) $request->string('resolution'),
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($workOrder);
    }

    public function close(Request $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $workOrder->close($this->actors->fromUser($request->user()));

        $this->repository->save($workOrder);

        return WorkOrderResource::make($workOrder);
    }
}
