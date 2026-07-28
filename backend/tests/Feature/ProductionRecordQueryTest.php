<?php

use App\Exceptions\ProductionRecordNotFound;
use App\Models\MachineModel;
use App\Models\ProductionRecordModel;
use App\Queries\ProductionRecordQuery;
use Tpm\Production\ProductionRecord;
use Tpm\Shared\MachineId;

beforeEach(function () {
    MachineModel::factory()->create(['id' => 'press-01', 'name' => 'Hydraulic Press #1']);
    MachineModel::factory()->create(['id' => 'press-99', 'name' => 'Hydraulic Press #99']);
});

it('finds the production record for a machine and exact window', function () {
    ProductionRecordModel::factory()->create([
        'machine_id' => 'press-01',
        'period_start' => '2026-01-01 08:00:00',
        'period_end' => '2026-01-01 16:00:00',
        'produced_units' => 800,
        'defective_units' => 20,
        'ideal_cycle_time' => 30,
    ]);
    ProductionRecordModel::factory()->create([
        'machine_id' => 'press-01',
        'period_start' => '2026-01-02 08:00:00',
        'period_end' => '2026-01-02 16:00:00',
        'produced_units' => 999,
        'defective_units' => 5,
        'ideal_cycle_time' => 30,
    ]);

    $record = app(ProductionRecordQuery::class)->forWindow(
        new MachineId('press-01'),
        new DateTimeImmutable('2026-01-01 08:00:00'),
        new DateTimeImmutable('2026-01-01 16:00:00'),
    );

    expect($record)->toBeInstanceOf(ProductionRecord::class)
        ->and($record->producedUnits())->toBe(800);
});

it('throws when no record matches the window', function () {
    app(ProductionRecordQuery::class)->forWindow(
        new MachineId('press-01'),
        new DateTimeImmutable('2026-01-01 08:00:00'),
        new DateTimeImmutable('2026-01-01 16:00:00'),
    );
})->throws(ProductionRecordNotFound::class);

it('throws when the same machine has a record but for a different window', function () {
    ProductionRecordModel::factory()->create([
        'machine_id' => 'press-01',
        'period_start' => '2026-01-02 08:00:00',
        'period_end' => '2026-01-02 16:00:00',
        'produced_units' => 800,
        'defective_units' => 20,
        'ideal_cycle_time' => 30,
    ]);

    app(ProductionRecordQuery::class)->forWindow(
        new MachineId('press-01'),
        new DateTimeImmutable('2026-01-01 08:00:00'),
        new DateTimeImmutable('2026-01-01 16:00:00'),
    );
})->throws(ProductionRecordNotFound::class);

it('throws when a different machine has a record for the exact window', function () {
    ProductionRecordModel::factory()->create([
        'machine_id' => 'press-99',
        'period_start' => '2026-01-01 08:00:00',
        'period_end' => '2026-01-01 16:00:00',
        'produced_units' => 800,
        'defective_units' => 20,
        'ideal_cycle_time' => 30,
    ]);

    app(ProductionRecordQuery::class)->forWindow(
        new MachineId('press-01'),
        new DateTimeImmutable('2026-01-01 08:00:00'),
        new DateTimeImmutable('2026-01-01 16:00:00'),
    );
})->throws(ProductionRecordNotFound::class);

it('throws when the same machine and period_start has a record but a different period_end', function () {
    // same machine + period_start, wrong period_end — proves period_end is an independent, load-bearing filter
    ProductionRecordModel::factory()->create([
        'machine_id' => 'press-01',
        'period_start' => '2026-01-01 08:00:00',
        'period_end' => '2026-01-01 14:00:00',
        'produced_units' => 800,
        'defective_units' => 20,
        'ideal_cycle_time' => 30,
    ]);

    app(ProductionRecordQuery::class)->forWindow(
        new MachineId('press-01'),
        new DateTimeImmutable('2026-01-01 08:00:00'),
        new DateTimeImmutable('2026-01-01 16:00:00'),
    );
})->throws(ProductionRecordNotFound::class);

it('throws when the same machine and period_end has a record but a different period_start', function () {
    ProductionRecordModel::factory()->create([
        'machine_id' => 'press-01',
        'period_start' => '2026-01-01 06:00:00',
        'period_end' => '2026-01-01 16:00:00',
        'produced_units' => 800,
        'defective_units' => 20,
        'ideal_cycle_time' => 30,
    ]);

    app(ProductionRecordQuery::class)->forWindow(
        new MachineId('press-01'),
        new DateTimeImmutable('2026-01-01 08:00:00'),
        new DateTimeImmutable('2026-01-01 16:00:00'),
    );
})->throws(ProductionRecordNotFound::class);
