<?php
/**
 * หน้าฟอร์มยื่นคำร้องขอเปิดหมู่เรียนพิเศษ
 * HCI: Simplicity, Learnability, Feedback, Error Prevention
 */

// ─── Handle POST ──────────────────────────────────────────────────────────────
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Logger.php';
require_once __DIR__ . '/classes/Request.php';
require_once __DIR__ . '/classes/Course.php';
require_once __DIR__ . '/classes/Student.php';
require_once __DIR__ . '/includes/functions.php';

// เช็ก Session
checkSession();

$errors        = [];
$success       = false;
$requestNumber = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();   // abort 403 ถ้า token ไม่ตรง

    $data = [
        'student_id'             => cleanInput($_POST['student_id']         ?? ''),
        'course_code'            => cleanInput($_POST['course_code']         ?? ''),
        'semester'               => cleanInput($_POST['semester']            ?? ''),
        'academic_year'          => cleanInput($_POST['academic_year']       ?? '2569'),
        'reason'                 => cleanInput($_POST['reason']              ?? ''),
        'expected_students'      => max(1, (int)($_POST['expected_students'] ?? 1)),
        'is_in_regular_schedule' => isset($_POST['is_in_regular_schedule']) ? 1 : 0,
    ];

    // 1. ตรวจสอบข้อมูลฟอร์มเบื้องต้น
    $errors = validateRequestForm($data);

    // 2. คำนวณชั้นปีจริงจากรหัสนักศึกษา เทียบกับ ปีการศึกษาที่ยื่น
    $calculatedStudyYear = 1;
    if (empty($errors)) {
        $studentId   = $data['student_id'];
        $academicYr  = (int)$data['academic_year'];
        
        // ดึง 2 ตัวแรกของรหัสนักศึกษา (เช่น 66, 69)
        $entryYear2D = (int)substr($studentId, 0, 2);
        $academic2D  = (int)substr((string)$academicYr, -2);

        if ($entryYear2D > 0 && $academic2D >= $entryYear2D) {
            $calculatedStudyYear = ($academic2D - $entryYear2D) + 1;
        } else {
            $calculatedStudyYear = 1;
        }

        // 🔴 เช็กเงื่อนไข: ต้องเป็นชั้นปีที่ 4 ขึ้นไปเท่านั้น
        if ($calculatedStudyYear < 4) {
            $errors[] = "นักศึกษารหัส {$studentId} คำนวณแล้วอยู่ชั้นปีที่ {$calculatedStudyYear} (ระบบอนุญาตเฉพาะนักศึกษาชั้นปีที่ 4 ขึ้นไปเท่านั้น)";
        }
    }

    // 3. Auto-Provision: บันทึกนักศึกษาและรายวิชาลง DB โดยใช้ชั้นปีที่คำนวณจริง
    if (empty($errors)) {
        try {
            $db = Database::getInstance()->getConnection();

            // บันทึกนักศึกษาพร้อมชั้นปีจริงที่คำนวณได้
            $stmtStudent = $db->prepare("
                INSERT INTO students (student_id, student_prefix, student_firstname, student_lastname, student_email, faculty, study_year, student_type)
                VALUES (:id, 'นาย', 'นักศึกษา', 'ระบบ', :email, 'เทคโนโลยีสารสนเทศ', :study_year, 'regular')
                ON DUPLICATE KEY UPDATE study_year = :study_year_update
            ");
            $stmtStudent->execute([
                ':id'                => $data['student_id'],
                ':email'             => $data['student_id'] . '@student.mail.com',
                ':study_year'        => $calculatedStudyYear,
                ':study_year_update' => $calculatedStudyYear
            ]);

            // บันทึกวิชาให้อัตโนมัติ หากยังไม่มีใน DB
            $stmtCourse = $db->prepare("
                INSERT INTO courses (course_code, course_name_th, course_name_en, credit_theory, credit_practice, credit_self)
                VALUES (:code, CONCAT('วิชา ', :code_name), :code_en, 3.0, 0.0, 6.0)
                ON DUPLICATE KEY UPDATE course_code = course_code
            ");
            $stmtCourse->execute([
                ':code'      => $data['course_code'],
                ':code_name' => $data['course_code'],
                ':code_en'   => $data['course_code']
            ]);

        } catch (PDOException $e) {
            $errors[] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูลเบื้องต้น: ' . $e->getMessage();
        }
    }

    // 4. ตรวจสอบตารางเรียนปกติ
    if (empty($errors)) {
        $courseModel = new Course();
        $inRegular   = $courseModel->isInRegularSchedule(
            $data['student_id'],
            $data['course_code'],
            (int)$data['semester'],
            (int)$data['academic_year']
        );

        if ($inRegular && $data['is_in_regular_schedule'] == 0) {
            $errors[] = 'รายวิชานี้มีในตารางเรียนปกติของคุณแล้ว หากต้องการขอเปิดพิเศษกรุณาทำเครื่องหมายยินยอมในฟอร์ม';
        }
    }

    // 5. บันทึกคำร้องลง Database
    if (empty($errors)) {
        $request = new Request();
        $result  = $request->create($data);

        if ($result['success']) {
            $success       = true;
            $requestNumber = $result['request_number'];
        } else {
            $errors[] = $result['message'];
        }
    }
}

// ─── Defaults ─────────────────────────────────────────────────────────────────
$currentYear     = 2569; // กำหนดปีการศึกษาปัจจุบันเป็น 2569
$currentSemester = getCurrentSemester();

$pageTitle = 'ยื่นคำร้องขอเปิดหมู่เรียนพิเศษ';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-graduation-cap me-2"></i>ขอเปิดหมู่เรียนพิเศษ
                </h5>
            </div>
            <div class="card-body p-4">

                <?php if ($success): ?>
                <!-- ── Success ── -->
                <div class="text-center py-3">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h4>ยื่นคำร้องสำเร็จ!</h4>
                    <p class="text-muted mb-2">หมายเลขคำร้องของคุณคือ</p>
                    <div class="fs-4 fw-bold text-success border rounded-2 d-inline-block px-4 py-2 mb-3 bg-light">
                        <?= htmlspecialchars($requestNumber, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <p class="small text-muted">
                        กรุณาบันทึกหมายเลขนี้เพื่อติดตามผล<br>
                        ผลการพิจารณาจะประกาศภายใน 1–2 สัปดาห์
                    </p>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <a href="track_request.php?request_number=<?= urlencode($requestNumber) ?>" class="btn btn-success">
                            <i class="fas fa-search me-1"></i>ติดตามสถานะคำร้อง
                        </a>
                        <a href="submit_request.php" class="btn btn-outline-secondary">
                            <i class="fas fa-plus me-1"></i>ยื่นคำร้องใหม่
                        </a>
                    </div>
                </div>

                <?php else: ?>
                <!-- ── Form ── -->

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-1"></i>กรุณาแก้ไขข้อผิดพลาด:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="info-box mb-4">
                    <h6 class="mb-2"><i class="fas fa-info-circle me-1"></i>เงื่อนไขการขอเปิดหมู่เรียนพิเศษ</h6>
                    <ul class="mb-0 small">
                        <li>รายวิชาต้องไม่มีในตารางเรียนปกติของภาคเรียนนั้น</li>
                        <li>สำหรับนักศึกษาชั้นปีที่ 4 ขึ้นไป (รหัส 66 หรือเก่ากว่า สำหรับปีการศึกษา 2569)</li>
                        <li>ต้องระบุเหตุผลและความจำเป็นอย่างชัดเจน (ขั้นต่ำ 20 ตัวอักษร)</li>
                    </ul>
                </div>

                <form method="POST" action="" id="requestForm" class="needs-validation" novalidate>
                    <?= csrfField() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="student_id" class="form-label required">รหัสนักศึกษา</label>
                            <input type="text" class="form-control" id="student_id" name="student_id"
                                   placeholder="เช่น 660105555" required pattern="\d{8,13}"
                                   value="<?= htmlspecialchars($_POST['student_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="invalid-feedback">กรุณากรอกรหัสนักศึกษา 8–13 หลัก</div>
                        </div>

                        <div class="col-md-6">
                            <label for="course_code" class="form-label required">รหัสวิชา</label>
                            <input type="text" class="form-control" id="course_code" name="course_code"
                                   placeholder="เช่น CS100 หรือ CS101" required
                                   value="<?= htmlspecialchars($_POST['course_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="invalid-feedback">กรุณากรอกรหัสวิชา</div>
                        </div>

                        <div class="col-md-4">
                            <label for="semester" class="form-label required">ภาคเรียน</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="">เลือกภาคเรียน</option>
                                <?php foreach ([1 => 'ภาคเรียนที่ 1', 2 => 'ภาคเรียนที่ 2', 3 => 'ภาคฤดูร้อน'] as $v => $label): ?>
                                    <option value="<?= $v ?>"
                                        <?= (int)($_POST['semester'] ?? $currentSemester) === $v ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">กรุณาเลือกภาคเรียน</div>
                        </div>

                        <div class="col-md-4">
                            <label for="academic_year" class="form-label required">ปีการศึกษา (พ.ศ.)</label>
                            <input type="text" class="form-control" id="academic_year" name="academic_year"
                                   placeholder="เช่น 2569" required pattern="\d{4}"
                                   value="<?= htmlspecialchars($_POST['academic_year'] ?? $currentYear, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="invalid-feedback">กรุณากรอกปีการศึกษา 4 หลัก</div>
                        </div>

                        <div class="col-md-4">
                            <label for="expected_students" class="form-label">จำนวนนักศึกษาที่คาดว่าจะเรียน</label>
                            <input type="number" class="form-control" id="expected_students"
                                   name="expected_students" min="1" max="200"
                                   value="<?= (int)($_POST['expected_students'] ?? 1) ?>">
                        </div>

                        <div class="col-12">
                            <label for="reason" class="form-label required">
                                เหตุผลและความจำเป็น
                                (<span id="reason-counter">0</span> ตัวอักษร; ขั้นต่ำ 20)
                            </label>
                            <textarea class="form-control" id="reason" name="reason"
                                      rows="5" required minlength="20"
                                      placeholder="อธิบายเหตุผลที่ต้องการเปิดหมู่เรียนพิเศษ..."
                            ><?= htmlspecialchars($_POST['reason'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            <div class="invalid-feedback">กรุณาระบุเหตุผลอย่างน้อย 20 ตัวอักษร</div>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       id="is_in_regular_schedule" name="is_in_regular_schedule" value="1"
                                    <?= isset($_POST['is_in_regular_schedule']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_in_regular_schedule">
                                    วิชานี้มีในตารางปกติ แต่ไม่สามารถลงได้เนื่องจากเหตุผลพิเศษ
                                </label>
                            </div>
                        </div>

                        <div class="col-12 text-center pt-2">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-paper-plane me-1"></i>ยื่นคำร้อง
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-lg ms-2">
                                <i class="fas fa-redo me-1"></i>ล้างข้อมูล
                            </button>
                        </div>
                    </div>
                </form>
                <?php endif; ?>

            </div><!-- /.card-body -->
        </div><!-- /.card -->
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>