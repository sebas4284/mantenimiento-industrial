<?php

namespace App\Notifications;

use App\Models\SparePart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SparePart $sparePart) {}

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
            ->subject("Stock bajo — {$this->sparePart->name}")
            ->line("El repuesto \"{$this->sparePart->name}\" ({$this->sparePart->code}) tiene {$this->sparePart->stock_quantity} unidades, por debajo del mínimo de {$this->sparePart->minimum_stock}.")
            ->action('Ver inventario', route('spare-parts.index'))
            ->line('Considera reordenar pronto.');
    }
}
