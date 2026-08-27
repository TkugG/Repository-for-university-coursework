<?php
/**
 * หน้าหลัก — ระบบขอเปิดหมู่เรียนพิเศษ
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Logger.php';
require_once __DIR__ . '/classes/Request.php';
require_once __DIR__ . '/includes/functions.php';

checkSession();
$pageTitle = 'หน้าหลัก';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row g-4 justify-content-center">
    <div class="col-12 text-center mb-2">
        <h1 class="display-6 fw-bold">ระบบขอเปิดหมู่เรียนพิเศษ</h1>
        <p class="text-muted">ยื่นคำร้องและติดตามสถานะได้ที่นี่</p>
    </div>

    <div class="col-md-5">
        <div class="card h-100 text-center p-4">
            <div class="card-body">
                <i class="fas fa-paper-plane fa-3x text-primary mb-3"></i>
                <h4 class="card-title">ยื่นคำร้อง</h4>
                <p class="card-text text-muted">ขอเปิดหมู่เรียนพิเศษสำหรับรายวิชาที่ไม่มีในตารางปกติ</p>
                <a href="submit_request.php" class="btn btn-primary mt-2">
                    <i class="fas fa-plus me-1"></i>ยื่นคำร้องใหม่
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card h-100 text-center p-4">
            <div class="card-body">
                <i class="fas fa-search fa-3x text-success mb-3"></i>
                <h4 class="card-title">ติดตามสถานะ</h4>
                <p class="card-text text-muted">ตรวจสอบผลการพิจารณาคำร้องด้วยหมายเลขคำร้อง</p>
                <a href="track_request.php" class="btn btn-success mt-2">
                    <i class="fas fa-search me-1"></i>ติดตามคำร้อง
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
