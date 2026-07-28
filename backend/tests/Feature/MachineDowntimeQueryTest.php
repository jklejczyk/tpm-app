<?php

use App\Models\MachineModel;
use App\Models\User;
use App\Models\WorkOrderModel;
use App\Queries\MachineDowntimeQuery;
use Tpm\Shared\MachineId;
use Tpm\WorkOrder\WorkOrderStatus;

beforeEach(function () {
    MachineModel::factory()->create(['id' => 'press-01', 'name' => 'Hydraulic Press #1']);
    MachineModel::factory()->create(['id' => 'press-99', 'name' => 'Hydraulic Press #99']);
});

it('sums clamped downtime for a machine within a window', function () {
    $reporter = User::factory()->create();
    $from = new DateTimeImmutable('2026-01-01 08:00:00');
    $to = new DateTimeImmutable('2026-01-01 16:00:00');

    WorkOrderModel::factory()->create([
        'machine_id' => 'press-01',
        'reported_by' => $reporter->id,
        'status' => WorkOrderStatus::Resolved->value,
        'reported_at' => '2026-01-01 10:00:00',
        'resolved_at' => '2026-01-01 10:30:00',
    ]);

    WorkOrderModel::factory()->create([
        'machine_id' => 'press-99',
        'reported_by' => $reporter->id,
        'status' => WorkOrderStatus::Resolved->value,
        'reported_at' => '2026-01-01 11:00:00',
        'resolved_at' => '2026-01-01 12:00:00',
    ]);

    $downtime = app(MachineDowntimeQuery::class)->within(new MachineId('press-01'), $from, $to);

    expect($downtime->seconds)->toBe(1800);
});

it('sums downtime across multiple resolved work orders on the same machine', function () {
    $reporter = User::factory()->create();
    $from = new DateTimeImmutable('2026-01-01 08:00:00');
    $to = new DateTimeImmutable('2026-01-01 16:00:00');

    WorkOrderModel::factory()->create([
        'machine_id' => 'press-01',
        'reported_by' => $reporter->id,
        'status' => WorkOrderStatus::Resolved->value,
        'reported_at' => '2026-01-01 10:00:00',
        'resolved_at' => '2026-01-01 10:30:00',
    ]);
    WorkOrderModel::factory()->create([
        'machine_id' => 'press-01',
        'reported_by' => $reporter->id,
        'status' => WorkOrderStatus::Resolved->value,
        'reported_at' => '2026-01-01 12:00:00',
        'resolved_at' => '2026-01-01 12:45:00',
    ]);

    $downtime = app(MachineDowntimeQuery::class)->within(new MachineId('press-01'), $from, $to);

    expect($downtime->seconds)->toBe(1800 + 2700);
});

it('clamps downtime at the window start when the work order was reported before it', function () {
    $reporter = User::factory()->create();
    $from = new DateTimeImmutable('2026-01-01 08:00:00');
    $to = new DateTimeImmutable('2026-01-01 16:00:00');

    WorkOrderModel::factory()->create([
        'machine_id' => 'press-01',
        'reported_by' => $reporter->id,
        'status' => WorkOrderStatus::Resolved->value,
        'reported_at' => '2026-01-01 07:30:00',
        'resolved_at' => '2026-01-01 08:30:00',
    ]);

    $downtime = app(MachineDowntimeQuery::class)->within(new MachineId('press-01'), $from, $to);

    expect($downtime->seconds)->toBe(1800);
});

it('unions two overlapping work orders on the same machine instead of double-counting', function () {
    $reporter = User::factory()->create();
    $from = new DateTimeImmutable('2026-01-01 08:00:00');
    $to = new DateTimeImmutable('2026-01-01 16:00:00');

    WorkOrderModel::factory()->create([
        'machine_id' => 'press-01',
        'reported_by' => $reporter->id,
        'status' => WorkOrderStatus::Resolved->value,
        'reported_at' => '2026-01-01 10:00:00',
        'resolved_at' => '2026-01-01 12:00:00',
    ]);
    WorkOrderModel::factory()->create([
        'machine_id' => 'press-01',
        'reported_by' => $reporter->id,
        'status' => WorkOrderStatus::Resolved->value,
        'reported_at' => '2026-01-01 11:00:00',
        'resolved_at' => '2026-01-01 13:00:00',
    ]);

    $downtime = app(MachineDowntimeQuery::class)->within(new MachineId('press-01'), $from, $to);

    expect($downtime->seconds)->toBe(3 * 3600)
        ->and($downtime->seconds)->toBeLessThanOrEqual($to->getTimestamp() - $from->getTimestamp());
});

it('clamps downtime at the window end for a still-open work order', function () {
    $reporter = User::factory()->create();
    $from = new DateTimeImmutable('2026-01-01 08:00:00');
    $to = new DateTimeImmutable('2026-01-01 16:00:00');

    WorkOrderModel::factory()->create([
        'machine_id' => 'press-01',
        'reported_by' => $reporter->id,
        'status' => WorkOrderStatus::InProgress->value,
        'reported_at' => '2026-01-01 15:30:00',
        'resolved_at' => null,
    ]);

    $downtime = app(MachineDowntimeQuery::class)->within(new MachineId('press-01'), $from, $to);

    expect($downtime->seconds)->toBe(1800);
});
