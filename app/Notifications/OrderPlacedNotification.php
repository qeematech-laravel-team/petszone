<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
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
            ->subject(__('Order placed successfully'))
            ->line(__('Your order #:order_id has been created successfully.', ['order_id' => $this->order->id]))
            ->line(__('Status: :status', ['status' => $this->order->status]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'title' => __('Order placed successfully'),
            'message' => __('Your order #:order_id has been created successfully.', ['order_id' => $this->order->id]),
            'status' => $this->order->status,
        ];
    }
}
