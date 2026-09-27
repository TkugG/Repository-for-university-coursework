<?php

namespace App\Repositories;

use App\Core\Database;

class TicketPartsRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT tp.*, sp.name as part_name, sp.part_code, sp.unit_price, sp.stock_quantity 
                FROM ticket_parts_used tp
                JOIN spare_parts sp ON sp.id = tp.part_id
                WHERE tp.id = :id LIMIT 1";
        $stmt = $this->db->query($sql, ['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByTicket(int $ticketId): array
    {
        $sql = "
            SELECT 
                tp.*,
                sp.part_code,
                sp.name as part_name,
                sp.unit_price,
                (tp.quantity * sp.unit_price) as total_price,
                sp.stock_quantity
            FROM ticket_parts_used tp
            JOIN spare_parts sp ON sp.id = tp.part_id
            WHERE tp.ticket_id = :ticket_id
            ORDER BY tp.created_at ASC
        ";

        $stmt = $this->db->query($sql, ['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    public function getPendingRequests(): array
    {
        $sql = "
            SELECT 
                tp.*,
                t.title as ticket_title,
                t.location as ticket_location,
                u.name as technician_name,
                sp.part_code,
                sp.name as part_name,
                sp.stock_quantity,
                sp.unit_price,
                (tp.quantity * sp.unit_price) as total_price
            FROM ticket_parts_used tp
            JOIN tickets t ON t.id = tp.ticket_id
            LEFT JOIN users u ON u.id = t.technician_id
            JOIN spare_parts sp ON sp.id = tp.part_id
            WHERE tp.status = 'requested'
            ORDER BY tp.created_at DESC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    public function requestPart(int $ticketId, int $partId, int $quantity): int
    {
        $sql = "INSERT INTO ticket_parts_used (ticket_id, part_id, quantity, status, created_at) 
                VALUES (:ticket_id, :part_id, :quantity, 'requested', CURRENT_TIMESTAMP)";
        
        $this->db->query($sql, [
            'ticket_id' => $ticketId,
            'part_id' => $partId,
            'quantity' => $quantity,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $sql = "UPDATE ticket_parts_used SET status = :status WHERE id = :id";
        $this->db->query($sql, ['id' => $id, 'status' => $status]);
        return true;
    }
}
