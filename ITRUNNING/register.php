<?php
/**
 * ==========================================================
 * ไฟล์: register.php
 * คำอธิบาย: หน้าฟอร์มสมัครวิ่งและประมวลผลการลงทะเบียน
 * ==========================================================
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$now   = date('Y-m-d H:i:s');
$today = date('Y-m-d');

// ==========================================
// 1. ประมวลผลเมื่อส่งฟอร์ม (Method POST)
// ==========================================
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    
    
    // 1.1 รับค่าจากฟอร์ม
    $event_id    = (int)($_POST['event_id'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $full_name   = trim($_POST['full_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $shirt_size  = trim($_POST['shirt_size'] ?? 'L');

    // เก็บข้อมูลฟอร์มไว้ใน Session เพื่อไม่ต้องกรอกใหม่ถ้าเกิด Error
    $_SESSION['form_data'] = [
        'category_id' => $category_id,
        'full_name'   => $full_name,
        'email'       => $email,
        'phone'       => $phone,
        'shirt_size'  => $shirt_size
    ];

    // 1.2 ตรวจสอบวันปิดรับสมัครของงานนี้
    $chk_stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
    $chk_stmt->execute([':id' => $event_id]);
    $chk_event = $chk_stmt->fetch();

    if (!$chk_event || ($now > $chk_event['registration_end_date']) || ($today > $chk_event['race_date'])) {
        $_SESSION['error'] = "ขออภัย งานวิ่งนี้ปิดรับสมัครแล้ว หรือจบการแข่งขันไปแล้ว ไม่สามารถลงทะเบียนได้";
        header("Location: index.php");
        exit;
    }

    // 1.3 ตรวจสอบว่ากรอกข้อมูลครบถ้วนหรือไม่
    if ($event_id === 0 || $category_id === 0 || empty($full_name) || empty($email) || empty($phone)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง (เลือกระยะทาง, ชื่อ, อีเมล, เบอร์โทร และแนบรูปภาพ)";
        header("Location: register.php?event_id=$event_id");
        exit;
    }

    // 1.3.1 ตรวจสอบโควตาของหมวดหมู่ระยะทาง
    $chk_cat = $conn->prepare("SELECT * FROM event_categories WHERE id = :cat_id AND event_id = :event_id");
    $chk_cat->execute([':cat_id' => $category_id, ':event_id' => $event_id]);
    $cat_data = $chk_cat->fetch();

    if (!$cat_data) {
        $_SESSION['error'] = "หมวดหมู่ระยะทางที่เลือกไม่ถูกต้อง";
        header("Location: register.php?event_id=$event_id");
        exit;
    }

    if ($cat_data['max_slots'] > 0 && $cat_data['booked_slots'] >= $cat_data['max_slots']) {
        $_SESSION['error'] = "ขออภัย หมวดหมู่ " . $cat_data['category_name'] . " มีผู้สมัครเต็มจำนวนแล้ว";
        header("Location: register.php?event_id=$event_id");
        exit;
    }

    // 1.4 ตรวจสอบความซ้ำซ้อน: อีเมล หรือ เบอร์โทร ในงานเดียวกัน
    $check_stmt = $conn->prepare("
        SELECT id, booking_code, email, phone 
        FROM registrations 
        WHERE event_id = :event_id AND (email = :email OR phone = :phone) 
        LIMIT 1
    ");
    $check_stmt->execute([
        ':event_id' => $event_id,
        ':email'    => $email,
        ':phone'    => $phone
    ]);
    $existing = $check_stmt->fetch();

    if ($existing) {
        if ($existing['email'] === $email) {
            $_SESSION['error'] = "⚠️ ไม่สามารถสมัครซ้ำได้: อีเมล ($email) นี้ได้ลงทะเบียนในงานวิ่งนี้ไปแล้ว! (รหัสการจองเดิมของคุณคือ " . $existing['booking_code'] . ") หากต้องการดูบัตรให้ใช้รหัสนี้ค้นหา";
        } else {
            $_SESSION['error'] = "⚠️ ไม่สามารถสมัครซ้ำได้: เบอร์โทรศัพท์ ($phone) นี้ได้ลงทะเบียนในงานวิ่งนี้ไปแล้ว! (รหัสการจองเดิมคือ " . $existing['booking_code'] . ")";
        }
        header("Location: register.php?event_id=$event_id");
        exit;
    }

    // 1.5 ตรวจสอบและอัปโหลดไฟล์รูปภาพ 2 ไฟล์ (รูปนักวิ่ง + สลิปโอนเงิน)
    $upload_dir = ROOT_DIR . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!isset($_FILES['runner_photo']) || $_FILES['runner_photo']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "กรุณาแนบไฟล์รูปถ่ายหน้าตรงนักวิ่ง";
        header("Location: register.php?event_id=$event_id");
        exit;
    }

    if (!isset($_FILES['slip_image']) || $_FILES['slip_image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "กรุณาแนบไฟล์สลิปหลักฐานการโอนเงิน";
        header("Location: register.php?event_id=$event_id");
        exit;
    }

    // อัปโหลดรูปถ่ายนักวิ่ง
    $photo_ext = strtolower(pathinfo($_FILES['runner_photo']['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($photo_ext, $allowed_exts)) {
        $_SESSION['error'] = "รูปถ่ายนักวิ่งต้องเป็นไฟล์ภาพนามสกุล JPG, PNG หรือ WebP เท่านั้น";
        header("Location: register.php?event_id=$event_id");
        exit;
    }
    $runner_photo = "photo_" . time() . "_" . rand(100, 999) . "." . $photo_ext;
    move_uploaded_file($_FILES['runner_photo']['tmp_name'], $upload_dir . $runner_photo);

    // อัปโหลดสลิปโอนเงิน
    $slip_ext = strtolower(pathinfo($_FILES['slip_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($slip_ext, $allowed_exts)) {
        $_SESSION['error'] = "สลิปโอนเงินต้องเป็นไฟล์ภาพนามสกุล JPG, PNG หรือ WebP เท่านั้น";
        header("Location: register.php?event_id=$event_id");
        exit;
    }
    $slip_image = "slip_" . time() . "_" . rand(100, 999) . "." . $slip_ext;
    move_uploaded_file($_FILES['slip_image']['tmp_name'], $upload_dir . $slip_image);

    // 1.6 สุ่มสร้างรหัสการจอง (Booking Code) เช่น RUN-2026-A1B2
    $booking_code = "RUN-" . date('Y') . "-" . strtoupper(substr(md5(uniqid('', true)), 0, 5));

    // 1.7 บันทึกข้อมูลลงตาราง registrations
    try {
        $sql_insert = "INSERT INTO registrations (event_id, category_id, booking_code, full_name, email, phone, shirt_size, runner_photo, slip_image, payment_status)
                       VALUES (:event_id, :category_id, :booking_code, :full_name, :email, :phone, :shirt_size, :runner_photo, :slip_image, 'pending')";
        
        $stmt = $conn->prepare($sql_insert);
        $stmt->execute([
            ':event_id'     => $event_id,
            ':category_id'  => $category_id,
            ':booking_code' => $booking_code,
            ':full_name'    => $full_name,
            ':email'        => $email,
            ':phone'        => $phone,
            ':shirt_size'   => $shirt_size,
            ':runner_photo' => $runner_photo,
            ':slip_image'   => $slip_image
        ]);

        // เพิ่มจำนวนยอดจองของหมวดหมู่นี้ (+1)
        $update_slot = $conn->prepare("UPDATE event_categories SET booked_slots = booked_slots + 1 WHERE id = :cat_id");
        $update_slot->execute([':cat_id' => $category_id]);

        // ล้างข้อมูลฟอร์มใน Session เมื่อสมัครสำเร็จ
        unset($_SESSION['form_data']);

        $_SESSION['msg'] = "สมัครวิ่งสำเร็จ! รหัสการจองของคุณคือ $booking_code";
        header("Location: confirmation.php?booking_code=$booking_code");
        exit;

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
            $_SESSION['error'] = "⚠️ ข้อมูลนี้ (อีเมลหรือรหัสการจอง) ได้ทำการลงทะเบียนในงานวิ่งนี้ไปแล้ว กรุณาใช้อีเมลอื่น หรือตรวจสอบรหัสการจองของคุณ";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
        }
        header("Location: register.php?event_id=$event_id");
        exit;
    }
}

// ==========================================
// 2. แสดงผลหน้าฟอร์มสมัคร (Method GET)
// ==========================================
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$stmt = $conn->prepare("SELECT * FROM events WHERE id = :id");
$stmt->execute([':id' => $event_id]);
$event = $stmt->fetch();

if (!$event) {
    $_SESSION['error'] = "กรุณาเลือกงานวิ่งที่ต้องการสมัคร";
    header("Location: index.php");
    exit;
}

// ตรวจสอบวันปิดรับสมัครและวันแข่งขัน
if (($now > $event['registration_end_date']) || ($today > $event['race_date'])) {
    $_SESSION['error'] = "ขออภัย งานวิ่ง \"" . e($event['title']) . "\" ปิดรับสมัครแล้ว หรือจบการแข่งขันไปแล้ว ไม่สามารถลงทะเบียนได้";
    header("Location: index.php");
    exit;
}

// ดึงหมวดหมู่ระยะทางของงานนี้
$cat_stmt = $conn->prepare("SELECT * FROM event_categories WHERE event_id = :id ORDER BY price DESC");
$cat_stmt->execute([':id' => $event_id]);
$categories = $cat_stmt->fetchAll();

// ดึงข้อมูลเดิมที่เคยกรอกค้างไว้ (ถ้ามี)
$saved_form = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

$page_title = "ลงทะเบียนสมัคร - " . $event['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-2xl mx-auto px-4 py-8 sm:py-12">
    
    <!-- ปุ่มย้อนกลับ -->
    <a href="event-detail.php?id=<?= $event['id'] ?>" class="text-xs font-bold text-slate-500 hover:text-brand-600 mb-4 inline-flex items-center gap-1.5 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> กลับไปหน้ารายละเอียดงาน
    </a>

    <!-- Clean Card Container กลางหน้าจอ -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-xl shadow-slate-200/50 border border-slate-200 space-y-8">
        
        <!-- หัวข้อฟอร์ม -->
        <div class="border-b border-slate-100 pb-5 text-center sm:text-left">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-bold mb-2 border border-brand-200">
                <i data-lucide="ticket" class="w-3.5 h-3.5 text-lime-600"></i> Registration Form
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900">แบบฟอร์มลงทะเบียนสมัครวิ่ง</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1"><?= e($event['title']) ?></p>
            <div class="text-xs text-amber-800 bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-200 mt-3 inline-block font-medium">
                ⏳ ปิดรับสมัคร: <strong><?= thai_date($event['registration_end_date'], true) ?></strong>
            </div>
        </div>

        <form action="register.php" method="POST" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">

            <!-- ส่วนที่ 1: เลือกระยะทาง -->
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs font-bold flex items-center justify-center">1</span>
                    <label class="text-sm font-bold text-slate-800">เลือกระยะทางและราคาค่าสมัคร <span class="text-rose-500">*</span></label>
                </div>
                
                <div class="space-y-2.5">
                    <?php 
                        $first_available_found = false;
                        foreach ($categories as $index => $c): 
                            $remaining = max(0, $c['max_slots'] - $c['booked_slots']);
                            $is_full = ($c['max_slots'] > 0 && $remaining === 0);
                            
                            $is_checked = false;
                            if (isset($saved_form['category_id'])) {
                                $is_checked = ($saved_form['category_id'] == $c['id'] && !$is_full);
                            } elseif (!$first_available_found && !$is_full) {
                                $is_checked = true;
                                $first_available_found = true;
                            }
                    ?>
                        <label class="flex items-center justify-between p-4 rounded-2xl border-2 transition-all <?= $is_full ? 'border-slate-200 bg-slate-100 opacity-60 cursor-not-allowed' : 'border-slate-200 hover:border-brand-500 hover:bg-brand-50/30 cursor-pointer' ?>">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="category_id" value="<?= $c['id'] ?>" <?= $is_checked ? 'checked' : '' ?> <?= $is_full ? 'disabled' : '' ?> 
                                       class="w-4 h-4 text-brand-600 focus:ring-brand-500 disabled:opacity-40">
                                <div>
                                    <span class="text-sm font-bold text-slate-800 block"><?= e($c['category_name']) ?></span>
                                    <?php if ($is_full): ?>
                                        <span class="text-xs font-bold text-rose-500">❌ ที่นั่งเต็มแล้ว (0/<?= $c['max_slots'] ?>)</span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-500">คงเหลือ <strong><?= number_format($remaining) ?></strong> ที่นั่ง</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="text-base font-black <?= $is_full ? 'text-slate-400' : 'text-brand-600' ?>"><?= format_baht($c['price']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ส่วนที่ 2: ข้อมูลส่วนตัวผู้สมัคร -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs font-bold flex items-center justify-center">2</span>
                    <label class="text-sm font-bold text-slate-800">ข้อมูลส่วนตัวผู้สมัคร</label>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ชื่อ-นามสกุล <span class="text-rose-500">*</span></label>
                    <input type="text" name="full_name" required value="<?= e($saved_form['full_name'] ?? '') ?>" placeholder="เช่น นายสมชาย รักการวิ่ง" 
                           class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">อีเมล (สำหรับรับ E-Ticket) <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required value="<?= e($saved_form['email'] ?? '') ?>" placeholder="runner@example.com" 
                               class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">เบอร์โทรศัพท์ <span class="text-rose-500">*</span></label>
                        <input type="tel" name="phone" required value="<?= e($saved_form['phone'] ?? '') ?>" placeholder="08XXXXXXXX" 
                               class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                    </div>
                </div>

                <!-- เลือกไซส์เสื้อ -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">ไซส์เสื้อวิ่งที่ระลึก <span class="text-rose-500">*</span></label>
                    <?php $cur_size = $saved_form['shirt_size'] ?? 'L'; ?>
                    <select name="shirt_size" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        <option value="S" <?= $cur_size === 'S' ? 'selected' : '' ?>>Size S (รอบอก 36 นิ้ว / ยาว 26 นิ้ว)</option>
                        <option value="M" <?= $cur_size === 'M' ? 'selected' : '' ?>>Size M (รอบอก 38 นิ้ว / ยาว 27 นิ้ว)</option>
                        <option value="L" <?= $cur_size === 'L' ? 'selected' : '' ?>>Size L (รอบอก 40 นิ้ว / ยาว 28 นิ้ว)</option>
                        <option value="XL" <?= $cur_size === 'XL' ? 'selected' : '' ?>>Size XL (รอบอก 42 นิ้ว / ยาว 29 นิ้ว)</option>
                        <option value="2XL" <?= $cur_size === '2XL' ? 'selected' : '' ?>>Size 2XL (รอบอก 44 นิ้ว / ยาว 30 นิ้ว)</option>
                        <option value="3XL" <?= $cur_size === '3XL' ? 'selected' : '' ?>>Size 3XL (รอบอก 46 นิ้ว / ยาว 31 นิ้ว)</option>
                    </select>
                </div>
            </div>

            <!-- ส่วนที่ 3: อัปโหลดรูปภาพและหลักฐาน พร้อม LIVE PREVIEW -->
            <div class="space-y-5 pt-4 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs font-bold flex items-center justify-center">3</span>
                    <label class="text-sm font-bold text-slate-800">อัปโหลดรูปภาพและหลักฐานการโอนเงิน</label>
                </div>
                
                <!-- อัปโหลดรูปถ่ายหน้าตรงนักวิ่ง -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <label class="block text-xs font-bold text-slate-700">
                        📷 รูปถ่ายหน้าตรงนักวิ่ง (สำหรับพิมพ์บัตร BIB) <span class="text-rose-500">*</span>
                    </label>
                    
                    <input type="file" id="runner_photo_input" name="runner_photo" accept="image/*" required 
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
                    
                    <!-- กล่องแสดงตัวอย่างรูปภาพนักวิ่ง (Live Preview) -->
                    <div id="runner_photo_preview_box" class="hidden items-center gap-3 pt-2">
                        <div class="w-20 h-20 rounded-xl overflow-hidden bg-white border-2 border-brand-500 shadow-sm flex-shrink-0">
                            <img id="runner_photo_preview" src="" class="w-full h-full object-cover">
                        </div>
                        <span class="text-xs text-emerald-700 font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i> เลือกรูปนักวิ่งเรียบร้อย
                        </span>
                    </div>
                </div>

                <!-- อัปโหลดสลิปโอนเงิน -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="flex justify-between items-center">
                        <label class="block text-xs font-bold text-slate-700">
                            🧾 สลิปหลักฐานการโอนเงิน <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-md border border-brand-200">PromptPay: 088-123-4567</span>
                    </div>
                    
                    <input type="file" id="slip_input" name="slip_image" accept="image/*" required 
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
                    
                    <!-- กล่องแสดงตัวอย่างสลิป (Live Preview) -->
                    <div id="slip_preview_box" class="hidden items-center gap-3 pt-2">
                        <div class="w-20 h-24 rounded-xl overflow-hidden bg-white border-2 border-brand-500 shadow-sm flex-shrink-0">
                            <img id="slip_preview" src="" class="w-full h-full object-cover">
                        </div>
                        <span class="text-xs text-emerald-700 font-semibold flex items-center gap-1">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i> เลือกรูปสลิปเรียบร้อย
                        </span>
                    </div>
                </div>
            </div>

            <!-- ปุ่มบันทึกการสมัคร -->
            <div class="pt-4">
                <button type="submit" class="w-full py-4 px-6 rounded-2xl font-bold text-base text-white bg-gradient-to-r from-brand-600 via-brand-500 to-lime-600 hover:from-brand-700 hover:to-brand-600 shadow-xl shadow-brand-500/25 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    ยืนยันการสมัครและส่งหลักฐาน
                </button>
            </div>

        </form>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
