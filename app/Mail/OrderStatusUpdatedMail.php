<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\VendorOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $status,
        public ?VendorOrder $vendorOrder = null
    ) {}

    public function envelope(): Envelope
    {
        $subject = __('Your order status has been updated');

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.status-updated',
            with: [
                'order' => $this->order,
                'vendorOrder' => $this->vendorOrder,
                'status' => $this->status,
            ],
        );
    }
}
