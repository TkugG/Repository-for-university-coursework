<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\TicketRepository;
use App\Services\TicketStatusService;
use Exception;

class ApiController
{
    private TicketRepository $ticketRepo;
    private TicketStatusService $statusService;

    public function __construct()
    {
        $this->ticketRepo = new TicketRepository();
        $this->statusService = new TicketStatusService();
    }

    public function getTickets(Request $request): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $filters = $request->all();
        if ($user['role'] === 'user') {
            $filters['user_id'] = $user['id'];
        } elseif ($user['role'] === 'technician') {
            $filters['technician_id'] = $user['id'];
        }

        $tickets = $this->ticketRepo->search($filters);
        Response::json(['data' => $tickets]);
    }

    public function getTicketDetail(Request $request, string $id): void
    {
        $ticketId = (int)$id;
        $ticket = $this->ticketRepo->find($ticketId);

        if (!$ticket) {
            Response::json(['error' => 'Ticket not found'], 404);
        }

        Response::json(['data' => $ticket]);
    }

    public function updateStatus(Request $request, string $id): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $ticketId = (int)$id;
        $status = $request->input('status');
        $note = $request->input('note');

        if (!$status) {
            Response::json(['error' => 'Status is required'], 422);
        }

        try {
            $this->statusService->transition($ticketId, $status, $user, $note);
            $updatedTicket = $this->ticketRepo->find($ticketId);
            Response::json(['success' => true, 'data' => $updatedTicket]);
        } catch (Exception $e) {
            Response::json(['error' => $e->getMessage()], 400);
        }
    }

    public function getStats(Request $request): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(['error' => 'Unauthorized'], 401);
        }

        $counts = $this->ticketRepo->getStatusCounts(
            userId: $user['role'] === 'user' ? $user['id'] : null,
            techId: $user['role'] === 'technician' ? $user['id'] : null
        );

        Response::json([
            'counts' => $counts,
            'mttr_hours' => $this->ticketRepo->getAverageResolutionTimeHours(),
        ]);
    }
}
