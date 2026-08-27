<?php
/**
 * Admin — อนุมัติ / ปฏิเสธคำร้อง
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Logger.php';
require_once __DIR__ . '/../classes/Request.php';
require_once __DIR__ . '/../includes/functions.php';

checkSession();

$requestId = (int)($_GET['id'] ?? 0);
if ($requestId <= 0) {
    redirect('/admin/manage_requests.php', 'ไม่พบคำร้อง', 'error');
}

$pdo    = Database::getInstance()->getConnection();
$stmt   = $pdo->prepare('SELECT * FROM vw_special_course_requests WHERE request_id = :id LIMIT 1');
$stmt->execute([':id' => $requestId]);
$req    = $stmt->fetch();

if (!$req) {
    redirect('/admin/manage_requests.php', 'ไม่พบคำร้องหมายเลขนี้', 'error');
}

// ─── Handle POST (approve / reject) ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $statusId     = (int)($_POST['status_id']     ?? 0);
    $reviewNotes  = cleanInput($_POST['review_notes'] ?? '');
    $instructorId = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
    $reviewedBy   = $_SESSION['user_name'] ?? 'Admin';

    if (!in_array($statusId, [3, 4], true)) {
        redirect("/admin/approve_request.php?id={$requestId}", 'กรุณาเลือกผลการพิจารณา', 'warning');
    }

    $reqModel = new Request();
    $ok = $reqModel->updateStatus($requestId, $statusId, $reviewNotes, $reviewedBy, $instructorId);

    if ($ok) {
        redirect('/admin/manage_requests.php', 'บันทึกผลการพิจารณาเรียบร้อย', 'success');
    } else {
        redirect("/admin/approve_request.php?id={$requestId}", 'เกิดข้อผิดพลาด กรุณาลองใหม่', 'error');
    }
}

// ─── Load instructors for dropdown ───────────────────────────────────────────
$instructors = $pdo->query(
    "SELECT instructor_id,
            CONCAT(instructor_prefix, ' ', instructor_firstname, ' ', instructor_lastname) AS full_name
       FROM instructors WHERE status = 'active' ORDER BY instructor_firstname"
)->fetchAll();

$pageTitle = 'พิจารณาคำร้อง';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-gavel me-2"></i>พิจารณาคำร้อง</h5>
            </div>
            <div class="card-body p-4">

                <!-- Request summary -->
                <table class="table table-sm table-borderless mb-4">
                    <tbody>
                        <tr><th class="text-muted w-40">หมายเลขคำร้อง</th>
                            <td class="fw-bold"><?= htmlspecialchars($req['request_number']) ?></td></tr>
                        <tr><th class="text-muted">นักศึกษา</th>
                            <td><?= htmlspecialchars($req['student_name']) ?></td></tr>
                        <tr><th class="text-muted">รายวิชา</th>
                            <td><?= htmlspecialchars($req['course_code'] . ' ' . $req['course_name_th']) ?></td></tr>
                        <tr><th class="text-muted">ภาค/ปี</th>
                            <td><?= (int)$req['semester'] ?> / <?= (int)$req['academic_year'] ?></td></tr>
                        <tr><th class="text-muted">เหตุผล</th>
                            <td><?= nl2br(htmlspecialchars($req['reason'])) ?></td></tr>
                    </tbody>
                </table>

                <form method="POST" action="">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label required">ผลการพิจารณา</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_id"
                                       id="approve" value="3" required>
                                <label class="form-check-label text-success fw-bold" for="approve">
                                    <i class="fas fa-check-circle me-1"></i>อนุมัติ
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status_id"
                                       id="reject" value="4">
                                <label class="form-check-label text-danger fw-bold" for="reject">
                                    <i class="fas fa-times-circle me-1"></i>ไม่อนุมัติ
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="instructor-field">
                        <label for="instructor_id" class="form-label">อาจารย์ผู้สอน (กรณีอนุมัติ)</label>
                        <select name="instructor_id" id="instructor_id" class="form-select">
                            <option value="">— เลือกอาจารย์ผู้สอน —</option>
                            <?php foreach ($instructors as $ins): ?>
                                <option value="<?= (int)$ins['instructor_id'] ?>">
                                    <?= htmlspecialchars($ins['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="review_notes" class="form-label">หมายเหตุ / เหตุผลประกอบ</label>
                        <textarea class="form-control" id="review_notes" name="review_notes"
                                  rows="3" placeholder="ระบุหมายเหตุหรือเหตุผลที่ไม่อนุมัติ..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>บันทึกผลการพิจารณา
                        </button>
                        <a href="/admin/manage_requests.php" class="btn btn-outline-secondary">ยกเลิก</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// แสดง/ซ่อน instructor field ตาม radio
document.querySelectorAll('input[name="status_id"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('instructor-field').style.display =
            this.value === '3' ? 'block' : 'none';
    });
});
document.getElementById('instructor-field').style.display = 'none';
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
