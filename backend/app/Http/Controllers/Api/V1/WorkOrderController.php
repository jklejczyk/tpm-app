<?php

namespace App\Http\Controllers\Api\V1;

use App\Factories\ActorFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WorkOrder\AssignWorkOrderRequest;
use App\Http\Requests\Api\V1\WorkOrder\HoldWorkOrderRequest;
use App\Http\Requests\Api\V1\WorkOrder\ListWorkOrdersRequest;
use App\Http\Requests\Api\V1\WorkOrder\ReportWorkOrderRequest;
use App\Http\Requests\Api\V1\WorkOrder\ResolveWorkOrderRequest;
use App\Http\Resources\Api\V1\WorkOrderResource;
use App\Models\User;
use App\Queries\WorkOrderQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Psr\Clock\ClockInterface;
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
        private readonly WorkOrderQuery $query,
        private readonly ClockInterface $clock,
    ) {}

    public function index(ListWorkOrdersRequest $request): AnonymousResourceCollection
    {
        return WorkOrderResource::collection(
            $this->query->paginate(
                $request->perPage(),
                $request->sort(),
                $request->direction(),
            ),
        );
    }

    public function report(ReportWorkOrderRequest $request): JsonResponse
    {
        $workOrder = WorkOrder::report(
            new WorkOrderId((string) Str::ulid()),
            new MachineId((string) $request->string('machine_id')),
            WorkOrderReason::from((string) $request->string('reason')),
            new UserId((string) $this->currentUser($request)->id),
            $this->clock->now(),
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->query->find($workOrder->id()->value))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): WorkOrderResource
    {
        return WorkOrderResource::make($this->query->find($id));
    }

    public function assign(AssignWorkOrderRequest $request, WorkOrder $workOrder): WorkOrderResource
    {
        $technician = User::query()->findOrFail((string) $request->string('technician_id'));

        $workOrder->assign(
            $this->actors->fromUser($this->currentUser($request)),
            new UserId((string) $technician->id),
            $technician->role,
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->query->find($workOrder->id()->value));
    }

    public function start(Request $request, WorkOrder $workOrder): WorkOrderResource
    {
        $workOrder->start($this->actors->fromUser($this->currentUser($request)));

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->query->find($workOrder->id()->value));
    }

    public function hold(HoldWorkOrderRequest $request, WorkOrder $workOrder): WorkOrderResource
    {
        $workOrder->hold(
            $this->actors->fromUser($this->currentUser($request)),
            (string) $request->string('reason'),
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->query->find($workOrder->id()->value));
    }

    public function resume(Request $request, WorkOrder $workOrder): WorkOrderResource
    {
        $workOrder->resume($this->actors->fromUser($this->currentUser($request)));

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->query->find($workOrder->id()->value));
    }

    public function resolve(ResolveWorkOrderRequest $request, WorkOrder $workOrder): WorkOrderResource
    {
        $workOrder->resolve(
            $this->actors->fromUser($this->currentUser($request)),
            (string) $request->string('resolution'),
            $this->clock->now(),
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->query->find($workOrder->id()->value));
    }

    public function close(Request $request, WorkOrder $workOrder): WorkOrderResource
    {
        $workOrder->close($this->actors->fromUser($this->currentUser($request)));

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->query->find($workOrder->id()->value));
    }
}
