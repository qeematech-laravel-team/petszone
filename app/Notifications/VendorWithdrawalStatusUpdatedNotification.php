<?php

namespace App\Notifications;

use App\Models\VendorWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorWithdrawalStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public VendorWithdrawal $withdrawal,
        public string $status,
        public ?string $notes = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->status) {
            'approved' => __('Your withdrawal request has been approved'),
            'rejected' => __('Your withdrawal request has been rejected'),
            default => __('Your withdrawal request status has been updated'),
        };

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.vendor_withdrawals.status-updated', [
                'withdrawal' => $this->withdrawal,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'amount' => $this->withdrawal->amount,
            'status' => $this->status,
            'title' => $this->getTitle(),
            'message' => $this->buildMessage(),
        ];
    }

    protected function getTitle(): string
    {
        return match ($this->status) {
            'approved' => __('Withdrawal Request Approved'),
            'rejected' => __('Withdrawal Request Rejected'),
            default => __('Withdrawal Request Status Updated'),
        };
    }

    protected function buildMessage(): string
    {
        $amount = number_format($this->withdrawal->amount, 2);
        $withdrawalId = $this->withdrawal->id;

        return match ($this->status) {
            'approved' => __(
                'Your withdrawal request #:id for :amount has been approved and processed.',
                ['id' => $withdrawalId, 'amount' => $amount]
            ),
            'rejected' => __(
                'Your withdrawal request #:id for :amount has been rejected. :notes',
                [
                    'id' => $withdrawalId,
                    'amount' => $amount,
                    'notes' => $this->notes ? __('Reason: :notes', ['notes' => $this->notes]) : '',
                ]
            ),
            default => __(
                'Your withdrawal request #:id for :amount status has been changed to :status.',
                ['id' => $withdrawalId, 'amount' => $amount, 'status' => $this->status]
            ),
        };
    }
}
