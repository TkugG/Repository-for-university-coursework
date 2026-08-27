<?php
/**
 * Admin — รายการคำร้องทั้งหมด พร้อม filter
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Logger.php';
require_once __DIR__ . '/../classes/Request.php';
require_once __DIR__ . '/../includes/functions.php';

checkSession();

$filters = [
    'status_id'    => !empty($_GET['status_id'])    ? (int)$_GET['status_id']    : null,
    'semester'     => !empty($_GET['semester'])      ? (int)$_GET['semester']     : null,
    'academic_year'=> !empty($_GET['academic_year']) ? (int)$_GET['academic_year']: null,
];

// ลบ key ที่เป็น null ออก
$filters = array_filter($filters);

$reqModel = new Request();
$requests = $reqModel->getAll($filters);

$pageTitle = 'จัดการคำร้อง';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-list me-2"></i>คำร้องทั้งหมด</h4>
    <a href="/admin/dashboard.php" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>กลับ Dashboard
    </a>
</div>

<!-- Filter form -->
<form method="GET" class="row g-2 mb-4">
    <div class="col-sm-3">
        <select name="status_id" class="form-select form-select-sm">
            <option value="">ทุกสถานะ</option>
            <option value="1" <?= ($filters['status_id'] ?? '') == 1 ? 'selected' : '' ?>>รอดำเนินการ</option>
            <option value="2" <?= ($filters['status_id'] ?? '') == 2 ? 'selected' : '' ?>>กำลังตรวจสอบ</option>
            <option value="3" <?= ($filters['status_id'] ?? '') == 3 ? 'selected' : '' ?>>อนุมัติ</option>
            <option value="4" <?= ($filters['status_id'] ?? '') == 4 ? 'selected' : '' ?>>ไม่อนุมัติ</option>
        </select>
    </div>
    <div class="col-sm-2">
        <select name="semester" class="form-select form-select-sm">
            <option value="">ทุกภาค</option>
            <option value="1" <?= ($filters['semester'] ?? '') == 1 ? 'selected' : '' ?>>1</option>
            <option value="2" <?= ($filters['semester'] ?? '') == 2 ? 'selected' : '' ?>>2</option>
            <option value="3" <?= ($filters['semester'] ?? '') == 3 ? 'selected' : '' ?>>3</option>
        </select>
    </div>
    <div class="col-sm-2">
        <input type="text" name="academic_year" class="form-control form-control-sm"
               placeholder="ปีการศึกษา เช่น 2567"
               value="<?= htmlspecialchars($_GET['academic_year'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="col-sm-2">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-filter me-1"></i>กรอง
        </button>
        <a href="/admin/manage_requests.php" class="btn btn-outline-secondary btn-sm ms-1">ล้าง</a>
    </div>
</form>

<!-- Table -->
<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead class="table-light">
            <tr>
                <th>หมายเลขคำร้อง</th>
                <th>นักศึกษา</th>
                <th>รายวิชา</th>
                <th>ภาค/ปี</th>
                <th>วันที่ยื่น</th>
                <th>สถานะ</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($requests)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">ไม่พบคำร้อง</td></tr>
        <?php else: ?>
            <?php foreach ($requests as $r): ?>
            <tr>
                <td class="fw-mono small"><?= htmlspecialchars($r['request_number']) ?></td>
                <td><?= htmlspecialchars($r['student_name']) ?></td>
                <td><?= htmlspecialchars($r['course_code']) ?></td>
                <td><?= (int)$r['semester'] ?> / <?= (int)$r['academic_year'] ?></td>
                <td><?= formatDateThai($r['request_date']) ?></td>
                <td><?= getStatusBadge($r['status_code'], $r['status_name_th']) ?></td>
                <td>
                    <a href="/admin/approve_request.php?id=<?= (int)$r['request_id'] ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
