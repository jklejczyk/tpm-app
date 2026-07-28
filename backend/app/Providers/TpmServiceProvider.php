<?php

namespace App\Providers;

use App\Exceptions\WorkOrderNotFound;
use App\Repositories\EloquentMachineRepository;
use App\Repositories\EloquentProductionRecordRepository;
use App\Repositories\EloquentWorkOrderRepository;
use App\Support\SystemClock;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Psr\Clock\ClockInterface;
use Tpm\Machine\MachineRepository;
use Tpm\Production\ProductionRecordRepository;
use Tpm\Shared\WorkOrderId;
use Tpm\WorkOrder\WorkOrder;
use Tpm\WorkOrder\WorkOrderRepository;

final class TpmServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        WorkOrderRepository::class => EloquentWorkOrderRepository::class,
        ProductionRecordRepository::class => EloquentProductionRecordRepository::class,
        MachineRepository::class => EloquentMachineRepository::class,
        ClockInterface::class => SystemClock::class,
    ];

    public function boot(): void
    {
        Route::bind('workOrder', function (string $id): WorkOrder {
            $workOrderId = new WorkOrderId($id);

            return $this->app->make(WorkOrderRepository::class)->findById($workOrderId)
                ?? throw WorkOrderNotFound::withId($workOrderId);
        });
    }
}
