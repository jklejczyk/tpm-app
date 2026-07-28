<?php

declare(strict_types=1);

use App\Models\MachineModel;
use App\Models\User;
use App\Notifications\BreakdownReported;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

it('queues a breakdown email to managers when a work order is reported', function () {
    Notification::fake();

    MachineModel::firstOrCreate(['id' => 'm-1'], ['name' => 'Machine m-1']);
    $manager = User::factory()->manager()->create();
    $reporter = User::factory()->create();

    test()->actingAs($reporter);
    $this->postJson('/api/v1/work-orders', ['machine_id' => 'm-1', 'reason' => 'breakdown'])
        ->assertCreated();

    Notification::assertSentTo(
        $manager,
        BreakdownReported::class,
        function (BreakdownReported $notification) use ($manager) {
            $mail = $notification->toMail($manager);

            return str_contains($mail->subject, 'm-1');
        }
    );
});

it('does not notify non-managers', function () {
    Notification::fake();

    MachineModel::firstOrCreate(['id' => 'm-1'], ['name' => 'Machine m-1']);
    $technician = User::factory()->technician()->create();
    $reporter = User::factory()->create();

    test()->actingAs($reporter);
    $this->postJson('/api/v1/work-orders', ['machine_id' => 'm-1', 'reason' => 'breakdown'])
        ->assertCreated();

    Notification::assertNotSentTo($technician, BreakdownReported::class);
});

it('marks the breakdown notification as queued', function () {
    $implements = class_implements(BreakdownReported::class);

    expect($implements)->toContain(ShouldQueue::class);
});
