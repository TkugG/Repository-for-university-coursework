<?php
/**
 * Helper Functions
 * หลัก HCI: Consistency, Feedback, Error Prevention
 */

// ─── Input / Security ────────────────────────────────────────────────────────

/**
 * ทำความสะอาดข้อมูล Input (ป้องกัน XSS)
 */
function cleanInput(string $data): string
{
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * ตรวจสอบรูปแบบอีเมล
 */
function validateEmail(string $email): bool
{
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ─── CSRF Protection ─────────────────────────────────────────────────────────

/**
 * สร้าง CSRF token และเก็บใน Session
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * ตรวจสอบ CSRF token ที่ส่งมากับ POST
 * คืน false และ abort ถ้า token ไม่ตรง
 */
function verifyCsrfToken(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('คำขอไม่ถูกต้อง (CSRF token ไม่ตรงกัน)');
    }
    // Regenerate token หลัง verify สำเร็จ (token rotation)
    unset($_SESSION['csrf_token']);
    return true;
}

/**
 * สร้าง hidden input สำหรับฝังใน form
 */
function csrfField(): string
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// ─── Session ─────────────────────────────────────────────────────────────────

/**
 * เริ่ม Session ถ้ายังไม่ได้เริ่ม
 */
function checkSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Redirect พร้อมข้อความ Flash
 */
function redirect(string $url, ?string $message = null, string $type = 'info'): never
{
    if ($message !== null) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type']    = $type;
    }
    header("Location: $url");
    exit();
}

// ─── UI / Display ─────────────────────────────────────────────────────────────

/**
 * แสดง Bootstrap Alert
 *
 * @param string $message ข้อความ (ผ่าน htmlspecialchars แล้ว)
 * @param string $type    success | error | warning | info
 */
function showMessage(string $message, string $type = 'info'): string
{
    $map = [
        'success' => ['alert-success', '✓'],
        'error'   => ['alert-danger',  '✗'],
        'warning' => ['alert-warning', '⚠'],
        'info'    => ['alert-info',    'ℹ'],
    ];

    [$alertClass, $icon] = $map[$type] ?? $map['info'];

    return <<<HTML
    <div class="alert {$alertClass} alert-dismissible fade show" role="alert">
        <strong>{$icon}</strong> {$message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
    </div>
    HTML;
}

/**
 * แสดง Flash message จาก Session แล้วลบออก
 */
function showFlash(): string
{
    if (empty($_SESSION['flash_message'])) {
        return '';
    }
    $html = showMessage($_SESSION['flash_message'], $_SESSION['flash_type'] ?? 'info');
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    return $html;
}

/**
 * แปลงสถานะเป็น Bootstrap Badge
 */
function getStatusBadge(string $statusCode, string $statusName): string
{
    $classMap = [
        'PENDING'   => 'bg-warning text-dark',
        'REVIEWING' => 'bg-info text-dark',
        'APPROVED'  => 'bg-success',
        'REJECTED'  => 'bg-danger',
        'CANCELLED' => 'bg-secondary',
    ];

    $class = $classMap[$statusCode] ?? 'bg-secondary';
    $name  = htmlspecialchars($statusName, ENT_QUOTES, 'UTF-8');

    return "<span class=\"badge {$class}\">{$name}</span>";
}

// ─── Date / Time ─────────────────────────────────────────────────────────────

/**
 * แปลงวันที่เป็นรูปแบบภาษาไทย (วัน เดือน พ.ศ.)
 *
 * Bug fix: เขียน array เดือนแบบชัดเจนทั้ง 12 key เพื่อป้องกัน
 * ความสับสนจาก sparse array ของต้นฉบับ
 */
function formatDateThai(string $date): string
{
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }

    $months = [
        1  => 'มกราคม',
        2  => 'กุมภาพันธ์',
        3  => 'มีนาคม',
        4  => 'เมษายน',
        5  => 'พฤษภาคม',
        6  => 'มิถุนายน',
        7  => 'กรกฎาคม',
        8  => 'สิงหาคม',
        9  => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม',
    ];

    $ts    = strtotime($date);
    $day   = (int)date('j', $ts);
    $month = $months[(int)date('n', $ts)];
    $year  = (int)date('Y', $ts) + 543;

    return "{$day} {$month} {$year}";
}

/**
 * คำนวณภาคการศึกษาปัจจุบัน (ตามปฏิทินมหาวิทยาลัยไทย)
 * เดือน 6–10 = ภาค 1 | เดือน 11–3 = ภาค 2 | เดือน 4–5 = ภาคฤดูร้อน (3)
 */
function getCurrentSemester(): int
{
    $month = (int)date('n');
    if ($month >= 6 && $month <= 10) {
        return 1;
    }
    if ($month >= 11 || $month <= 3) {
        return 2;
    }
    return 3;   // เมษายน–พฤษภาคม
}

// ─── Validation ──────────────────────────────────────────────────────────────

/**
 * Validate ข้อมูลฟอร์มคำร้อง
 *
 * @param  array<string, mixed> $data
 * @return string[]  รายการข้อผิดพลาด (array ว่าง = ผ่าน)
 */
function validateRequestForm(array $data): array
{
    $errors = [];

    if (empty($data['student_id'])) {
        $errors[] = 'กรุณากรอกรหัสนักศึกษา';
    } elseif (!preg_match('/^\d{8,13}$/', $data['student_id'])) {
        $errors[] = 'รหัสนักศึกษาต้องเป็นตัวเลข 8–13 หลัก';
    }

    if (empty($data['course_code'])) {
        $errors[] = 'กรุณากรอกรหัสวิชา';
    }

    if (empty($data['semester']) || !in_array((int)$data['semester'], [1, 2, 3], true)) {
        $errors[] = 'กรุณาเลือกภาคเรียนที่ถูกต้อง (1, 2, หรือ 3)';
    }

    if (empty($data['academic_year']) || !preg_match('/^\d{4}$/', $data['academic_year'])) {
        $errors[] = 'กรุณากรอกปีการศึกษา 4 หลัก (เช่น 2567)';
    }

    if (empty($data['reason'])) {
        $errors[] = 'กรุณาระบุเหตุผลและความจำเป็น';
    } elseif (mb_strlen($data['reason']) < 20) {
        $errors[] = 'เหตุผลควรมีความยาวอย่างน้อย 20 ตัวอักษร';
    }

    return $errors;
}
