<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\MessageRequest;
use App\Http\Requests\Tickets\UpdateRequest;
use App\Services\TicketService;
use App\Services\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TicketController extends Controller
{
    protected TicketService $service;

    protected VendorService $vendorService;

    public function __construct(TicketService $service, VendorService $vendorService)
    {
        $this->service = $service;
        $this->vendorService = $vendorService;
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
            'vendor_id' => $request->get('vendor_id', ''),
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
            'sort' => (string) $request->get('sort', ''),
        ];

        $tickets = $this->service->getPaginatedTickets($perPage, $filters);
        $vendors = $this->vendorService->getActiveVendors();

        return view('admin.tickets.index', compact('tickets', 'vendors'));
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

        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Update ticket status
     */
    public function update(UpdateRequest $request, int $id): RedirectResponse
    {
        $ticket = $this->service->getTicketById($id);

        if (! $ticket) {
            return redirect()->route('admin.tickets.index')
                ->with('error', __('Ticket not found.'));
        }

        $data = $request->validated();

        // Handle attachments if provided
        if ($request->hasFile('attachments')) {
            $data['attachments'] = $request->file('attachments');
        }

        $this->service->updateTicket($id, $data);

        return redirect()->route('admin.tickets.show', $id)
            ->with('success', __('Ticket updated successfully.'));
    }

    /**
     * Add message to ticket
     */
    public function addMessage(MessageRequest $request, int $id): RedirectResponse
    {
        $ticket = $this->service->getTicketById($id);

        if (! $ticket) {
            return redirect()->route('admin.tickets.index')
                ->with('error', __('Ticket not found.'));
        }

        $user = Auth::user();
        $messageData = $request->validated();
        $messageData['sender_type'] = 'admin';
        $messageData['sender_id'] = $user->id;

        // Handle attachments
        if ($request->hasFile('attachments')) {
            $messageData['attachments'] = $request->file('attachments');
        }

        $this->service->addMessage($id, $messageData);

        return redirect()->route('admin.tickets.show', $id)
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

        return redirect()->route('admin.tickets.show', $id)
            ->with('success', __('Ticket status updated successfully.'));
    }

    /**
     * Remove the specified ticket
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->deleteTicket($id);

        return redirect()->route('admin.tickets.index')
            ->with('success', __('Ticket deleted successfully.'));
    }
}
