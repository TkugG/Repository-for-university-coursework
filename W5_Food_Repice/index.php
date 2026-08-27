<?php
require_once 'db.php';

// จัดการการลบข้อมูล (Delete)
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM foods WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: index.php");
    exit;
}

// ดึงข้อมูลอาหารพร้อมวัตถุดิบมาแสดงผล (Read)
$stmt = $pdo->query("SELECT * FROM foods ORDER BY id DESC");
$foods = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบจัดการข้อมูลอาหารและสูตรอาหาร</title>
    <!-- ใช้ Bootstrap 5 เพื่อความสวยงาม สบายตา ตามหลัก HCI ด้าน Aesthetic -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-secondary">📋 รายการเมนูอาหารทั้งหมด</h2>
        <a href="manage.php" class="btn btn-primary">+ เพิ่มเมนูอาหารใหม่</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
    <thead class="table-dark">
        <tr>
            <th style="width: 20%">ชื่ออาหาร (ไทย)</th>
            <th style="width: 15%">รูปภาพ</th>
            <th style="width: 15%">หมวดหมู่</th>
            <th style="width: 35%">วัตถุดิบและส่วนผสม (Recipe)</th>
            <th style="width: 15%" class="text-center">จัดการ</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($foods)): ?>
            <tr>
                <td colspan="5" class="text-center text-muted py-4">ยังไม่มีข้อมูลอาหารในระบบ</td>
            </tr>
        <?php else: ?>
            <?php foreach ($foods as $food): 
                $stmtRecipe = $pdo->prepare("SELECT * FROM recipes WHERE food_id = ?");
                $stmtRecipe->execute([$food['id']]);
                $recipes = $stmtRecipe->fetchAll();
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($food['name_th']) ?></strong></td>
                    
                    <!-- เพิ่มช่อง <td> สำหรับแสดงรูปภาพตรงนี้ -->
                    <td>
                        <?php if (!empty($food['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($food['image']) ?>" width="80" class="img-thumbnail">
                        <?php else: ?>
                            <span class="text-muted small">ไม่มีรูป</span>
                        <?php endif; ?>
                    </td>

                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($food['category']) ?></span></td>
                    <td>
                        <?php if (!empty($recipes)): ?>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($recipes as $r): ?>
                                    <li><?= htmlspecialchars($r['recipe_name']) ?> <?= $r['quantity'] ?> <?= htmlspecialchars($r['unit_name']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <span class="text-muted small">ไม่มีข้อมูลวัตถุดิบ</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="manage.php?id=<?= $food['id'] ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                        <a href="index.php?delete_id=<?= $food['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบเมนูนี้? ข้อมูลวัตถุดิบทั้งหมดจะถูกลบไปด้วย');">ลบ</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
        </div>
    </div>
</div>
</body>
</html>
