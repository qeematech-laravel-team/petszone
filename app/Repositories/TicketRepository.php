<?php

namespace App\Repositories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketRepository
{
    protected Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get all tickets
     */
    public function getAllTickets(): Collection
    {
        return Ticket::with(['user', 'vendor', 'messages'])->latest()->get();
    }

    /**
     * Get paginated tickets with filters
     */
    public function getPaginatedTickets(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Ticket::with([
            'user',
            'vendor' => fn ($vendorQuery) => \App\Models\Vendor::constrainVisibleRatingAggregates($vendorQuery),
            'messages',
        ]);

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['ticket_from']) && $filters['ticket_from'] !== '') {
            $query->where('ticket_from', $filters['ticket_from']);
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['vendor_id']) && $filters['vendor_id'] !== '') {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    /**
     * Get ticket by ID
     */
    public function getTicketById(int $id): ?Ticket
    {
        return Ticket::with(['user', 'vendor', 'messages.sender'])->find($id);
    }

    /**
     * Get tickets by user ID
     */
    public function getTicketsByUser(int $userId): Collection
    {
        return Ticket::where('user_id', $userId)
            ->with(['vendor', 'messages.sender'])
            ->latest()
            ->get();
    }

    /**
     * Get tickets by vendor ID
     */
    public function getTicketsByVendor(int $vendorId): Collection
    {
        return Ticket::where('vendor_id', $vendorId)
            ->with(['user', 'messages.sender'])
            ->latest()
            ->get();
    }

    /**
     * Get pending tickets
     */
    public function getPendingTickets(): Collection
    {
        return Ticket::pending()->with(['user', 'vendor'])->latest()->get();
    }

    /**
     * Create a new ticket
     */
    public function createTicket(array $ticketData): Ticket
    {
        return Ticket::create($ticketData);
    }

    /**
     * Update a ticket
     */
    public function updateTicket(int $id, array $ticketData): ?Ticket
    {
        $ticket = Ticket::find($id);
        if ($ticket) {
            $ticket->update($ticketData);

            return $ticket->fresh();
        }

        return null;
    }

    /**
     * Delete a ticket
     */
    public function deleteTicket(int $id): ?bool
    {
        $ticket = Ticket::find($id);
        if ($ticket) {
            return $ticket->delete();
        }

        return null;
    }
}
