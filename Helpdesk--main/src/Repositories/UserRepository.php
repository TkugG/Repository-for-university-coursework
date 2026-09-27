<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class UserRepository implements RepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users WHERE id = :id LIMIT 1", ['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE email = :email LIMIT 1", ['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public function findByRole(string $role): array
    {
        $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users WHERE role = :role ORDER BY name ASC", ['role' => $role]);
        return $stmt->fetchAll();
    }

    public function getTechnicians(): array
    {
        return $this->findByRole('technician');
    }

    public function getAdmins(): array
    {
        return $this->findByRole('admin');
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO users (name, email, password_hash, role, created_at) 
                VALUES (:name, :email, :password_hash, :role, CURRENT_TIMESTAMP)";
        
        $this->db->query($sql, [
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => $data['role'] ?? 'user',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params['name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params['email'] = $data['email'];
        }
        if (isset($data['role'])) {
            $fields[] = "role = :role";
            $params['role'] = $data['role'];
        }
        if (!empty($data['password'])) {
            $fields[] = "password_hash = :password_hash";
            $params['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->query($sql, $params);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM users WHERE id = :id", ['id' => $id]);
        return true;
    }
}
