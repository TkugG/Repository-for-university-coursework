<?php

namespace App\Repositories;

use App\Core\Database;

class CategoryRepository implements RepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM categories WHERE id = :id LIMIT 1", ['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT c.*, COUNT(t.id) as ticket_count 
            FROM categories c
            LEFT JOIN tickets t ON t.category_id = c.id
            GROUP BY c.id, c.name, c.description
            ORDER BY c.name ASC
        ");
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $this->db->query(
            "INSERT INTO categories (name, description) VALUES (:name, :description)",
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query(
            "UPDATE categories SET name = :name, description = :description WHERE id = :id",
            [
                'id' => $id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]
        );
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM categories WHERE id = :id", ['id' => $id]);
        return true;
    }
}
