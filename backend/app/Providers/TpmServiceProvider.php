<?php

namespace App\Providers;

use App\Repositories\EloquentWorkOrderRepository;
use App\Support\UserDirectory;
use Illuminate\Support\ServiceProvider;
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
}
