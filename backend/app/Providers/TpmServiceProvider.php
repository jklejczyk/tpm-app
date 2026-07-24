<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\EloquentWorkOrderRepository;
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
}
