<?php

namespace App\Services;

use App\Core\Database;
use App\Core\EventDispatcher;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Repositories\TicketRepository;
use App\Repositories\StatusLogRepository;
use App\Repositories\RatingRepository;
use App\Repositories\RepairLogRepository;
use App\Repositories\TicketPartsRepository;
use App\Repositories\SparePartRepository;
use Exception;

class TicketStatusService
{
    private TicketRepository $ticketRepo;
    private StatusLogRepository $logRepo;
    private RatingRepository $ratingRepo;
    private RepairLogRepository $repairLogRepo;
    private TicketPartsRepository $ticketPartsRepo;
    private SparePartRepository $sparePartRepo;
    private EventDispatcher $events;
    private Database $db;

    public function __construct()
    {
        $this->ticketRepo = new TicketRepository();
        $this->logRepo = new StatusLogRepository();
        $this->ratingRepo = new RatingRepository();
        $this->repairLogRepo = new RepairLogRepository();
        $this->ticketPartsRepo = new TicketPartsRepository();
        $this->sparePartRepo = new SparePartRepository();
        $this->events = EventDispatcher::getInstance();
        $this->db = Database::getInstance();
    }

    /**
     * General State Machine Transition
     */
    public function transition(int $ticketId, string $newStatusStr, array $actor, ?string $note = null, ?array $ratingData = null): void
    {
        $ticket = $this->ticketRepo->find($ticketId);
        if (!$ticket) {
            throw new Exception("ไม่พบข้อมูล Ticket #{$ticketId}");
        }

        $currentStatus = TicketStatus::tryFrom($ticket['status']);
        $targetStatus = TicketStatus::tryFrom($newStatusStr);

        if (!$targetStatus) {
            throw new Exception("สถานะปลายทางไม่ถูกต้อง: '{$newStatusStr}'");
        }

        // 1. Validate State Machine Transitions
        $this->validateStateTransition($currentStatus, $targetStatus);

        // 2. Check Role & Permissions
        $this->checkPermission($actor, $ticket, $currentStatus, $targetStatus);

        // 3. Perform Updates within DB Transaction
        $this->db->beginTransaction();

        try {
            $timeField = null;
            if ($targetStatus === TicketStatus::RESOLVED) {
                $timeField = 'resolved_at';
            } elseif ($targetStatus === TicketStatus::CLOSED) {
                $timeField = 'closed_at';
            }

            $this->ticketRepo->updateStatus($ticketId, $targetStatus->value, $timeField);

            $this->logRepo->create([
                'ticket_id' => $ticketId,
                'changed_by' => $actor['id'],
                'from_status' => $ticket['status'],
                'to_status' => $targetStatus->value,
                'note' => $note ?: "เปลี่ยนสถานะเป็น {$targetStatus->label()}",
            ]);

            // If closed with rating
            if ($targetStatus === TicketStatus::CLOSED && !empty($ratingData)) {
                $this->ratingRepo->create([
                    'ticket_id' => $ticketId,
                    'score' => (int) $ratingData['score'],
                    'feedback' => $ratingData['feedback'] ?? null,
                ]);
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        $updatedTicket = $this->ticketRepo->find($ticketId);

        // 4. Dispatch Domain Events
        $this->events->dispatch('ticket.status_updated', [
            'ticket' => $updatedTicket,
            'from_status' => $ticket['status'],
            'to_status' => $targetStatus->value,
            'note' => $note,
            'actor' => $actor,
        ]);

        if ($targetStatus === TicketStatus::RESOLVED) {
            $this->events->dispatch('ticket.resolved', [
                'ticket' => $updatedTicket,
                'actor' => $actor,
            ]);
        }

        if ($ticket['status'] === TicketStatus::RESOLVED->value && $targetStatus === TicketStatus::IN_PROGRESS) {
            $this->events->dispatch('ticket.rejected', [
                'ticket' => $updatedTicket,
                'reason' => $note,
                'actor' => $actor,
            ]);
        }
    }

    /**
     * User Cancels Ticket (only allowed before repair starts)
     */
    public function cancelTicket(int $ticketId, string $reason, array $actor): void
    {
        $ticket = $this->ticketRepo->find($ticketId);
        if (!$ticket) {
            throw new Exception("ไม่พบข้อมูล Ticket");
        }

        if ($actor['role'] === 'user' && (int)$ticket['user_id'] !== (int)$actor['id']) {
            throw new Exception("คุณสามารถยกเลิกได้เฉพาะ Ticket ของตนเองเท่านั้น");
        }

        $currentStatus = TicketStatus::tryFrom($ticket['status']);
        if (!$currentStatus || !$currentStatus->canCancel()) {
            throw new Exception("ไม่สามารถยกเลิกได้ เนื่องจากช่างเริ่มปฏิบัติงานแล้ว หรือใบงานปิดไปแล้ว");
        }

        $this->transition($ticketId, 'cancelled', $actor, "ผู้แจ้งขอยกเลิกคำขอ: {$reason}");
    }

    /**
     * Dispatcher/Supervisor Screens & Approves Ticket
     */
    public function approveTicket(int $ticketId, array $actor, ?string $note = null): void
    {
        $this->transition($ticketId, 'approved', $actor, $note ?: 'Supervisor ตรวจสอบและอนุมัติรับเรื่อง');
    }

    /**
     * Dispatcher Assigns Ticket to Technician and sets Priority & SLA
     */
    public function assignTechnician(int $ticketId, int $technicianId, array $actor, ?string $priority = null, ?string $note = null): void
    {
        $ticket = $this->ticketRepo->find($ticketId);
        if (!$ticket) {
            throw new Exception("ไม่พบข้อมูล Ticket #{$ticketId}");
        }

        $userRepo = new \App\Repositories\UserRepository();
        $technician = $userRepo->find($technicianId);
        if (!$technician || $technician['role'] !== 'technician') {
            throw new Exception("ไม่พบช่างเทคนิคที่เลือก หรือผู้ใช้ไม่ได้เป็นช่างเทคนิค");
        }

        $this->db->beginTransaction();

        try {
            $this->ticketRepo->assignTechnician($ticketId, $technicianId, $priority);

            $this->logRepo->create([
                'ticket_id' => $ticketId,
                'changed_by' => $actor['id'],
                'from_status' => $ticket['status'],
                'to_status' => TicketStatus::ASSIGNED->value,
                'note' => $note ?: "มอบหมายงานให้ {$technician['name']}" . ($priority ? " (ปรับความสำคัญเป็น: " . strtoupper($priority) . ")" : ""),
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        $updatedTicket = $this->ticketRepo->find($ticketId);

        $this->events->dispatch('ticket.assigned', [
            'ticket' => $updatedTicket,
            'technician' => $technician,
            'actor' => $actor,
            'note' => $note,
        ]);
    }

    /**
     * Technician Logs Repair & Resolves Ticket
     */
    public function resolveTicketWithLog(int $ticketId, array $logData, array $actor): void
    {
        $ticket = $this->ticketRepo->find($ticketId);
        if (!$ticket) {
            throw new Exception("ไม่พบข้อมูล Ticket #{$ticketId}");
        }

        $this->db->beginTransaction();

        try {
            // 1. Save repair log (root cause, solution, actual hours, proof image)
            $this->repairLogRepo->save([
                'ticket_id' => $ticketId,
                'root_cause' => $logData['root_cause'],
                'solution_note' => $logData['solution_note'],
                'actual_hours' => (float)($logData['actual_hours'] ?? 1.0),
                'proof_image_path' => $logData['proof_image_path'] ?? null,
            ]);

            // 2. Update status to resolved
            $this->ticketRepo->updateStatus($ticketId, TicketStatus::RESOLVED->value, 'resolved_at');

            // 3. Log state change
            $this->logRepo->create([
                'ticket_id' => $ticketId,
                'changed_by' => $actor['id'],
                'from_status' => $ticket['status'],
                'to_status' => TicketStatus::RESOLVED->value,
                'note' => "ซ่อมเสร็จสิ้น: " . substr($logData['solution_note'], 0, 100),
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        $updatedTicket = $this->ticketRepo->find($ticketId);

        $this->events->dispatch('ticket.resolved', [
            'ticket' => $updatedTicket,
            'actor' => $actor,
        ]);
    }

    /**
     * Approve Spare Part Requisition (deducts stock)
     */
    public function approvePartRequest(int $ticketPartId, array $actor): void
    {
        $partUsed = $this->ticketPartsRepo->find($ticketPartId);
        if (!$partUsed) {
            throw new Exception("ไม่พบรายการขอเบิกอะไหล่");
        }

        if ($partUsed['status'] !== 'requested') {
            throw new Exception("รายการนี้ได้รับการพิจารณาไปแล้ว");
        }

        if ($partUsed['stock_quantity'] < $partUsed['quantity']) {
            throw new Exception("สต็อกคงเหลือไม่เพียงพอ (คงเหลือ {$partUsed['stock_quantity']} ชิ้น แต่ขอเบิก {$partUsed['quantity']} ชิ้น)");
        }

        $this->db->beginTransaction();
        try {
            $this->ticketPartsRepo->updateStatus($ticketPartId, 'approved');
            $this->sparePartRepo->decreaseStock((int)$partUsed['part_id'], (int)$partUsed['quantity']);
            
            $this->logRepo->create([
                'ticket_id' => $partUsed['ticket_id'],
                'changed_by' => $actor['id'],
                'from_status' => 'waiting_parts',
                'to_status' => 'waiting_parts',
                'note' => "Supervisor อนุมัติเบิกอะไหล่: {$partUsed['part_name']} จำนวน {$partUsed['quantity']} ชิ้น",
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Reject Spare Part Requisition
     */
    public function rejectPartRequest(int $ticketPartId, string $reason, array $actor): void
    {
        $partUsed = $this->ticketPartsRepo->find($ticketPartId);
        if (!$partUsed) {
            throw new Exception("ไม่พบรายการขอเบิกอะไหล่");
        }

        $this->db->beginTransaction();
        try {
            $this->ticketPartsRepo->updateStatus($ticketPartId, 'rejected');
            
            $this->logRepo->create([
                'ticket_id' => $partUsed['ticket_id'],
                'changed_by' => $actor['id'],
                'from_status' => 'waiting_parts',
                'to_status' => 'waiting_parts',
                'note' => "ไม่อนุมัติการเบิกอะไหล่ ({$partUsed['part_name']}): {$reason}",
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function validateStateTransition(?TicketStatus $from, TicketStatus $to): void
    {
        if ($from === null || $from === $to) {
            return;
        }

        if (!$from->canTransitionTo($to)) {
            throw new Exception("ไม่สามารถเปลี่ยนสถานะจาก '{$from->label()}' ไปเป็น '{$to->label()}' ได้ตามกฎ State Machine");
        }
    }

    private function checkPermission(array $actor, array $ticket, ?TicketStatus $from, TicketStatus $to): void
    {
        $role = $actor['role'];
        $actorId = (int)$actor['id'];

        // Admin / Supervisor has full authority
        if ($role === 'admin') {
            return;
        }

        // User rules
        if ($role === 'user') {
            if ((int)$ticket['user_id'] !== $actorId) {
                throw new Exception("คุณสามารถจัดการเฉพาะ Ticket ที่ตนเองเป็นผู้แจ้งเท่านั้น");
            }

            // User can cancel if not yet in repair
            if ($to === TicketStatus::CANCELLED && $from && $from->canCancel()) {
                return;
            }

            // User can confirm (closed) or reject (in_progress) when resolved
            if ($from === TicketStatus::RESOLVED && in_array($to, [TicketStatus::CLOSED, TicketStatus::IN_PROGRESS], true)) {
                return;
            }

            throw new Exception("ผู้ใช้งานทั่วไปไม่มีสิทธิ์เปลี่ยนเป็นสถานะนี้");
        }

        // Technician rules
        if ($role === 'technician') {
            if ($ticket['technician_id'] && (int)$ticket['technician_id'] !== $actorId) {
                throw new Exception("Ticket นี้ถูกมอบหมายให้ช่างเทคนิคท่านอื่นดูแลอยู่");
            }

            // Tech can change to: en_route, in_progress, waiting_parts, resolved
            if (in_array($to, [TicketStatus::EN_ROUTE, TicketStatus::IN_PROGRESS, TicketStatus::WAITING_PARTS, TicketStatus::RESOLVED], true)) {
                return;
            }

            throw new Exception("ช่างเทคนิคไม่มีสิทธิ์เปลี่ยนเป็นสถานะนี้");
        }

        throw new Exception("ไม่ได้รับอนุญาตให้ดำเนินการ");
    }
}
