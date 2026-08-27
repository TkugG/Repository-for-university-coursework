<?php
/**
 * Admin — Dashboard สรุปภาพรวมคำร้อง
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Logger.php';
require_once __DIR__ . '/../classes/Request.php';
require_once __DIR__ . '/../includes/functions.php';

checkSession();
// TODO: ตรวจสอบ Admin role ก่อน render
// if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') redirect('/index.php', 'ไม่มีสิทธิ์เข้าถึง', 'error');

$reqModel = new Request();

// สรุปจำนวนแต่ละสถานะ
$pdo = Database::getInstance()->getConnection();
$summary = $pdo->query(
    'SELECT rs.status_code, rs.status_name_th, rs.status_color, COUNT(scr.request_id) AS cnt
       FROM request_status rs
  LEFT JOIN special_course_requests scr ON scr.status_id = rs.status_id
      GROUP BY rs.status_id
      ORDER BY rs.sort_order'
)->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h4>

<div class="row g-3 mb-4">
<?php foreach ($summary as $row): ?>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-center border-0" style="border-left: 4px solid <?= htmlspecialchars($row['status_color']) ?> !important;">
            <div class="card-body">
                <div class="fs-2 fw-bold"><?= (int)$row['cnt'] ?></div>
                <div class="text-muted small"><?= htmlspecialchars($row['status_name_th']) ?></div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<a href="/admin/manage_requests.php" class="btn btn-primary">
    <i class="fas fa-list me-1"></i>จัดการคำร้องทั้งหมด
</a>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
