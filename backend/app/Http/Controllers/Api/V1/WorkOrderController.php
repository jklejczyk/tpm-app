<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\WorkOrderNotFound;
use App\Factories\ActorFactory;
use App\Factories\WorkOrderDataFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AssignWorkOrderRequest;
use App\Http\Requests\Api\V1\HoldWorkOrderRequest;
use App\Http\Requests\Api\V1\ListWorkOrdersRequest;
use App\Http\Requests\Api\V1\ReportWorkOrderRequest;
use App\Http\Requests\Api\V1\ResolveWorkOrderRequest;
use App\Http\Resources\Api\V1\WorkOrderResource;
use App\Models\User;
use App\Queries\WorkOrderQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
        private readonly WorkOrderDataFactory $data,
        private readonly WorkOrderQuery $query,
    ) {}

    public function index(ListWorkOrdersRequest $request): AnonymousResourceCollection
    {
        $page = $this->query->paginate(
            $request->perPage(),
            $request->sort(),
            $request->direction(),
        );

        $page->setCollection(
            collect($this->data->fromModels($page->items())),
        );

        return WorkOrderResource::collection($page);
    }

    public function report(ReportWorkOrderRequest $request): JsonResponse
    {
        $workOrder = WorkOrder::report(
            new WorkOrderId((string) Str::ulid()),
            new MachineId((string) $request->string('machine_id')),
            WorkOrderReason::from((string) $request->string('reason')),
            new UserId((string) $request->user()->id),
            now()->toDateTimeImmutable(),
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->data->fromEntity($workOrder))->response()->setStatusCode(201);
    }

    public function show(string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        return WorkOrderResource::make($this->data->fromEntity($workOrder));
    }

    public function assign(AssignWorkOrderRequest $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $technician = User::query()->findOrFail((string) $request->string('technician_id'));

        $workOrder->assign(
            $this->actors->fromUser($request->user()),
            new UserId((string) $technician->id),
            $technician->role,
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->data->fromEntity($workOrder));
    }

    public function start(Request $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $workOrder->start($this->actors->fromUser($request->user()));

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->data->fromEntity($workOrder));
    }

    public function hold(HoldWorkOrderRequest $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $workOrder->hold(
            $this->actors->fromUser($request->user()),
            (string) $request->string('reason'),
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->data->fromEntity($workOrder));
    }

    public function resume(Request $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $workOrder->resume($this->actors->fromUser($request->user()));

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->data->fromEntity($workOrder));
    }

    public function resolve(ResolveWorkOrderRequest $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $workOrder->resolve(
            $this->actors->fromUser($request->user()),
            (string) $request->string('resolution'),
        );

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->data->fromEntity($workOrder));
    }

    public function close(Request $request, string $id): WorkOrderResource
    {
        $workOrderId = new WorkOrderId($id);
        $workOrder = $this->repository->findById($workOrderId) ?? throw WorkOrderNotFound::withId($workOrderId);

        $workOrder->close($this->actors->fromUser($request->user()));

        $this->repository->save($workOrder);

        return WorkOrderResource::make($this->data->fromEntity($workOrder));
    }
}
