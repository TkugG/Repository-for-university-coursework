<?php

namespace App\Repositories;

use App\Core\Database;

class CommentRepository implements RepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM comments WHERE id = :id LIMIT 1", ['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM comments ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function findByTicket(int $ticketId): array
    {
        $sql = "
            SELECT 
                c.*,
                u.name as user_name,
                u.role as user_role,
                u.email as user_email
            FROM comments c
            JOIN users u ON u.id = c.user_id
            WHERE c.ticket_id = :ticket_id
            ORDER BY c.created_at ASC
        ";

        $stmt = $this->db->query($sql, ['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = "
            INSERT INTO comments (ticket_id, user_id, body, image_path, created_at)
            VALUES (:ticket_id, :user_id, :body, :image_path, CURRENT_TIMESTAMP)
        ";

        $this->db->query($sql, [
            'ticket_id' => $data['ticket_id'],
            'user_id' => $data['user_id'],
            'body' => $data['body'],
            'image_path' => $data['image_path'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("UPDATE comments SET body = :body WHERE id = :id", [
            'id' => $id,
            'body' => $data['body'],
        ]);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM comments WHERE id = :id", ['id' => $id]);
        return true;
    }
}
