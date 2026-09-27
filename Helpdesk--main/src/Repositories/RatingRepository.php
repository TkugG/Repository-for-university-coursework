<?php

namespace App\Repositories;

use App\Core\Database;

class RatingRepository implements RepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM ratings WHERE id = :id LIMIT 1", ['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByTicket(int $ticketId): ?array
    {
        $stmt = $this->db->query("SELECT * FROM ratings WHERE ticket_id = :ticket_id LIMIT 1", ['ticket_id' => $ticketId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM ratings ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO ratings (ticket_id, score, feedback, created_at) 
                VALUES (:ticket_id, :score, :feedback, CURRENT_TIMESTAMP)";
        
        $this->db->query($sql, [
            'ticket_id' => $data['ticket_id'],
            'score' => (int) $data['score'],
            'feedback' => $data['feedback'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("UPDATE ratings SET score = :score, feedback = :feedback WHERE id = :id", [
            'id' => $id,
            'score' => (int)$data['score'],
            'feedback' => $data['feedback'] ?? null,
        ]);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM ratings WHERE id = :id", ['id' => $id]);
        return true;
    }

    public function getAverageScore(): float
    {
        $stmt = $this->db->query("SELECT AVG(score) as avg_score FROM ratings");
        $row = $stmt->fetch();
        return round((float)($row['avg_score'] ?? 0), 1);
    }
}
