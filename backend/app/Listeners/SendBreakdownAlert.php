<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\BreakdownReported;
use Illuminate\Support\Facades\Notification;
use Tpm\Shared\Role;
use Tpm\WorkOrder\Event\WorkOrderReported;

class SendBreakdownAlert
{
    public function handle(WorkOrderReported $event): void
    {
        $managers = User::query()->where('role', Role::Manager->value)->get();

        Notification::send($managers, new BreakdownReported($event));
    }
}
