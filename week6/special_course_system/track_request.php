<?php
/**
 * หน้าติดตามสถานะคำร้อง
 * HCI: Visibility, Feedback
 *
 * Refactoring:
 * - แก้ typo "นักศึกษ:" → "นักศึกษา:"
 * - ใช้ header/footer include แทน inline HTML
 * - ใช้ __DIR__ ใน require_once
 * - ปรับ timeline logic ให้รองรับ REJECTED (ไม่แสดง step 3 เป็น completed)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Logger.php';
require_once __DIR__ . '/classes/Request.php';
require_once __DIR__ . '/includes/functions.php';

checkSession();

$requestData = null;
$error       = '';

if (!empty($_GET['request_number'])) {
    $requestNumber = cleanInput($_GET['request_number']);
    $reqModel      = new Request();
    $requestData   = $reqModel->trackByNumber($requestNumber);

    if (!$requestData) {
        $error = 'ไม่พบคำร้องหมายเลข ' . htmlspecialchars($requestNumber, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * คำนวณ CSS class ของ timeline step
 * step_threshold = status_id ที่ถือว่า step นี้ "ผ่านแล้ว"
 */
function timelineClass(int $currentStatus, int $stepThreshold, bool $isRejected = false): string
{
    if ($isRejected && $stepThreshold >= 3) {
        return '';   // ถ้า rejected ไม่แสดง step ที่ 3 เป็น active/completed
    }
    if ($currentStatus > $stepThreshold) {
        return 'step-completed';
    }
    if ($currentStatus === $stepThreshold) {
        return 'step-active';
    }
    return '';
}

$pageTitle = 'ติดตามสถานะคำร้อง';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-search me-2"></i>ติดตามสถานะคำร้อง
                </h5>
            </div>
            <div class="card-body p-4">

                <!-- ── Search form ── -->
                <form method="GET" action="" class="row g-2 mb-4">
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="request_number"
                               placeholder="หมายเลขคำร้อง เช่น SCR-671-0001"
                               value="<?= htmlspecialchars($_GET['request_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               required>
                    </div>
                    <div class="col-sm-4">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-search me-1"></i>ค้นหา
                        </button>
                    </div>
                </form>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-1"></i><?= $error ?>
                    </div>

                <?php elseif ($requestData): ?>
                <?php
                    $statusId  = (int)$requestData['status_id'];
                    $isRejected = $requestData['status_code'] === 'REJECTED';
                ?>

                <!-- ── Alert: request number ── -->
                <div class="alert alert-info mb-4">
                    <i class="fas fa-ticket-alt me-1"></i>
                    หมายเลขคำร้อง: <strong><?= htmlspecialchars($requestData['request_number'], ENT_QUOTES, 'UTF-8') ?></strong>
                    &nbsp;
                    <?= getStatusBadge($requestData['status_code'], $requestData['status_name_th']) ?>
                </div>

                <!-- ── Timeline ── -->
                <div class="timeline mb-4">
                    <div class="timeline-item <?= timelineClass($statusId, 1) ?>">
                        <div class="timeline-icon"><i class="fas fa-paper-plane"></i></div>
                        <div>
                            <h6 class="mb-0">ยื่นคำร้อง</h6>
                            <small class="text-muted"><?= formatDateThai($requestData['created_at']) ?></small>
                        </div>
                    </div>

                    <div class="timeline-item <?= timelineClass($statusId, 2) ?>">
                        <div class="timeline-icon"><i class="fas fa-tasks"></i></div>
                        <div>
                            <h6 class="mb-0">กำลังตรวจสอบ</h6>
                            <small class="text-muted">
                                <?= $requestData['review_date']
                                    ? 'เมื่อ ' . formatDateThai($requestData['review_date'])
                                    : 'รอสายวิชาพิจารณา (1–2 สัปดาห์)' ?>
                            </small>
                        </div>
                    </div>

                    <div class="timeline-item <?= timelineClass($statusId, 3, $isRejected) ?>">
                        <div class="timeline-icon">
                            <i class="fas <?= $isRejected ? 'fa-times-circle' : 'fa-check-circle' ?>"></i>
                        </div>
                        <div>
                            <?php if ($isRejected): ?>
                                <h6 class="mb-0 text-danger">ไม่อนุมัติ</h6>
                                <small class="text-muted">
                                    <?= $requestData['review_notes']
                                        ? 'หมายเหตุ: ' . htmlspecialchars($requestData['review_notes'], ENT_QUOTES, 'UTF-8')
                                        : '' ?>
                                </small>
                            <?php else: ?>
                                <h6 class="mb-0">อนุมัติ</h6>
                                <small class="text-muted">
                                    <?= $requestData['approval_date']
                                        ? 'อนุมัติเมื่อ ' . formatDateThai($requestData['approval_date'])
                                        : 'รอการอนุมัติจากสายวิชา' ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ── Detail table ── -->
                <table class="table table-borderless table-sm">
                    <tbody>
                        <tr>
                            <th class="text-muted w-40">นักศึกษา</th>
                            <td><?= htmlspecialchars($requestData['student_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">รายวิชา</th>
                            <td>
                                <?= htmlspecialchars($requestData['course_code'], ENT_QUOTES, 'UTF-8') ?>
                                <?= htmlspecialchars($requestData['course_name_th'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">ภาคเรียน / ปีการศึกษา</th>
                            <td>
                                <?= (int)$requestData['semester'] ?> /
                                <?= (int)$requestData['academic_year'] ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">เหตุผล</th>
                            <td><?= nl2br(htmlspecialchars($requestData['reason'], ENT_QUOTES, 'UTF-8')) ?></td>
                        </tr>
                        <?php if (!empty($requestData['review_notes'])): ?>
                        <tr>
                            <th class="text-muted">หมายเหตุจากสายวิชา</th>
                            <td><?= nl2br(htmlspecialchars($requestData['review_notes'], ENT_QUOTES, 'UTF-8')) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($requestData['instructor_name'])): ?>
                        <tr>
                            <th class="text-muted">อาจารย์ผู้สอน</th>
                            <td><?= htmlspecialchars($requestData['instructor_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="d-flex gap-2 mt-3">
                    <a href="track_request.php" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-search me-1"></i>ค้นหาใหม่
                    </a>
                    <a href="submit_request.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>ยื่นคำร้องใหม่
                    </a>
                </div>

                <?php endif; ?>

            </div><!-- /.card-body -->
        </div><!-- /.card -->
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
