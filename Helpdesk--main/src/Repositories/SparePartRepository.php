<?php

namespace App\Repositories;

use App\Core\Database;

class SparePartRepository implements RepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->query("SELECT * FROM spare_parts WHERE id = :id LIMIT 1", ['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->query("SELECT * FROM spare_parts WHERE part_code = :code LIMIT 1", ['code' => $code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM spare_parts ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function search(string $keyword): array
    {
        $sql = "SELECT * FROM spare_parts WHERE name LIKE :kw OR part_code LIKE :kw ORDER BY name ASC";
        $stmt = $this->db->query($sql, ['kw' => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO spare_parts (part_code, name, stock_quantity, unit_price, created_at) 
                VALUES (:part_code, :name, :stock_quantity, :unit_price, CURRENT_TIMESTAMP)";
        
        $this->db->query($sql, [
            'part_code' => $data['part_code'],
            'name' => $data['name'],
            'stock_quantity' => (int)($data['stock_quantity'] ?? 0),
            'unit_price' => (float)($data['unit_price'] ?? 0.0),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE spare_parts 
                SET part_code = :part_code, name = :name, stock_quantity = :stock_quantity, unit_price = :unit_price 
                WHERE id = :id";

        $this->db->query($sql, [
            'id' => $id,
            'part_code' => $data['part_code'],
            'name' => $data['name'],
            'stock_quantity' => (int)$data['stock_quantity'],
            'unit_price' => (float)$data['unit_price'],
        ]);

        return true;
    }

    public function decreaseStock(int $id, int $quantity): bool
    {
        $sql = "UPDATE spare_parts SET stock_quantity = GREATEST(0, stock_quantity - :qty) WHERE id = :id";
        $this->db->query($sql, ['id' => $id, 'qty' => $quantity]);
        return true;
    }

    public function increaseStock(int $id, int $quantity): bool
    {
        $sql = "UPDATE spare_parts SET stock_quantity = stock_quantity + :qty WHERE id = :id";
        $this->db->query($sql, ['id' => $id, 'qty' => $quantity]);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM spare_parts WHERE id = :id", ['id' => $id]);
        return true;
    }
}
