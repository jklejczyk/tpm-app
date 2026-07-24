<?php

namespace Database\Factories;

use App\Models\WorkOrderModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Tpm\WorkOrder\WorkOrderReason;
use Tpm\WorkOrder\WorkOrderStatus;

/**
 * @extends Factory<WorkOrderModel>
 */
class WorkOrderModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(WorkOrderStatus::cases());

        $isAssigned = $status !== WorkOrderStatus::Reported;
        $isResolved = in_array($status, [WorkOrderStatus::Resolved, WorkOrderStatus::Closed], true);

        return [
            'id' => (string) Str::ulid(),
            'machine_id' => (string) Str::ulid(),
            'status' => $status->value,
            'reason' => fake()->randomElement(WorkOrderReason::cases())->value,
            'reported_by' => (string) Str::ulid(),
            'assigned_to' => $isAssigned ? (string) Str::ulid() : null,
            'resolution' => $isResolved ? fake()->sentence() : null,
            'hold_reason' => $status === WorkOrderStatus::OnHold ? fake()->sentence() : null,
        ];
    }
}