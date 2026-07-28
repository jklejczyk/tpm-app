<?php

use App\Models\MachineModel;
use App\Models\ProductionRecordModel;
use App\Models\User;

beforeEach(function () {
    MachineModel::factory()->create(['id' => 'press-01', 'name' => 'Hydraulic Press #1']);
});

it('lists available production records for an authenticated user', function () {
    $user = User::factory()->create();
    ProductionRecordModel::factory()->create([
        'machine_id' => 'press-01',
        'period_start' => '2026-01-01 08:00:00',
        'period_end' => '2026-01-01 16:00:00',
        'produced_units' => 800,
        'defective_units' => 20,
        'ideal_cycle_time' => 30,
    ]);

    $this->actingAs($user)
        ->getJson('/api/v1/production-records')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.machineId', 'press-01')
        ->assertJsonPath('data.0.periodStart', '2026-01-01T08:00:00+00:00')
        ->assertJsonPath('data.0.periodEnd', '2026-01-01T16:00:00+00:00');
});

it('requires authentication', function () {
    $this->getJson('/api/v1/production-records')->assertUnauthorized();
});
