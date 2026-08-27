<?php
/**
 * Request — จัดการคำร้องขอเปิดหมู่เรียนพิเศษ
 *
 * Refactoring notes:
 * - แยก Logger ออกเป็น class ต่างหาก (SRP)
 * - เพิ่ม type hints ทุกเมธอด
 * - ใช้ named params แทน positional เพื่ออ่านง่าย
 * - ไม่ใช้ string interpolation ใน SQL (ป้องกัน injection)
 */
class Request
{
    private PDO    $conn;
    private Logger $logger;
    private const TABLE = 'special_course_requests';
    private const VIEW  = 'vw_special_course_requests';

    public function __construct()
    {
        $this->conn   = Database::getInstance()->getConnection();
        $this->logger = new Logger();
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * สร้างคำร้องใหม่
     *
     * @param  array<string, mixed> $data ข้อมูลจากฟอร์ม (ผ่าน cleanInput แล้ว)
     * @return array{success: bool, request_id?: int, request_number?: string, message: string}
     */
    public function create(array $data): array
    {
        try {
            $this->conn->beginTransaction();

            $requestNumber = $this->generateRequestNumber(
                (int)$data['semester'],
                (int)$data['academic_year']
            );

            $stmt = $this->conn->prepare(
                'INSERT INTO ' . self::TABLE . '
                     (request_number, student_id, course_code,
                      semester, academic_year, reason,
                      expected_students, is_in_regular_schedule, request_date)
                 VALUES
                     (:request_number, :student_id, :course_code,
                      :semester, :academic_year, :reason,
                      :expected_students, :is_in_regular_schedule, :request_date)'
            );

            $stmt->execute([
                ':request_number'         => $requestNumber,
                ':student_id'             => $data['student_id'],
                ':course_code'            => $data['course_code'],
                ':semester'               => (int)$data['semester'],
                ':academic_year'          => (int)$data['academic_year'],
                ':reason'                 => $data['reason'],
                ':expected_students'      => (int)($data['expected_students'] ?? 1),
                ':is_in_regular_schedule' => (int)($data['is_in_regular_schedule'] ?? 0),
                ':request_date'           => date('Y-m-d'),
            ]);

            $requestId = (int)$this->conn->lastInsertId();

            $this->logger->log($requestId, 'CREATE', 'สร้างคำร้องขอเปิดหมู่เรียนพิเศษ');

            $this->conn->commit();

            return [
                'success'        => true,
                'request_id'     => $requestId,
                'request_number' => $requestNumber,
                'message'        => 'ยื่นคำร้องสำเร็จ กรุณาบันทึกหมายเลขคำร้องเพื่อติดตามผล',
            ];

        } catch (\Exception $e) {
    $this->conn->rollBack();
    error_log('[Request::create] ' . $e->getMessage());
    return [
        'success' => false,
        // 🟢 เปลี่ยนบรรทัดนี้ให้โชว์ Error จริง
        'message' => 'Database Error: ' . $e->getMessage(),
    ];
}
    }

    /**
     * ติดตามสถานะคำร้องด้วยหมายเลขคำร้อง
     *
     * @return array<string, mixed>|false
     */
    public function trackByNumber(string $requestNumber): array|false
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM ' . self::VIEW . ' WHERE request_number = :rn LIMIT 1'
        );
        $stmt->execute([':rn' => $requestNumber]);
        return $stmt->fetch();
    }

    /**
     * รับรายการคำร้องทั้งหมด (Admin)
     *
     * @param  array<string, mixed> $filters ['status_id', 'semester', 'academic_year']
     * @return array<int, array<string, mixed>>
     */
    public function getAll(array $filters = []): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($filters['status_id'])) {
            $conditions[]          = 'status_id = :status_id';
            $params[':status_id']  = (int)$filters['status_id'];
        }
        if (!empty($filters['semester'])) {
            $conditions[]         = 'semester = :semester';
            $params[':semester']  = (int)$filters['semester'];
        }
        if (!empty($filters['academic_year'])) {
            $conditions[]              = 'academic_year = :academic_year';
            $params[':academic_year']  = (int)$filters['academic_year'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $this->conn->prepare(
            'SELECT * FROM ' . self::VIEW . " $where ORDER BY created_at DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * อนุมัติหรือปฏิเสธคำร้อง
     *
     * @param int         $requestId
     * @param int         $statusId      3 = APPROVED, 4 = REJECTED
     * @param string      $reviewNotes   หมายเหตุ
     * @param string      $reviewedBy    ชื่อผู้พิจารณา
     * @param int|null    $instructorId  กำหนดเมื่ออนุมัติ
     */
    public function updateStatus(
        int    $requestId,
        int    $statusId,
        string $reviewNotes,
        string $reviewedBy,
        ?int   $instructorId = null
    ): bool {
        try {
            $this->conn->beginTransaction();

            // Build query แบบ safe — field names ไม่มาจาก user input
            $extra  = '';
            $params = [
                ':request_id'   => $requestId,
                ':status_id'    => $statusId,
                ':reviewed_by'  => $reviewedBy,
                ':review_notes' => $reviewNotes,
            ];

            if ($statusId === 3 && $instructorId !== null) {   // APPROVED
                $extra                   = ', instructor_id = :instructor_id, approval_date = NOW()';
                $params[':instructor_id'] = $instructorId;
            }

            $stmt = $this->conn->prepare(
                'UPDATE ' . self::TABLE . "
                    SET status_id    = :status_id,
                        review_date  = NOW(),
                        reviewed_by  = :reviewed_by,
                        review_notes = :review_notes
                        $extra
                  WHERE request_id   = :request_id"
            );
            $stmt->execute($params);

            $statusLabel = $statusId === 3 ? 'อนุมัติ' : 'ไม่อนุมัติ';
            $this->logger->log(
                $requestId,
                'UPDATE_STATUS',
                "{$statusLabel}คำร้อง: {$reviewNotes}",
                $reviewedBy
            );

            $this->conn->commit();
            return true;

        } catch (\Exception $e) {
            $this->conn->rollBack();
            error_log('[Request::updateStatus] ' . $e->getMessage());
            return false;
        }
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /**
     * สร้างหมายเลขคำร้องอัตโนมัติ
     * Format: SCR-{ปี 2 หลัก}{เทอม}-{เลขลำดับ 4 หลัก}
     * ตัวอย่าง: SCR-671-0001
     */
    private function generateRequestNumber(int $semester, int $academicYear): string
    {
        $yearShort = substr((string)$academicYear, -2);
        $prefix    = "SCR-{$yearShort}{$semester}-";

        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE request_number LIKE :prefix'
        );
        $stmt->execute([':prefix' => $prefix . '%']);
        $count = (int)$stmt->fetchColumn();

        return $prefix . str_pad((string)($count + 1), 4, '0', STR_PAD_LEFT);
    }
}
