<?php
/**
 * Student — จัดการข้อมูลนักศึกษา
 */
class Student
{
    private PDO $conn;
    private const TABLE = 'students';

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * ค้นหานักศึกษาด้วยรหัสนักศึกษา
     *
     * @return array<string, mixed>|false
     */
    public function findById(string $studentId): array|false
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM ' . self::TABLE . ' WHERE student_id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $studentId]);
        return $stmt->fetch();
    }

    /**
     * ค้นหาด้วยอีเมล
     *
     * @return array<string, mixed>|false
     */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM ' . self::TABLE . ' WHERE student_email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

public function isEligibleForSpecialCourse(string $studentId): bool
{
    $cleanId = trim($studentId);
    if (empty($cleanId)) {
        return false;
    }

    // 1. ตรวจสอบข้อมูลจากฐานข้อมูลก่อน (ถ้ามีเรคคอร์ด)
    $student = $this->findById($cleanId);
    if ($student) {
        // นักศึกษาภาคค่ำ / เสาร์-อาทิตย์ ได้สิทธิ์ทันที
        $studentType = strtolower($student['student_type'] ?? '');
        if (in_array($studentType, ['evening', 'weekend'], true)) {
            return true;
        }

        // ถ้าระบุ study_year ใน DB ชัดเจนและ >= 4 ให้ผ่าน
        if (isset($student['study_year']) && (int)$student['study_year'] >= 4) {
            return true;
        }
    }

    // 2. คำนวณชั้นปีด่วนจากรหัสนักศึกษา (Fallback กรณีไม่มีใน DB หรือ DB ไม่ได้ลงปีไว้)
    // รองรับทั้งแบบ 66XXXXXX (2 หลักแรก) และ 2566XXXXXX (4 หลักแรก)
    if (str_starts_with($cleanId, '25') && strlen($cleanId) >= 4) {
        $entryYear2Digit = (int)substr($cleanId, 2, 2);
    } else {
        $entryYear2Digit = (int)substr($cleanId, 0, 2);
    }

    $currentYear2Digit = (int)substr((string)((int)date('Y') + 543), -2);

    if ($entryYear2Digit > 0 && $entryYear2Digit <= $currentYear2Digit) {
        $calculatedYear = ($currentYear2Digit - $entryYear2Digit) + 1;
        return $calculatedYear >= 4;
    }

    return false;
}
}
