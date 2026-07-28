<?php

namespace Database\Factories;

use App\Models\MachineModel;
use App\Models\User;
use App\Models\WorkOrderModel;
use Carbon\CarbonInterface;
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
            'machine_id' => fn () => MachineModel::factory()->create()->id,
            'status' => $status->value,
            'reason' => fake()->randomElement(WorkOrderReason::cases())->value,
            'reported_by' => User::factory(),
            'reported_at' => now()->subDay(),
            'assigned_to' => $isAssigned ? User::factory() : null,
            'resolution' => $isResolved ? fake()->sentence() : null,
            'hold_reason' => $status === WorkOrderStatus::OnHold ? fake()->sentence() : null,
            'resolved_at' => $isResolved ? now() : null,
        ];
    }

    /**
     * A historical work order resolved (or closed) within the given window.
     */
    public function resolved(CarbonInterface $reportedAt, CarbonInterface $resolvedAt, WorkOrderStatus $status = WorkOrderStatus::Resolved): static
    {
        return $this->state(fn (): array => [
            'status' => $status->value,
            'reported_at' => $reportedAt,
            'resolved_at' => $resolvedAt,
            'resolution' => fake()->sentence(),
        ]);
    }

    /**
     * A still-open work order (no resolution yet), reported at the given time.
     */
    public function open(WorkOrderStatus $status, CarbonInterface $reportedAt): static
    {
        return $this->state(fn (): array => [
            'status' => $status->value,
            'reported_at' => $reportedAt,
            'assigned_to' => $status === WorkOrderStatus::Reported ? null : User::factory(),
            'hold_reason' => $status === WorkOrderStatus::OnHold ? fake()->sentence() : null,
            'resolution' => null,
            'resolved_at' => null,
        ]);
    }
}
