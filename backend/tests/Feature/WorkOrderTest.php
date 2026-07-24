<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\Sanctum;

/**
 * @param  array<string, string>  $overrides
 */
function reportWorkOrder(array $overrides = []): string
{
    return test()->postJson('/api/v1/work-orders', [
        'machine_id' => 'm-1',
        'reason' => 'breakdown',
        ...$overrides,
    ])->json('data.id');
}

it('rejects unauthenticated requests with 401', function () {
    $this->postJson('/api/v1/work-orders', ['machine_id' => 'm-1', 'reason' => 'breakdown'])
        ->assertUnauthorized();
});

it('walks a work order through its full lifecycle', function () {
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();

    Sanctum::actingAs($manager);
    $id = reportWorkOrder();

    $this->postJson("/api/v1/work-orders/{$id}/assign", ['technician_id' => (string) $technician->id])
        ->assertOk()
        ->assertJsonPath('data.status', 'assigned')
        ->assertJsonPath('data.assignedTo', (string) $technician->id);

    Sanctum::actingAs($technician);
    $this->postJson("/api/v1/work-orders/{$id}/start")
        ->assertOk()
        ->assertJsonPath('data.status', 'in_progress');

    $this->postJson("/api/v1/work-orders/{$id}/hold", ['reason' => 'waiting for a part'])
        ->assertOk()
        ->assertJsonPath('data.status', 'on_hold')
        ->assertJsonPath('data.holdReason', 'waiting for a part');

    $this->postJson("/api/v1/work-orders/{$id}/resume")
        ->assertOk()
        ->assertJsonPath('data.status', 'in_progress');

    $this->postJson("/api/v1/work-orders/{$id}/resolve", ['resolution' => 'replaced the bearing'])
        ->assertOk()
        ->assertJsonPath('data.status', 'resolved')
        ->assertJsonPath('data.resolution', 'replaced the bearing');

    Sanctum::actingAs($manager);
    $this->postJson("/api/v1/work-orders/{$id}/close")
        ->assertOk()
        ->assertJsonPath('data.status', 'closed');
});

it('records the reporter as the authenticated user', function () {
    $operator = User::factory()->create();

    Sanctum::actingAs($operator);

    $this->postJson('/api/v1/work-orders', ['machine_id' => 'm-1', 'reason' => 'breakdown'])
        ->assertCreated()
        ->assertJsonPath('data.reportedBy', (string) $operator->id);
});

it('forbids an operator from assigning a work order', function () {
    $operator = User::factory()->create();
    $technician = User::factory()->technician()->create();

    Sanctum::actingAs($operator);
    $id = reportWorkOrder();

    $this->postJson("/api/v1/work-orders/{$id}/assign", ['technician_id' => (string) $technician->id])
        ->assertForbidden();
});

it('forbids a technician who is not the assignee from starting', function () {
    $manager = User::factory()->manager()->create();
    $assignee = User::factory()->technician()->create();
    $someoneElse = User::factory()->technician()->create();

    Sanctum::actingAs($manager);
    $id = reportWorkOrder();
    $this->postJson("/api/v1/work-orders/{$id}/assign", ['technician_id' => (string) $assignee->id])
        ->assertOk();

    Sanctum::actingAs($someoneElse);
    $this->postJson("/api/v1/work-orders/{$id}/start")
        ->assertForbidden();
});

it('rejects an illegal transition with 422', function () {
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();

    Sanctum::actingAs($manager);
    $id = reportWorkOrder();

    Sanctum::actingAs($technician);
    $this->postJson("/api/v1/work-orders/{$id}/start")
        ->assertStatus(422);
});

it('requires a reason to put a work order on hold', function () {
    $manager = User::factory()->manager()->create();
    $technician = User::factory()->technician()->create();

    Sanctum::actingAs($manager);
    $id = reportWorkOrder();
    $this->postJson("/api/v1/work-orders/{$id}/assign", ['technician_id' => (string) $technician->id]);

    Sanctum::actingAs($technician);
    $this->postJson("/api/v1/work-orders/{$id}/start");

    $this->postJson("/api/v1/work-orders/{$id}/hold", ['reason' => ''])
        ->assertStatus(422);
});

it('returns 404 for an unknown work order', function () {
    Sanctum::actingAs(User::factory()->manager()->create());

    $this->getJson('/api/v1/work-orders/does-not-exist')
        ->assertNotFound();
});
