<?php

namespace App\Notifications;

use App\Models\MaintenancePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PreventiveMaintenanceDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MaintenancePlan $plan) {}

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
            ->subject("Mantenimiento preventivo próximo — {$this->plan->asset->code}")
            ->line("El plan \"{$this->plan->name}\" del equipo {$this->plan->asset->name} ({$this->plan->asset->code}) vence el {$this->plan->next_due_date->format('d/m/Y')}.")
            ->action('Ver planes de mantenimiento', route('maintenance-plans.index'))
            ->line('Prográmalo con el equipo de mantenimiento.');
    }
}
