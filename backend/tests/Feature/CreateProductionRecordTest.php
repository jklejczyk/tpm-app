<?php

use App\Models\MachineModel;
use App\Models\ProductionRecordModel;
use App\Models\User;

function validPayload(array $overrides = []): array
{
    return array_merge([
        'machine_id' => 'press-01',
        'period_start' => '2026-02-01 08:00:00',
        'period_end' => '2026-02-01 16:00:00',
        'produced_units' => 900,
        'defective_units' => 30,
        'ideal_cycle_time' => 30,
    ], $overrides);
}

beforeEach(function () {
    MachineModel::factory()->create(['id' => 'press-01', 'name' => 'Hydraulic Press #1']);
});

it('creates a production record for an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/v1/production-records', validPayload())
        ->assertCreated()
        ->assertJsonPath('data.machineId', 'press-01')
        ->assertJsonPath('data.periodStart', '2026-02-01T08:00:00+00:00');

    expect(ProductionRecordModel::where('machine_id', 'press-01')
        ->where('period_start', '2026-02-01 08:00:00')->exists())->toBeTrue();
});

it('rejects a window whose end is not after its start', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->postJson('/api/v1/production-records', validPayload([
            'period_start' => '2026-02-01 16:00:00',
            'period_end' => '2026-02-01 08:00:00',
        ]))->assertStatus(422);
});

it('rejects more defective units than produced', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->postJson('/api/v1/production-records', validPayload([
            'produced_units' => 10, 'defective_units' => 11,
        ]))->assertStatus(422);
});

it('rejects a non-positive ideal cycle time', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->postJson('/api/v1/production-records', validPayload(['ideal_cycle_time' => 0]))
        ->assertStatus(422);
});

it('rejects an unknown machine', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->postJson('/api/v1/production-records', validPayload(['machine_id' => 'ghost-99']))
        ->assertStatus(422);
});

it('requires authentication', function () {
    $this->postJson('/api/v1/production-records', validPayload())->assertUnauthorized();
});
