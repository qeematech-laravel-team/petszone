<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketMessageAddedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketMessage $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('New ticket message'))
            ->line(__('A new message was added to ticket #:ticket_id.', ['ticket_id' => $this->ticket->id]))
            ->line(__('From: :from', ['from' => $this->message->sender_type]))
            ->line(str($this->message->message)->limit(120));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_message_id' => $this->message->id,
            'title' => __('New ticket message'),
            'message' => __('New message on ticket #:ticket_id', ['ticket_id' => $this->ticket->id]),
            'sender_type' => $this->message->sender_type,
            'sender_id' => $this->message->sender_id,
        ];
    }
}
