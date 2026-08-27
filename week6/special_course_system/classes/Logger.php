<?php
/**
 * Logger — บันทึก Audit Trail ของคำร้องทุกการกระทำ
 *
 * แยกออกจาก Request เพื่อรองรับหลัก Single Responsibility
 */
class Logger
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * บันทึก log การดำเนินการ
     *
     * @param int    $requestId   ID คำร้อง
     * @param string $actionType  รหัสประเภทกิจกรรม เช่น 'CREATE', 'UPDATE_STATUS'
     * @param string $description คำอธิบายละเอียด
     * @param string|null $performedBy ผู้ดำเนินการ (ถ้าไม่ระบุ ดึงจาก Session)
     */
    public function log(
        int    $requestId,
        string $actionType,
        string $description,
        ?string $performedBy = null
    ): void {
        $performer = $performedBy
            ?? ($_SESSION['user_id'] ?? 'System');

        $ip = $this->resolveIp();

        $stmt = $this->conn->prepare(
            'INSERT INTO request_logs
                 (request_id, action_type, action_description, performed_by, ip_address, user_agent)
             VALUES
                 (:request_id, :action_type, :action_description, :performed_by, :ip_address, :user_agent)'
        );

        $stmt->execute([
            ':request_id'         => $requestId,
            ':action_type'        => $actionType,
            ':action_description' => $description,
            ':performed_by'       => $performer,
            ':ip_address'         => $ip,
            ':user_agent'         => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    /**
     * ดึง log ทั้งหมดของคำร้อง
     *
     * @return array<int, array<string, mixed>>
     */
    public function getByRequest(int $requestId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM request_logs
              WHERE request_id = :request_id
              ORDER BY performed_at DESC'
        );
        $stmt->execute([':request_id' => $requestId]);
        return $stmt->fetchAll();
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    private function resolveIp(): string
    {
        // รองรับ Proxy / Load Balancer
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                // X_FORWARDED_FOR อาจมีหลาย IP — เอาตัวแรก
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return 'unknown';
    }
}
