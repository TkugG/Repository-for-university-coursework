<?php

namespace App\Repositories;

use App\Core\Database;

class StatusLogRepository implements RepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM status_logs WHERE id = :id LIMIT 1", ['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM status_logs ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function findByTicket(int $ticketId): array
    {
        $sql = "
            SELECT 
                l.*,
                u.name as changed_by_name,
                u.role as changed_by_role
            FROM status_logs l
            JOIN users u ON u.id = l.changed_by
            WHERE l.ticket_id = :ticket_id
            ORDER BY l.created_at ASC
        ";

        $stmt = $this->db->query($sql, ['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = "
            INSERT INTO status_logs (ticket_id, changed_by, from_status, to_status, note, created_at)
            VALUES (:ticket_id, :changed_by, :from_status, :to_status, :note, CURRENT_TIMESTAMP)
        ";

        $this->db->query($sql, [
            'ticket_id' => $data['ticket_id'],
            'changed_by' => $data['changed_by'],
            'from_status' => $data['from_status'],
            'to_status' => $data['to_status'],
            'note' => $data['note'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        return false; // Logs are append-only audit trail
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM status_logs WHERE id = :id", ['id' => $id]);
        return true;
    }
}
