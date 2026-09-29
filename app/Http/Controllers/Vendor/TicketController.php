<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\CreateRequest;
use App\Http\Requests\Tickets\MessageRequest;
use App\Http\Requests\Tickets\UpdateRequest;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TicketController extends Controller
{
    protected TicketService $service;

    public function __construct(TicketService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of tickets
     */
    public function index(Request $request): View
    {
        $perPage = $request->get('per_page', 15);
        $filters = [
            'search' => $request->get('search', ''),
            'status' => $request->get('status', ''),
            'ticket_from' => $request->get('ticket_from', ''),
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
            'sort' => (string) $request->get('sort', ''),
        ];

        // Filter by vendor if user is vendor
        $vendorUser = \App\Models\VendorUser::where('user_id', Auth::id())->where('is_active', true)->first();
        if ($vendorUser && $vendorUser->vendor) {
            $filters['vendor_id'] = $vendorUser->vendor->id;
            $filters['ticket_from'] = 'vendor';
        }

        $tickets = $this->service->getPaginatedTickets($perPage, $filters);

        return view('vendor.tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create(): View
    {
        return view('vendor.tickets.create');
    }

    /**
     * Store a newly created ticket
     */
    public function store(CreateRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $ticketData = $request->validated();
        $ticketData['user_id'] = $user->id;
        $ticketData['ticket_from'] = 'vendor';

        $vendorUser = \App\Models\VendorUser::where('user_id', $user->id)->where('is_active', true)->first();
        if ($vendorUser && $vendorUser->vendor) {
            $ticketData['vendor_id'] = $vendorUser->vendor->id;
        }

        // Handle attachments
        if ($request->hasFile('attachments')) {
            $ticketData['attachments'] = $request->file('attachments');
        }

        $ticket = $this->service->createTicket($ticketData);

        return redirect()->route('vendor.tickets.show', $ticket->id)
            ->with('success', __('Ticket created successfully.'));
    }

    /**
     * Display the specified ticket
     */
    public function show(int $id): View
    {
        $ticket = $this->service->getTicketById($id);

        if (! $ticket) {
            abort(404, __('Ticket not found.'));
        }

        // Check authorization
        $vendorUser = \App\Models\VendorUser::where('user_id', Auth::id())->where('is_active', true)->first();
        if ($vendorUser && $vendorUser->vendor && $ticket->vendor_id !== $vendorUser->vendor->id) {
            abort(403, __('You do not have permission to view this ticket.'));
        }

        return view('vendor.tickets.show', compact('ticket'));
    }

    /**
     * Update ticket status
     */
    public function update(UpdateRequest $request, int $id): RedirectResponse
    {
        $ticket = $this->service->getTicketById($id);

        if (! $ticket) {
            return redirect()->route('vendor.tickets.index')
                ->with('error', __('Ticket not found.'));
        }

        $data = $request->validated();

        // Handle attachments if provided
        if ($request->hasFile('attachments')) {
            $data['attachments'] = $request->file('attachments');
        }

        $this->service->updateTicket($id, $data);

        return redirect()->route('vendor.tickets.show', $id)
            ->with('success', __('Ticket updated successfully.'));
    }

    /**
     * Add message to ticket
     */
    public function addMessage(MessageRequest $request, int $id): RedirectResponse
    {
        $ticket = $this->service->getTicketById($id);

        if (! $ticket) {
            return redirect()->route('vendor.tickets.index')
                ->with('error', __('Ticket not found.'));
        }

        $user = Auth::user();
        $messageData = $request->validated();
        $messageData['sender_type'] = 'vendor';
        $messageData['sender_id'] = $user->id;

        // Handle attachments
        if ($request->hasFile('attachments')) {
            $messageData['attachments'] = $request->file('attachments');
        }

        $this->service->addMessage($id, $messageData);

        return redirect()->route('vendor.tickets.show', $id)
            ->with('success', __('Message added successfully.'));
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,resolved,closed'],
        ]);

        $this->service->updateStatus($id, $request->status);

        return redirect()->route('vendor.tickets.show', $id)
            ->with('success', __('Ticket status updated successfully.'));
    }

    /**
     * Remove the specified ticket
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->deleteTicket($id);

        return redirect()->route('vendor.tickets.index')
            ->with('success', __('Ticket deleted successfully.'));
    }
}
