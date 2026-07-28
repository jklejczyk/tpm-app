<?php

namespace App\Notifications;

use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tpm\WorkOrder\Event\WorkOrderReported;

class BreakdownReported extends Notification implements ShouldQueue
{
    use Queueable;

    private readonly string $machineId;

    private readonly string $reason;

    private readonly DateTimeImmutable $reportedAt;

    public function __construct(WorkOrderReported $event)
    {
        $this->machineId = $event->machineId->value;
        $this->reason = $event->reason->value;
        $this->reportedAt = $event->reportedAt;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New breakdown reported: {$this->machineId}")
            ->line("A breakdown has been reported for machine {$this->machineId}.")
            ->line("Reason: {$this->reason}")
            ->line('Reported at: '.$this->reportedAt->format(DateTimeInterface::ATOM));
    }
}
