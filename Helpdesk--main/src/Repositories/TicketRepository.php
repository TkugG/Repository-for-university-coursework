<?php

namespace App\Repositories;

use App\Core\Database;
use App\Enums\TicketPriority;
use PDO;

class TicketRepository implements RepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $sql = "
            SELECT 
                t.*,
                u.name as user_name,
                u.email as user_email,
                c.name as category_name,
                tech.name as technician_name,
                tech.email as technician_email,
                r.score as rating_score,
                r.feedback as rating_feedback,
                r.created_at as rating_created_at,
                rl.root_cause,
                rl.solution_note,
                rl.actual_hours,
                rl.proof_image_path,
                CASE 
                    WHEN t.status NOT IN ('resolved', 'closed', 'cancelled') AND t.sla_due_at IS NOT NULL AND t.sla_due_at < NOW() THEN 1 
                    ELSE 0 
                END as is_overdue
            FROM tickets t
            JOIN users u ON u.id = t.user_id
            JOIN categories c ON c.id = t.category_id
            LEFT JOIN users tech ON tech.id = t.technician_id
            LEFT JOIN ratings r ON r.ticket_id = t.id
            LEFT JOIN ticket_repair_logs rl ON rl.ticket_id = t.id
            WHERE t.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->query($sql, ['id' => $id]);
        $ticket = $stmt->fetch();
        return $ticket ?: null;
    }

    public function all(): array
    {
        return $this->search([]);
    }

    public function findByUser(int $userId): array
    {
        return $this->search(['user_id' => $userId]);
    }

    public function findByTechnician(int $techId): array
    {
        $sql = "
            SELECT 
                t.*,
                u.name as user_name,
                u.email as user_email,
                c.name as category_name,
                tech.name as technician_name,
                r.score as rating_score,
                CASE 
                    WHEN t.status NOT IN ('resolved', 'closed', 'cancelled') AND t.sla_due_at IS NOT NULL AND t.sla_due_at < NOW() THEN 1 
                    ELSE 0 
                END as is_overdue
            FROM tickets t
            JOIN users u ON u.id = t.user_id
            JOIN categories c ON c.id = t.category_id
            LEFT JOIN users tech ON tech.id = t.technician_id
            LEFT JOIN ratings r ON r.ticket_id = t.id
            WHERE t.technician_id = :tech_id
            ORDER BY 
                is_overdue DESC,
                CASE t.priority 
                    WHEN 'urgent' THEN 1 
                    WHEN 'high' THEN 2 
                    WHEN 'normal' THEN 3 
                    WHEN 'low' THEN 4 
                    ELSE 5 
                END ASC,
                t.created_at DESC
        ";

        $stmt = $this->db->query($sql, ['tech_id' => $techId]);
        return $stmt->fetchAll();
    }

    public function search(array $filters = []): array
    {
        $sql = "
            SELECT 
                t.*,
                u.name as user_name,
                u.email as user_email,
                c.name as category_name,
                tech.name as technician_name,
                tech.email as technician_email,
                r.score as rating_score,
                CASE 
                    WHEN t.status NOT IN ('resolved', 'closed', 'cancelled') AND t.sla_due_at IS NOT NULL AND t.sla_due_at < NOW() THEN 1 
                    ELSE 0 
                END as is_overdue
            FROM tickets t
            JOIN users u ON u.id = t.user_id
            JOIN categories c ON c.id = t.category_id
            LEFT JOIN users tech ON tech.id = t.technician_id
            LEFT JOIN ratings r ON r.ticket_id = t.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND t.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['technician_id'])) {
            if ($filters['technician_id'] === 'unassigned') {
                $sql .= " AND t.technician_id IS NULL";
            } else {
                $sql .= " AND t.technician_id = :technician_id";
                $params['technician_id'] = $filters['technician_id'];
            }
        }

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND t.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['sla_filter'])) {
            if ($filters['sla_filter'] === 'overdue') {
                $sql .= " AND t.status NOT IN ('resolved', 'closed', 'cancelled') AND t.sla_due_at IS NOT NULL AND t.sla_due_at < :now_overdue";
                $params['now_overdue'] = date('Y-m-d H:i:s');
            } elseif ($filters['sla_filter'] === 'near_due') {
                $sql .= " AND t.status NOT IN ('resolved', 'closed', 'cancelled') AND t.sla_due_at IS NOT NULL AND t.sla_due_at >= :now_near AND t.sla_due_at <= :near_limit";
                $params['now_near'] = date('Y-m-d H:i:s');
                $params['near_limit'] = date('Y-m-d H:i:s', strtotime('+4 hours'));
            }
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND (t.title LIKE :kw OR t.description LIKE :kw OR t.location LIKE :kw OR u.name LIKE :kw)";
            $params['kw'] = '%' . $filters['keyword'] . '%';
        }

        $sql .= " ORDER BY 
            CASE 
                WHEN t.priority = 'urgent' THEN 1 
                WHEN t.priority = 'high' THEN 2 
                WHEN t.priority = 'normal' THEN 3 
                ELSE 4 
            END ASC,
            t.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $priority = $data['priority'] ?? 'normal';
        $priorityEnum = TicketPriority::tryFrom($priority) ?? TicketPriority::NORMAL;
        $slaHours = $priorityEnum->slaHours();
        $slaDueAt = date('Y-m-d H:i:s', strtotime("+{$slaHours} hours"));
        $now = date('Y-m-d H:i:s');

        $sql = "
            INSERT INTO tickets (user_id, category_id, technician_id, title, location, description, status, priority, sla_due_at, created_at, updated_at)
            VALUES (:user_id, :category_id, :technician_id, :title, :location, :description, :status, :priority, :sla_due_at, :created_at, :updated_at)
        ";

        $this->db->query($sql, [
            'user_id' => $data['user_id'],
            'category_id' => $data['category_id'],
            'technician_id' => $data['technician_id'] ?? null,
            'title' => $data['title'],
            'location' => $data['location'] ?? 'อาคารหลัก',
            'description' => $data['description'],
            'status' => $data['status'] ?? 'pending_approval',
            'priority' => $priority,
            'sla_due_at' => $slaDueAt,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach ($data as $key => $val) {
            $fields[] = "{$key} = :{$key}";
            $params[$key] = $val;
        }
        $fields[] = "updated_at = CURRENT_TIMESTAMP";

        $sql = "UPDATE tickets SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->query($sql, $params);
        return true;
    }

    public function updateStatus(int $id, string $status, ?string $timeField = null): bool
    {
        $sql = "UPDATE tickets SET status = :status, updated_at = CURRENT_TIMESTAMP";
        $params = ['id' => $id, 'status' => $status];

        if ($timeField === 'resolved_at') {
            $sql .= ", resolved_at = CURRENT_TIMESTAMP";
        } elseif ($timeField === 'closed_at') {
            $sql .= ", closed_at = CURRENT_TIMESTAMP";
        }

        $sql .= " WHERE id = :id";
        $this->db->query($sql, $params);
        return true;
    }

    public function assignTechnician(int $id, int $technicianId, ?string $priority = null): bool
    {
        $priorityUpdate = "";
        $params = ['id' => $id, 'tech_id' => $technicianId];

        if ($priority) {
            $priorityEnum = TicketPriority::tryFrom($priority) ?? TicketPriority::NORMAL;
            $slaHours = $priorityEnum->slaHours();
            $priorityUpdate = ", priority = :priority, sla_due_at = :sla_due_at";
            $params['priority'] = $priority;
            $params['sla_due_at'] = date('Y-m-d H:i:s', strtotime("+{$slaHours} hours"));
        }

        $sql = "UPDATE tickets SET technician_id = :tech_id, status = 'assigned', updated_at = CURRENT_TIMESTAMP {$priorityUpdate} WHERE id = :id";
        $this->db->query($sql, $params);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM tickets WHERE id = :id", ['id' => $id]);
        return true;
    }

    public function getStatusCounts(?int $userId = null, ?int $techId = null): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM tickets WHERE 1=1";
        $params = [];

        if ($userId !== null) {
            $sql .= " AND user_id = :uid";
            $params['uid'] = $userId;
        }
        if ($techId !== null) {
            $sql .= " AND technician_id = :tid";
            $params['tid'] = $techId;
        }

        $sql .= " GROUP BY status";
        $rows = $this->db->query($sql, $params)->fetchAll();

        $counts = [
            'total' => 0,
            'pending_approval' => 0,
            'approved' => 0,
            'assigned' => 0,
            'en_route' => 0,
            'in_progress' => 0,
            'waiting_parts' => 0,
            'resolved' => 0,
            'closed' => 0,
            'cancelled' => 0,
            'overdue' => 0,
        ];

        foreach ($rows as $r) {
            $st = $r['status'];
            $c = (int)$r['count'];
            $counts[$st] = $c;
            $counts['total'] += $c;
        }

        // Count Overdue
        $overdueSql = "SELECT COUNT(*) as count FROM tickets 
                       WHERE status NOT IN ('resolved', 'closed', 'cancelled') 
                       AND sla_due_at IS NOT NULL AND sla_due_at < NOW()";
        if ($userId !== null) {
            $overdueSql .= " AND user_id = " . (int)$userId;
        }
        if ($techId !== null) {
            $overdueSql .= " AND technician_id = " . (int)$techId;
        }
        $overdueRow = $this->db->query($overdueSql)->fetch();
        $counts['overdue'] = (int)($overdueRow['count'] ?? 0);

        return $counts;
    }

    public function countByStatus(?int $userId = null, ?int $techId = null): array
    {
        return $this->getStatusCounts($userId, $techId);
    }

    /**
     * SLA Compliance Rate % (Resolved before or on SLA due date)
     */
    public function getSlaComplianceRate(): float
    {
        $sql = "
            SELECT 
                COUNT(*) as total_resolved,
                SUM(CASE WHEN resolved_at <= sla_due_at THEN 1 ELSE 0 END) as on_time_count
            FROM tickets
            WHERE resolved_at IS NOT NULL AND sla_due_at IS NOT NULL
        ";
        $row = $this->db->query($sql)->fetch();
        $total = (int)($row['total_resolved'] ?? 0);
        if ($total === 0) {
            return 100.0;
        }
        $onTime = (int)($row['on_time_count'] ?? 0);
        return round(($onTime / $total) * 100, 1);
    }

    public function getAverageResolutionTimeHours(): float
    {
        $sql = "
            SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at) / 60.0) as avg_hours
            FROM tickets
            WHERE resolved_at IS NOT NULL
        ";
        $row = $this->db->query($sql)->fetch();
        return round((float)($row['avg_hours'] ?? 0), 1);
    }

    public function getCategoryDistribution(): array
    {
        $sql = "
            SELECT c.name, COUNT(t.id) as ticket_count
            FROM categories c
            LEFT JOIN tickets t ON t.category_id = c.id
            GROUP BY c.id, c.name
            ORDER BY ticket_count DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getTechnicianPerformance(): array
    {
        $sql = "
            SELECT 
                u.id,
                u.name,
                COUNT(t.id) as total_assigned,
                SUM(CASE WHEN t.status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN t.status = 'closed' THEN 1 ELSE 0 END) as closed_count,
                SUM(CASE WHEN t.status IN ('in_progress', 'assigned', 'en_route', 'waiting_parts') THEN 1 ELSE 0 END) as active_count,
                ROUND(AVG(r.score), 1) as avg_rating
            FROM users u
            LEFT JOIN tickets t ON t.technician_id = u.id
            LEFT JOIN ratings r ON r.ticket_id = t.id
            WHERE u.role = 'technician'
            GROUP BY u.id, u.name
            ORDER BY closed_count DESC, avg_rating DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }
}
