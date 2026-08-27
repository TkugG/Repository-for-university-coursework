<?php
// ==========================================
// 1. นำเข้าไฟล์เชื่อมต่อฐานข้อมูล และแถบเมนู
// ==========================================
require_once "db.php";
require_once "navbar.php";

// ==========================================
// 2. รับรหัสงานวิ่ง (id) จาก URL
// ==========================================
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 3. เพิ่มยอดวิวของงานนี้ (+1)
$conn->query("UPDATE events SET view_count = view_count + 1 WHERE id = $id");

// 4. ดึงข้อมูลงานวิ่งจากตาราง events
$stmt = $conn->query("SELECT * FROM events WHERE id = $id");
$event = $stmt->fetch();

// ถ้าไม่พบข้อมูลให้กลับหน้าแรก
if (!$event) {
    echo "<script>alert('ไม่พบข้อมูลงานวิ่งนี้'); window.location='index.php';</script>";
    exit;
}

// วันที่ปัจจุบันสำหรับตรวจสอบ
$now = date('Y-m-d H:i:s');
$today = date('Y-m-d');
$is_event_ended = ($today > $event['race_date']);
$is_reg_closed = ($now > $event['registration_end_date']) || $is_event_ended;

// 5. ดึงหมวดหมู่ระยะทางและราคาค่าสมัคร
$cat_stmt = $conn->query("SELECT * FROM event_categories WHERE event_id = $id ORDER BY price DESC");
$categories = $cat_stmt->fetchAll();

// ตรวจสอบว่าที่นั่งเต็มหมดแล้วหรือไม่
$total_max = 0;
$total_booked = 0;
foreach ($categories as $cat) {
    $total_max += $cat['max_slots'];
    $total_booked += $cat['booked_slots'];
}
$is_slots_full = ($total_max > 0 && $total_booked >= $total_max);
$can_register = !$is_reg_closed && !$is_slots_full;
?>

