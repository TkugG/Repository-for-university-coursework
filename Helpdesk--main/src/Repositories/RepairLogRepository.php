<?php

namespace App\Repositories;

use App\Core\Database;

class RepairLogRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByTicket(int $ticketId): ?array
    {
        $stmt = $this->db->query("SELECT * FROM ticket_repair_logs WHERE ticket_id = :ticket_id LIMIT 1", [
            'ticket_id' => $ticketId,
        ]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function save(array $data): bool
    {
        $existing = $this->findByTicket((int)$data['ticket_id']);

        if ($existing) {
            $sql = "UPDATE ticket_repair_logs 
                    SET root_cause = :root_cause, 
                        solution_note = :solution_note, 
                        actual_hours = :actual_hours" . 
                        (!empty($data['proof_image_path']) ? ", proof_image_path = :proof_image_path" : "") . 
                    " WHERE ticket_id = :ticket_id";
            
            $params = [
                'ticket_id' => $data['ticket_id'],
                'root_cause' => $data['root_cause'],
                'solution_note' => $data['solution_note'],
                'actual_hours' => (float)$data['actual_hours'],
            ];

            if (!empty($data['proof_image_path'])) {
                $params['proof_image_path'] = $data['proof_image_path'];
            }

            $this->db->query($sql, $params);
            return true;
        }

        $sql = "INSERT INTO ticket_repair_logs (ticket_id, root_cause, solution_note, actual_hours, proof_image_path, created_at)
                VALUES (:ticket_id, :root_cause, :solution_note, :actual_hours, :proof_image_path, CURRENT_TIMESTAMP)";

        $this->db->query($sql, [
            'ticket_id' => $data['ticket_id'],
            'root_cause' => $data['root_cause'],
            'solution_note' => $data['solution_note'],
            'actual_hours' => (float)$data['actual_hours'],
            'proof_image_path' => $data['proof_image_path'] ?? null,
        ]);

        return true;
    }
}
