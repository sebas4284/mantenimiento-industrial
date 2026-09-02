<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrderOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysOpen = (int) $this->workOrder->opened_at->diffInDays(now());

        return (new MailMessage)
            ->subject("OT vencida — {$this->workOrder->asset->code}")
            ->line("La orden de trabajo del equipo {$this->workOrder->asset->name} ({$this->workOrder->asset->code}) lleva {$daysOpen} días abierta sin completarse.")
            ->line("Prioridad: {$this->workOrder->priority->label()}")
            ->action('Ver orden de trabajo', route('work-orders.show', $this->workOrder))
            ->line('Por favor da seguimiento lo antes posible.');
    }
}
