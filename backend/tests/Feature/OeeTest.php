<?php

use App\Models\MachineModel;
use App\Models\ProductionRecordModel;
use App\Models\User;
use App\Models\WorkOrderModel;
use Tpm\WorkOrder\WorkOrderStatus;

function oeeUrl(string $machineId, string $from, string $to): string
{
    return "/api/v1/machines/{$machineId}/oee?from=".urlencode($from).'&to='.urlencode($to);
}

beforeEach(function () {
    MachineModel::factory()->create(['id' => 'press-01', 'name' => 'Hydraulic Press #1']);
});

it('returns the OEE breakdown for a machine and window', function () {
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
        ->getJson(oeeUrl('press-01', '2026-01-01 08:00:00', '2026-01-01 16:00:00'))
        ->assertOk()
        ->assertJsonPath('data.machineId', 'press-01')
        ->assertJsonPath('data.producedUnits', 800)
        ->assertJsonPath('data.downtimeSeconds', 0)
        ->assertJsonPath('data.availability', 1.0);
});

it('drops availability when a work order downtime overlaps the window', function () {
    $user = User::factory()->create();
    ProductionRecordModel::factory()->create([
        'machine_id' => 'press-01',
        'period_start' => '2026-01-01 08:00:00',
        'period_end' => '2026-01-01 16:00:00',
        'produced_units' => 800,
        'defective_units' => 20,
        'ideal_cycle_time' => 30,
    ]);
    // 4h downtime inside the 8h window -> availability 0.5
    WorkOrderModel::factory()->create([
        'machine_id' => 'press-01',
        'reported_by' => $user->id,
        'status' => WorkOrderStatus::Resolved->value,
        'reported_at' => '2026-01-01 10:00:00',
        'resolved_at' => '2026-01-01 14:00:00',
    ]);

    $this->actingAs($user)
        ->getJson(oeeUrl('press-01', '2026-01-01 08:00:00', '2026-01-01 16:00:00'))
        ->assertOk()
        ->assertJsonPath('data.downtimeSeconds', 4 * 3600)
        ->assertJsonPath('data.availability', 0.5);
});

it('returns 404 when there is no production record for the window', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(oeeUrl('press-01', '2026-01-01 08:00:00', '2026-01-01 16:00:00'))
        ->assertNotFound();
});

it('rejects a window whose end is not after its start', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(oeeUrl('press-01', '2026-01-01 16:00:00', '2026-01-01 08:00:00'))
        ->assertStatus(422);
});

it('rejects an OEE window longer than 31 days with 422', function () {
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
        ->getJson(oeeUrl('press-01', '2026-01-01 00:00:00', '2026-03-01 00:00:00'))
        ->assertStatus(422);
});

it('requires authentication', function () {
    $this->getJson(oeeUrl('press-01', '2026-01-01 08:00:00', '2026-01-01 16:00:00'))
        ->assertUnauthorized();
});
