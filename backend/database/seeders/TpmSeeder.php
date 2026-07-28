<?php

namespace Database\Seeders;

use App\Models\MachineModel;
use App\Models\ProductionRecordModel;
use App\Models\User;
use App\Models\WorkOrderModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Tpm\Machine\Machine;
use Tpm\Machine\MachineRepository;
use Tpm\Shared\MachineId;
use Tpm\Shared\Role;
use Tpm\WorkOrder\WorkOrderReason;
use Tpm\WorkOrder\WorkOrderStatus;

class TpmSeeder extends Seeder
{
    private const MACHINE_COUNT = 30;

    private const WORK_ORDER_COUNT = 300;

    private const PRODUCTION_RECORD_COUNT = 40;

    private const TIMELINE_HORIZON_DAYS = 90;

    public function run(MachineRepository $repository): void
    {
        $this->seedMachines($repository);
        $this->seedWorkOrders();
        $this->seedProductionRecords();
    }

    private function seedMachines(MachineRepository $repository): void
    {
        for ($i = 0; $i < self::MACHINE_COUNT; $i++) {
            $code = fake()->unique()->bothify('?????#??');

            if ($repository->findById(new MachineId($code)) === null) {
                $repository->save(Machine::register(new MachineId($code), $code));
            }
        }
    }

    private function seedWorkOrders(): void
    {
        $users = User::all();
        $technicians = $users->where('role', Role::Technician)->values();
        $machineIds = MachineModel::pluck('id');

        $counts = $this->distributeWorkOrderCounts($machineIds->count());

        foreach ($machineIds as $index => $machineId) {
            $this->seedMachineTimeline($machineId, $counts[$index], $users, $technicians);
        }
    }

    /**
     * Spread WORK_ORDER_COUNT across the machines as evenly as possible, so the grand total is exact.
     *
     * @return list<int>
     */
    private function distributeWorkOrderCounts(int $machineCount): array
    {
        $base = intdiv(self::WORK_ORDER_COUNT, $machineCount);
        $remainder = self::WORK_ORDER_COUNT % $machineCount;

        return array_map(
            static fn (int $i): int => $base + ($i < $remainder ? 1 : 0),
            range(0, $machineCount - 1),
        );
    }

    /**
     * Walk a per-machine timeline from ~90 days ago toward now. Gaps (uptime) between consecutive
     * work orders are comfortably larger than any downtime span, so consecutive down-intervals never
     * overlap: sum(downtimeWithin) then equals the union, and downtime can never exceed a window.
     *
     * @param  Collection<int, User>  $users
     * @param  Collection<int, User>  $technicians
     */
    private function seedMachineTimeline(string $machineId, int $count, Collection $users, Collection $technicians): void
    {
        if ($count === 0) {
            return;
        }

        $cursor = CarbonImmutable::now()->subDays(self::TIMELINE_HORIZON_DAYS);

        // Rare, recent tail: only the single most recent work order per machine may stay open,
        // and only when it lands within the last ~2 days (otherwise it would saturate every
        // earlier downtime window through `resolvedAt ?? to`).
        $leavesOpenTail = fake()->boolean(30);

        for ($i = 0; $i < $count; $i++) {
            $isLast = $i === $count - 1;
            $reportedBy = $users->random();
            $reason = fake()->randomElement(WorkOrderReason::cases());

            if ($isLast && $leavesOpenTail) {
                $reportedAt = CarbonImmutable::now()->subHours(fake()->numberBetween(2, 48));
                // Guard: never let the recent tail land before the historical timeline it follows.
                $reportedAt = $reportedAt->max($cursor->addHour());

                $status = fake()->randomElement([
                    WorkOrderStatus::Reported,
                    WorkOrderStatus::Assigned,
                    WorkOrderStatus::InProgress,
                    WorkOrderStatus::OnHold,
                ]);

                WorkOrderModel::factory()
                    ->open($status, $reportedAt)
                    ->create([
                        'machine_id' => $machineId,
                        'reported_by' => $reportedBy->id,
                        'reason' => $reason->value,
                        'assigned_to' => $status === WorkOrderStatus::Reported ? null : $technicians->random()->id,
                    ]);

                $cursor = $reportedAt;

                continue;
            }

            $gapHours = fake()->numberBetween(12, 120);
            $reportedAt = $cursor->addHours($gapHours);

            $spanMinutes = fake()->numberBetween(15, 360);
            $resolvedAt = $reportedAt->addMinutes($spanMinutes);
            $status = fake()->randomElement([WorkOrderStatus::Resolved, WorkOrderStatus::Closed]);

            WorkOrderModel::factory()
                ->resolved($reportedAt, $resolvedAt, $status)
                ->create([
                    'machine_id' => $machineId,
                    'reported_by' => $reportedBy->id,
                    'reason' => $reason->value,
                    'assigned_to' => $technicians->random()->id,
                ]);

            $cursor = $resolvedAt;
        }
    }

    private function seedProductionRecords(): void
    {
        $machineIds = MachineModel::pluck('id');

        ProductionRecordModel::factory()
            ->count(self::PRODUCTION_RECORD_COUNT)
            ->state(function () {
                $day = now()->subDays(fake()->numberBetween(0, 30))->startOfDay();

                return [
                    'period_start' => $day->copy()->addHours(8),
                    'period_end' => $day->copy()->addHours(16),
                ];
            })
            ->create(['machine_id' => fn () => $machineIds->random()]);
    }
}
