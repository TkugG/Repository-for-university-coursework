<?php

namespace App\Observers;

use App\Services\EmailNotificationService;

class TicketObserver
{
    private EmailNotificationService $emailService;

    public function __construct()
    {
        $this->emailService = new EmailNotificationService();
    }

    public function onTicketCreated(array $payload): void
    {
        try {
            $ticket = $payload['ticket'] ?? [];
            if (!empty($ticket)) {
                $this->emailService->notifyTicketCreated($ticket);
            }
        } catch (\Throwable $e) {
            error_log("Failed to send ticket.created email: " . $e->getMessage());
        }
    }

    public function onTicketAssigned(array $payload): void
    {
        try {
            $ticket = $payload['ticket'] ?? [];
            $technician = $payload['technician'] ?? [];
            $note = $payload['note'] ?? null;
            if (!empty($ticket) && !empty($technician)) {
                $this->emailService->notifyTicketAssigned($ticket, $technician, $note);
            }
        } catch (\Throwable $e) {
            error_log("Failed to send ticket.assigned email: " . $e->getMessage());
        }
    }

    public function onStatusUpdated(array $payload): void
    {
        try {
            $ticket = $payload['ticket'] ?? [];
            $fromStatus = $payload['from_status'] ?? '';
            $toStatus = $payload['to_status'] ?? '';
            $note = $payload['note'] ?? null;

            if (!empty($ticket) && $fromStatus && $toStatus) {
                $this->emailService->notifyStatusUpdated($ticket, $fromStatus, $toStatus, $note);
            }
        } catch (\Throwable $e) {
            error_log("Failed to send ticket.status_updated email: " . $e->getMessage());
        }
    }

    public function onTicketResolved(array $payload): void
    {
        try {
            $ticket = $payload['ticket'] ?? [];
            if (!empty($ticket)) {
                $this->emailService->notifyTicketResolved($ticket);
            }
        } catch (\Throwable $e) {
            error_log("Failed to send ticket.resolved email: " . $e->getMessage());
        }
    }

    public function onTicketRejected(array $payload): void
    {
        try {
            $ticket = $payload['ticket'] ?? [];
            $reason = $payload['reason'] ?? '';
            $actor = $payload['actor'] ?? [];
            if (!empty($ticket)) {
                $this->emailService->notifyTicketRejected($ticket, $reason, $actor);
            }
        } catch (\Throwable $e) {
            error_log("Failed to send ticket.rejected email: " . $e->getMessage());
        }
    }
}
