<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\CreateRequest;
use App\Http\Requests\Tickets\MessageRequest;
use App\Http\Requests\Tickets\UpdateRequest;
use App\Http\Resources\TicketMessageResource;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    protected TicketService $service;

    public function __construct(TicketService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of user tickets
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = (int) $request->get('per_page', 15);
        $filters = [
            'search' => (string) $request->get('search', ''),
            'status' => (string) $request->get('status', ''),
            'from_date' => (string) $request->get('from_date', ''),
            'to_date' => (string) $request->get('to_date', ''),
            'sort' => (string) $request->get('sort', ''),
            'user_id' => $user->id,
        ];

        $tickets = $this->service->getPaginatedTickets($perPage, $filters);

        return response()->json([
            'success' => true,
            'data' => TicketResource::collection($tickets),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * Store a newly created ticket
     */
    public function store(CreateRequest $request): JsonResponse
    {
        $user = Auth::user();
        $ticketData = $request->validated();
        $ticketData['user_id'] = $user->id;
        $ticketData['ticket_from'] = 'user';

        // Handle attachments
        if ($request->hasFile('attachments')) {
            $ticketData['attachments'] = $request->file('attachments');
        }

        $ticket = $this->service->createTicket($ticketData);

        return response()->json([
            'success' => true,
            'message' => __('Ticket created successfully.'),
            'data' => new TicketResource($ticket->load(['user', 'vendor', 'messages.sender'])),
        ], 201);
    }

    /**
     * Display the specified ticket
     */
    public function show(Ticket $ticket): JsonResponse
    {
        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => __('Ticket not found.'),
            ], 404);
        }

        // Check authorization
        $user = Auth::user();
        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to view this ticket.'),
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new TicketResource($ticket->load(['user', 'vendor', 'messages.sender'])),
        ]);
    }

    /**
     * Update ticket status
     */
    public function update(UpdateRequest $request, Ticket $ticket): JsonResponse
    {
        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => __('Ticket not found.'),
            ], 404);
        }

        // Check authorization
        $user = Auth::user();
        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to update this ticket.'),
            ], 403);
        }

        $data = $request->validated();

        // Handle attachments if provided
        if ($request->hasFile('attachments')) {
            $data['attachments'] = $request->file('attachments');
        }

        $ticket = $this->service->updateTicket($ticket->id, $data);

        return response()->json([
            'success' => true,
            'message' => __('Ticket updated successfully.'),
            'data' => new TicketResource($ticket->load(['user', 'vendor', 'messages.sender'])),
        ]);
    }

    /**
     * Add message to ticket
     */
    public function addMessage(MessageRequest $request, Ticket $ticket): JsonResponse
    {
        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => __('Ticket not found.'),
            ], 404);
        }

        // Check authorization
        $user = Auth::user();
        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to add messages to this ticket.'),
            ], 403);
        }

        $messageData = $request->validated();
        $messageData['sender_type'] = 'user';
        $messageData['sender_id'] = $user->id;

        // Handle attachments
        if ($request->hasFile('attachments')) {
            $messageData['attachments'] = $request->file('attachments');
        }

        $message = $this->service->addMessage($ticket->id, $messageData);

        return response()->json([
            'success' => true,
            'message' => __('Message added successfully.'),
            'data' => new TicketMessageResource($message->load('sender')),
        ], 201);
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, Ticket $ticket): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,resolved,closed'],
        ]);

        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => __('Ticket not found.'),
            ], 404);
        }

        // Check authorization
        $user = Auth::user();
        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to update this ticket.'),
            ], 403);
        }

        $ticket = $this->service->updateStatus($ticket->id, $request->status);

        return response()->json([
            'success' => true,
            'message' => __('Ticket status updated successfully.'),
            'data' => new TicketResource($ticket->load(['user', 'vendor', 'messages.sender'])),
        ]);
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => __('Ticket not found.'),
            ], 404);
        }
        // Check authorization
        $user = Auth::user();
        if ($ticket->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('You do not have permission to delete this ticket.'),
            ], 403);
        }
        $this->service->deleteTicket($ticket->id);

        return response()->json([
            'success' => true,
            'message' => __('Ticket deleted successfully.'),
        ]);
    }
}
