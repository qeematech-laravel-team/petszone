<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('New support ticket'))
            ->line(__('A new ticket has been created.'))
            ->line(__('Ticket #:ticket_id', ['ticket_id' => $this->ticket->id]))
            ->line(__('Subject: :subject', ['subject' => $this->ticket->subject]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => __('New support ticket'),
            'message' => __('Ticket #:ticket_id - :subject', [
                'ticket_id' => $this->ticket->id,
                'subject' => $this->ticket->subject,
            ]),
            'status' => $this->ticket->status,
            'ticket_from' => $this->ticket->ticket_from,
            'vendor_id' => $this->ticket->vendor_id,
        ];
    }
}
