<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketMessageAddedNotification;
use App\Repositories\TicketRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketService
{
    protected TicketRepository $ticketRepository;

    public function __construct(
        TicketRepository $ticketRepository,
        protected NotificationService $notificationService
    ) {
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Get all tickets
     */
    public function getAllTickets(): Collection
    {
        return $this->ticketRepository->getAllTickets();
    }

    /**
     * Get paginated tickets
     */
    public function getPaginatedTickets(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->ticketRepository->getPaginatedTickets($perPage, $filters);
    }

    /**
     * Get ticket by ID
     */
    public function getTicketById(int $id): ?Ticket
    {
        return $this->ticketRepository->getTicketById($id);
    }

    /**
     * Get tickets by user ID
     */
    public function getTicketsByUser(int $userId): Collection
    {
        return $this->ticketRepository->getTicketsByUser($userId);
    }

    /**
     * Get tickets by vendor ID
     */
    public function getTicketsByVendor(int $vendorId): Collection
    {
        return $this->ticketRepository->getTicketsByVendor($vendorId);
    }

    /**
     * Get pending tickets
     */
    public function getPendingTickets(): Collection
    {
        return $this->ticketRepository->getPendingTickets();
    }

    /**
     * Create a new ticket
     */
    public function createTicket(array $ticketData): Ticket
    {
        DB::beginTransaction();
        try {
            // Handle attachments
            $attachments = [];
            if (isset($ticketData['attachments']) && is_array($ticketData['attachments'])) {
                foreach ($ticketData['attachments'] as $file) {
                    if ($file->isValid()) {
                        $attachments[] = $file->store('tickets', 'public');
                    }
                }
                $ticketData['attachments'] = $attachments;
            }

            $ticket = $this->ticketRepository->createTicket($ticketData);

            DB::afterCommit(function () use ($ticket) {
                $ticket->loadMissing(['user', 'vendor']);

                $this->notificationService->notifyAdmins(new TicketCreatedNotification($ticket));

                if ($ticket->vendor_id) {
                    $this->notificationService->notifyVendorUsers((int) $ticket->vendor_id, new TicketCreatedNotification($ticket));
                }
            });

            DB::commit();

            return $ticket->load(['user', 'vendor']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a ticket
     */
    public function updateTicket(int $id, array $ticketData): ?Ticket
    {
        DB::beginTransaction();
        try {
            // Handle new attachments
            if (isset($ticketData['attachments']) && is_array($ticketData['attachments'])) {
                $ticket = $this->ticketRepository->getTicketById($id);
                $existingAttachments = $ticket ? ($ticket->attachments ?? []) : [];

                $newAttachments = [];
                foreach ($ticketData['attachments'] as $file) {
                    if ($file->isValid()) {
                        $newAttachments[] = $file->store('tickets', 'public');
                    }
                }

                $ticketData['attachments'] = array_merge($existingAttachments, $newAttachments);
            }

            $ticket = $this->ticketRepository->updateTicket($id, $ticketData);

            DB::commit();

            return $ticket ? $ticket->load(['user', 'vendor']) : null;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Add message to ticket
     */
    public function addMessage(int $ticketId, array $messageData): TicketMessage
    {
        DB::beginTransaction();
        try {
            $ticket = $this->ticketRepository->getTicketById($ticketId);
            if (! $ticket) {
                throw new \Exception('Ticket not found');
            }

            // Handle attachments
            $attachments = [];
            if (isset($messageData['attachments']) && is_array($messageData['attachments'])) {
                foreach ($messageData['attachments'] as $file) {
                    if ($file->isValid()) {
                        $attachments[] = $file->store('tickets/messages', 'public');
                    }
                }
                $messageData['attachments'] = $attachments;
            }

            $message = TicketMessage::create([
                'ticket_id' => $ticketId,
                'sender_type' => $messageData['sender_type'] ?? 'user',
                'sender_id' => $messageData['sender_id'] ?? Auth::id(),
                'message' => $messageData['message'],
                'attachments' => $attachments,
            ]);

            // Update ticket status to pending if it was resolved/closed
            if (in_array($ticket->status, ['resolved', 'closed'])) {
                $ticket->update(['status' => 'pending']);
            }

            DB::afterCommit(function () use ($ticket, $message) {
                $ticket->loadMissing(['user', 'vendor']);

                $notification = new TicketMessageAddedNotification($ticket, $message);

                // Always notify admins about new messages
                $this->notificationService->notifyAdmins($notification);

                // Notify ticket owner when message is not from user
                if (($message->sender_type ?? 'user') !== 'user') {
                    $this->notificationService->notifyUser($ticket->user, $notification);
                }

                // Notify vendor users when ticket is related to a vendor and message is not from vendor
                if ($ticket->vendor_id && ($message->sender_type ?? 'user') !== 'vendor') {
                    $this->notificationService->notifyVendorUsers((int) $ticket->vendor_id, $notification);
                }
            });

            DB::commit();

            return $message->load('sender');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update ticket status
     */
    public function updateStatus(int $id, string $status): ?Ticket
    {
        $allowedStatuses = ['pending', 'resolved', 'closed'];
        if (! in_array($status, $allowedStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        return $this->ticketRepository->updateTicket($id, ['status' => $status]);
    }

    /**
     * Delete a ticket
     */
    public function deleteTicket(int $id): ?bool
    {
        $ticket = $this->ticketRepository->getTicketById($id);
        if ($ticket) {
            // Delete attachments
            if ($ticket->attachments && is_array($ticket->attachments)) {
                foreach ($ticket->attachments as $attachment) {
                    if (Storage::disk('public')->exists($attachment)) {
                        Storage::disk('public')->delete($attachment);
                    }
                }
            }

            // Delete message attachments
            foreach ($ticket->messages as $message) {
                if ($message->attachments && is_array($message->attachments)) {
                    foreach ($message->attachments as $attachment) {
                        if (Storage::disk('public')->exists($attachment)) {
                            Storage::disk('public')->delete($attachment);
                        }
                    }
                }
            }

            return $this->ticketRepository->deleteTicket($id);
        }

        return false;
    }
}
