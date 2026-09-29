<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderForAdminNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('New order created'))
            ->line(__('A new order #:order_id has been created.', ['order_id' => $this->order->id]))
            ->line(__('Status: :status', ['status' => $this->order->status]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'title' => __('New order created'),
            'message' => __('A new order #:order_id has been created.', ['order_id' => $this->order->id]),
            'status' => $this->order->status,
        ];
    }
}
