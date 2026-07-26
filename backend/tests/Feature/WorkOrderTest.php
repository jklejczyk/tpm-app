<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Psr\Clock\ClockInterface;

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

it('rejects assigning a work order to a non-technician with 422', function () {
    $manager = User::factory()->manager()->create();
    $operator = User::factory()->create();

    Sanctum::actingAs($manager);
    $id = reportWorkOrder();

    $this->postJson("/api/v1/work-orders/{$id}/assign", ['technician_id' => (string) $operator->id])
        ->assertStatus(422);
});

it('rejects assigning a work order to a non-existent user with 422', function () {
    $manager = User::factory()->manager()->create();

    Sanctum::actingAs($manager);
    $id = reportWorkOrder();

    $this->postJson("/api/v1/work-orders/{$id}/assign", ['technician_id' => '999999'])
        ->assertStatus(422);
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

it('lists work orders', function () {
    Sanctum::actingAs(User::factory()->manager()->create());
    reportWorkOrder(['machine_id' => 'm-1']);
    reportWorkOrder(['machine_id' => 'm-2']);

    $this->getJson('/api/v1/work-orders')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('returns 404 for an unknown work order', function () {
    Sanctum::actingAs(User::factory()->manager()->create());

    $this->getJson('/api/v1/work-orders/does-not-exist')
        ->assertNotFound();
});

it('renders the reporter and assignee display names', function () {
    $manager = User::factory()->manager()->create(['name' => 'Boss']);
    $technician = User::factory()->technician()->create(['name' => 'Fixer']);

    Sanctum::actingAs($manager);
    $id = reportWorkOrder();
    $this->postJson("/api/v1/work-orders/{$id}/assign", ['technician_id' => (string) $technician->id])
        ->assertOk();

    $this->getJson("/api/v1/work-orders/{$id}")
        ->assertOk()
        ->assertJsonPath('data.reportedByName', 'Boss')
        ->assertJsonPath('data.assignedToName', 'Fixer');
});

it('batches user-name lookups on the list instead of querying per row', function () {
    $manager = User::factory()->manager()->create();
    $technicians = User::factory()->technician()->count(3)->create();

    Sanctum::actingAs($manager);
    foreach ($technicians as $i => $technician) {
        $id = reportWorkOrder(['machine_id' => "m-{$i}"]);
        $this->postJson("/api/v1/work-orders/{$id}/assign", ['technician_id' => (string) $technician->id])
            ->assertOk();
    }

    $userQueries = 0;
    DB::listen(function ($query) use (&$userQueries): void {
        if (preg_match('/\busers\b/i', $query->sql) === 1) {
            $userQueries++;
        }
    });

    $this->getJson('/api/v1/work-orders')
        ->assertOk()
        ->assertJsonCount(3, 'data');

    // Eager loading batches reporters and assignees into two constant queries,
    // regardless of how many work orders the page holds — never one per row.
    expect($userQueries)->toBeLessThanOrEqual(2);
});

it('paginates the list', function () {
    Sanctum::actingAs(User::factory()->manager()->create());
    reportWorkOrder(['machine_id' => 'm-1']);
    reportWorkOrder(['machine_id' => 'm-2']);
    reportWorkOrder(['machine_id' => 'm-3']);

    $this->getJson('/api/v1/work-orders?per_page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.last_page', 2);

    $this->getJson('/api/v1/work-orders?per_page=2&page=2')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.current_page', 2);
});

it('sorts the list by a whitelisted column', function () {
    Sanctum::actingAs(User::factory()->manager()->create());
    reportWorkOrder(['machine_id' => 'm-c']);
    reportWorkOrder(['machine_id' => 'm-a']);
    reportWorkOrder(['machine_id' => 'm-b']);

    $this->getJson('/api/v1/work-orders?sort=machine_id&direction=asc')
        ->assertOk()
        ->assertJsonPath('data.0.machineId', 'm-a')
        ->assertJsonPath('data.2.machineId', 'm-c');

    $this->getJson('/api/v1/work-orders?sort=machine_id&direction=desc')
        ->assertOk()
        ->assertJsonPath('data.0.machineId', 'm-c');
});

it('rejects an unknown sort column with 422', function () {
    Sanctum::actingAs(User::factory()->manager()->create());

    $this->getJson('/api/v1/work-orders?sort=id')
        ->assertStatus(422);
});

it('sorts the list by reason', function () {
    Sanctum::actingAs(User::factory()->manager()->create());
    reportWorkOrder(['machine_id' => 'm-1', 'reason' => 'operator_report']);
    reportWorkOrder(['machine_id' => 'm-2', 'reason' => 'breakdown']);
    reportWorkOrder(['machine_id' => 'm-3', 'reason' => 'inspection']);

    $this->getJson('/api/v1/work-orders?sort=reason&direction=asc')
        ->assertOk()
        ->assertJsonPath('data.0.reason', 'breakdown')
        ->assertJsonPath('data.2.reason', 'operator_report');
});

it('sorts the list by the assignee display name, not the raw id', function () {
    $manager = User::factory()->manager()->create();
    $alice = User::factory()->technician()->create(['name' => 'Alice']);
    $bob = User::factory()->technician()->create(['name' => 'Bob']);

    Sanctum::actingAs($manager);
    $first = reportWorkOrder(['machine_id' => 'm-1']);
    $second = reportWorkOrder(['machine_id' => 'm-2']);
    $this->postJson("/api/v1/work-orders/{$first}/assign", ['technician_id' => (string) $bob->id])->assertOk();
    $this->postJson("/api/v1/work-orders/{$second}/assign", ['technician_id' => (string) $alice->id])->assertOk();

    $this->getJson('/api/v1/work-orders?sort=assigned_to&direction=asc')
        ->assertOk()
        ->assertJsonPath('data.0.assignedToName', 'Alice')
        ->assertJsonPath('data.1.assignedToName', 'Bob');
});

it('includes the reported timestamp on the list', function () {
    Sanctum::actingAs(User::factory()->manager()->create());
    reportWorkOrder(['machine_id' => 'm-1']);

    $response = $this->getJson('/api/v1/work-orders')->assertOk();

    expect($response->json('data.0.reportedAt'))->not->toBeNull();
});

it('stamps the report time from the injected clock', function () {
    $instant = new DateTimeImmutable('2026-01-01T10:00:00+00:00');
    $this->app->bind(ClockInterface::class, fn () => new class($instant) implements ClockInterface
    {
        public function __construct(private DateTimeImmutable $instant) {}

        public function now(): DateTimeImmutable
        {
            return $this->instant;
        }
    });

    Sanctum::actingAs(User::factory()->manager()->create());

    $this->postJson('/api/v1/work-orders', ['machine_id' => 'm-1', 'reason' => 'breakdown'])
        ->assertCreated()
        ->assertJsonPath('data.reportedAt', '2026-01-01T10:00:00+00:00');
});

it('includes the reported timestamp on a single work order', function () {
    Sanctum::actingAs(User::factory()->manager()->create());
    $id = reportWorkOrder();

    $response = $this->getJson("/api/v1/work-orders/{$id}")->assertOk();

    expect($response->json('data.reportedAt'))->not->toBeNull();
});