<!-- หน้ารายละเอียดงานวิ่ง (Event Detail) -->
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-6">
    
    <a href="index.php" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-brand-600 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> กลับไปหน้ารายการงานวิ่ง
    </a>

    <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-xl shadow-slate-200/50">
        
        <!-- รูปภาพงานวิ่ง Aspect Ratio 16:9 บนมือถือ และ 21:9 บนจอใหญ่ -->
        <div class="w-full aspect-[16/9] sm:aspect-[21/9] relative bg-slate-900 overflow-hidden">
            <img src="<?= htmlspecialchars($event['banner_image']) ?>" 
                 alt="<?= htmlspecialchars($event['title']) ?>" 
                 class="w-full h-full object-cover opacity-85">
            
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent flex flex-col justify-end p-6 sm:p-10 text-white">
                <div class="flex items-center gap-2">
                    <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-brand-600 shadow">
                        <?= htmlspecialchars($event['category_type']) ?>
                    </span>
                    <?php if ($is_event_ended): ?>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-200 border border-slate-600 shadow">
                            🏁 สิ้นสุดการแข่งขันแล้ว
                        </span>
                    <?php elseif ($is_reg_closed || $is_slots_full): ?>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-600 text-white shadow">
                            🔴 ปิดรับสมัครแล้ว
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow">
                            🟢 เปิดรับสมัคร
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black mt-2 leading-tight">
                    <?= htmlspecialchars($event['title']) ?>
                </h1>
                <p class="text-xs sm:text-sm text-slate-200 flex items-center gap-1.5 mt-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-lime-400"></i> <?= htmlspecialchars($event['location']) ?>
                </p>
            </div>
        </div>

        <div class="p-6 sm:p-10 space-y-8">
            
            <!-- สรุป 4 ข้อมูลสำคัญ -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200/70 text-xs sm:text-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center flex-shrink-0 font-bold">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px] font-medium">วันแข่งขัน</span>
                        <strong class="text-slate-800 text-sm font-bold"><?= $event['race_date'] ?></strong>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-lime-100 text-lime-800 flex items-center justify-center flex-shrink-0 font-bold">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px] font-medium">เวลาปล่อยตัว</span>
                        <strong class="text-slate-800 text-sm font-bold"><?= substr($event['race_time'], 0, 5) ?> น.</strong>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center flex-shrink-0 font-bold">
                        <i data-lucide="hourglass" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px] font-medium">ปิดรับสมัคร</span>
                        <strong class="text-slate-800 text-sm font-bold"><?= $event['registration_end_date'] ?></strong>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center flex-shrink-0 font-bold">
                        <i data-lucide="eye" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px] font-medium">ยอดผู้เข้าชม</span>
                        <strong class="text-amber-700 text-sm font-bold"><?= number_format($event['view_count']) ?> ครั้ง</strong>
                    </div>
                </div>
            </div>

            <!-- หมวดหมู่และราคาค่าสมัคร -->
            <div class="space-y-4">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i data-lucide="tag" class="w-5 h-5 text-brand-600"></i> หมวดหมู่ระยะทางและอัตราค่าสมัคร
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach ($categories as $cat): ?>
                        <div class="p-5 rounded-2xl border border-slate-200 bg-white hover:border-brand-300 transition-colors flex justify-between items-center shadow-sm">
                            <div class="space-y-1">
                                <h4 class="font-bold text-sm sm:text-base text-slate-900"><?= htmlspecialchars($cat['category_name']) ?></h4>
                                <span class="text-xs text-slate-500 block">โควตา: <?= $cat['booked_slots'] ?> / <?= $cat['max_slots'] ?> ที่นั่ง</span>
                            </div>
                            <div class="text-right">
                                <span class="text-lg sm:text-xl font-black text-brand-600">฿<?= number_format($cat['price'], 2) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ของรางวัลและสิทธิประโยชน์ -->
            <div class="space-y-4">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i data-lucide="award" class="w-5 h-5 text-amber-500"></i> ของรางวัลและสิทธิประโยชน์ที่ได้รับ
                </h3>
                <div class="bg-amber-50/60 p-6 rounded-3xl border border-amber-200/60 text-xs sm:text-sm text-slate-700 whitespace-pre-line leading-relaxed shadow-sm">
                    <?= htmlspecialchars($event['rewards_detail']) ?>
                </div>
            </div>

            <!-- ปุ่มกดไปหน้าสมัคร (ล็อกถ้าปิดรับสมัครหรือจบไปแล้ว) -->
            <div class="pt-4 flex flex-col sm:flex-row gap-3">
                <?php if ($can_register): ?>
                    <a href="register.php?event_id=<?= $event['id'] ?>" class="flex-1 text-center py-4 px-6 rounded-2xl font-bold text-base text-white bg-gradient-to-r from-brand-600 via-brand-500 to-lime-600 hover:from-brand-700 hover:to-brand-600 shadow-xl shadow-brand-500/25 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                        ลงทะเบียนสมัครวิ่งงานนี้
                    </a>
                <?php elseif ($is_event_ended): ?>
                    <a href="results.php?event_id=<?= $event['id'] ?>" class="flex-1 text-center py-4 px-6 rounded-2xl font-bold text-base text-white bg-amber-500 hover:bg-amber-600 shadow-lg transition-all flex items-center justify-center gap-2">
                        <i data-lucide="trophy" class="w-5 h-5"></i>
                        ดูสรุปผลการแข่งขันงานนี้
                    </a>
                <?php else: ?>
                    <div class="flex-1 text-center py-4 px-6 rounded-2xl font-bold text-base bg-slate-200 text-slate-500 border border-slate-300 cursor-not-allowed flex items-center justify-center gap-2">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                        ปิดรับสมัครแล้ว (สิ้นสุดระยะเวลารับสมัครหรือโควตาเต็ม)
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<?php require_once "footer.php"; ?>
