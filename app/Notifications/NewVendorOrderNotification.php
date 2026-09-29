<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\VendorOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewVendorOrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public VendorOrder $vendorOrder
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('New order received'))
            ->line(__('A new order #:order_id has been placed.', ['order_id' => $this->order->id]))
            ->line(__('Vendor order #:vendor_order_id', ['vendor_order_id' => $this->vendorOrder->id]))
            ->line(__('Status: :status', ['status' => $this->vendorOrder->status]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'vendor_order_id' => $this->vendorOrder->id,
            'title' => __('New order received'),
            'message' => __('A new order #:order_id has been placed.', ['order_id' => $this->order->id]),
            'status' => $this->vendorOrder->status,
        ];
    }
}
