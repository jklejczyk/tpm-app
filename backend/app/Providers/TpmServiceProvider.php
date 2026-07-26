<?php

namespace App\Providers;

use App\Exceptions\WorkOrderNotFound;
use App\Repositories\EloquentWorkOrderRepository;
use App\Support\UserDirectory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
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
    ];

    public function register(): void
    {
        $this->app->scoped(UserDirectory::class);
    }

    public function boot(): void
    {
        Route::bind('workOrder', function (string $id): WorkOrder {
            $workOrderId = new WorkOrderId($id);

            return $this->app->make(WorkOrderRepository::class)->findById($workOrderId)
                ?? throw WorkOrderNotFound::withId($workOrderId);
        });
    }
}
