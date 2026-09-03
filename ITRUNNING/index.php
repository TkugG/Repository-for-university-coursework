<?php
/**
 * ==========================================================
 * ไฟล์: index.php
 * คำอธิบาย: หน้ารายการงานวิ่งทั้งหมด พร้อมระบบค้นหาและตัวกรอง
 * ==========================================================
 */

require_once __DIR__ . '/config/db.php';

// รับค่าค้นหาและตัวกรองจากแบบฟอร์ม
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// วันที่และเวลาปัจจุบันสำหรับเปรียบเทียบ
$now   = date('Y-m-d H:i:s');
$today = date('Y-m-d');

// สร้างคำสั่ง SQL และ Binding Parameters
$sql = "SELECT e.*, 
               COALESCE(SUM(c.max_slots), 0) AS total_max, 
               COALESCE(SUM(c.booked_slots), 0) AS total_booked,
               MIN(c.price) AS min_price
        FROM events e
        LEFT JOIN event_categories c ON e.id = c.event_id
        WHERE 1=1";

$params = [];

if ($search !== '') {
    $sql .= " AND (e.title LIKE :search1 OR e.location LIKE :search2)";
    $params[':search1'] = "%$search%";
    $params[':search2'] = "%$search%";
}

if ($category !== '') {
    $sql .= " AND (e.category_type LIKE :cat1 OR e.categories LIKE :cat2)";
    $params[':cat1'] = "%$category%";
    $params[':cat2'] = "%$category%";
}

$sql .= " GROUP BY e.id ORDER BY e.race_date ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

$page_title = "หน้าหลัก - งานวิ่งและเทรลธรรมชาติ 2026";
require_once __DIR__ . '/includes/header.php';
?>

<!-- ส่วนแบนเนอร์ด้านบน (Hero Section) -->
<div class="bg-gradient-to-b from-brand-50/70 via-brand-50/30 to-[#F8FAFC] py-12 sm:py-16 border-b border-slate-200/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-500/10 text-brand-700 text-xs sm:text-sm font-bold border border-brand-200 shadow-sm">
            <i data-lucide="trees" class="w-4 h-4 text-lime-600"></i> Nature & Trail Running Platform 2026
        </div>
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-tight">
            เปิดประตูสู่งานวิ่งและ <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-lime-600">เทรลธรรมชาติ</span>
        </h1>
        <p class="text-slate-600 text-sm sm:text-base max-w-2xl mx-auto font-normal leading-relaxed">
            ค้นหาและลงทะเบียนสมัครงานวิ่งทั่วประเทศไทย อัปเดตตารางแข่งขัน รับ E-Ticket สะดวก รวดเร็ว พร้อมตรวจสอบผลการแข่งขันได้ทันใจ
        </p>
    </div>
</div>

<!-- ส่วนค้นหาและฟิลเตอร์งานวิ่ง -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 sm:-mt-8 relative z-20">
    <div class="bg-white p-4 sm:p-6 rounded-3xl shadow-lg shadow-slate-200/60 border border-slate-200">
        <form method="GET" action="index.php" class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-center">
            
            <!-- ช่องค้นหาชื่อหรือสถานที่ -->
            <div class="sm:col-span-6 relative">
                <input type="text" name="search" value="<?= e($search) ?>" 
                       placeholder="พิมพ์ชื่องานวิ่ง หรือ จังหวัด/สถานที่ เช่น เขาใหญ่, บางแสน..." 
                       class="w-full pl-10 pr-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>
            </div>

            <!-- ตัวกรองประเภทระยะทาง -->
            <div class="sm:col-span-4">
                <select name="category" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                    <option value="">-- ทุกประเภทระยะทาง --</option>
                    <option value="Trail" <?= $category == 'Trail' ? 'selected' : '' ?>>🟣 Trail Challenge (เทรล)</option>
                    <option value="Full Marathon" <?= $category == 'Full Marathon' ? 'selected' : '' ?>>🔴 Full Marathon (42K)</option>
                    <option value="Half Marathon" <?= $category == 'Half Marathon' ? 'selected' : '' ?>>🟠 Half Marathon (21K)</option>
                    <option value="Mini Marathon" <?= $category == 'Mini Marathon' ? 'selected' : '' ?>>🔵 Mini Marathon (10K)</option>
                    <option value="Fun Run" <?= $category == 'Fun Run' ? 'selected' : '' ?>>🟢 Fun Run (3K-5K)</option>
                </select>
            </div>

            <!-- ปุ่มกดค้นหา -->
            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-2xl font-bold text-sm shadow-md shadow-brand-500/20 transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-4 h-4"></i> ค้นหา
                </button>
                <?php if ($search !== '' || $category !== ''): ?>
                    <a href="index.php" class="p-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl transition-colors" title="ล้างตัวกรอง">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </div>
</div>

<!-- ส่วนแสดงการ์ดรายการงานวิ่ง -->
<div id="events-list" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-8">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="flag" class="w-6 h-6 text-brand-600"></i> รายการงานวิ่งทั้งหมด
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">พบงานวิ่งทั้งหมด <?= count($events) ?> รายการ</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="results.php" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-4 py-2 rounded-xl transition-colors">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-500"></i> ดูผลการแข่งขัน
            </a>
            <a href="calendar.php" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 px-4 py-2 rounded-xl transition-colors">
                <i data-lucide="calendar" class="w-4 h-4 text-brand-600"></i> ปฏิทิน
            </a>
        </div>
    </div>

    <?php if (count($events) == 0): ?>
        <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-slate-300 p-8 shadow-sm">
            <div class="w-14 h-14 bg-brand-50 text-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i data-lucide="search-x" class="w-7 h-7"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">ไม่พบรายการงานวิ่งที่ค้นหา</h3>
            <p class="text-xs text-slate-500 mt-1 mb-4">ลองค้นหาด้วยคำสำคัญอื่น หรือกดดูงานวิ่งทั้งหมด</p>
            <a href="index.php" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 transition-colors">
                <i data-lucide="rotate-cw" class="w-3.5 h-3.5"></i> แสดงงานวิ่งทั้งหมด
            </a>
        </div>
    <?php else: ?>
        <!-- วนลูปแสดงการ์ดงานวิ่ง -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($events as $row): ?>
                <?php
                    // ตรวจสอบสถานะวันรับสมัครและวันแข่งขันจริง
                    $is_event_ended = ($today > $row['race_date']);
                    $is_reg_closed  = ($now > $row['registration_end_date']) || $is_event_ended;
                    $is_slots_full  = ($row['total_max'] > 0 && $row['total_booked'] >= $row['total_max']);
                    $can_register   = !$is_reg_closed && !$is_slots_full;
                ?>
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-200/80 shadow-sm hover:shadow-xl hover:border-brand-300 transform hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                    
                    <div>
                        <!-- รูปแบนเนอร์งาน (Aspect Ratio 16:9) -->
                        <div class="w-full aspect-[16/9] relative overflow-hidden bg-slate-100">
                            <img src="<?= e($row['banner_image']) ?>" 
                                 alt="<?= e($row['title']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent"></div>
                            
                            <!-- ป้ายสถานะการรับสมัคร -->
                            <div class="absolute top-3 left-3">
                                <?php if ($is_event_ended): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-200 border border-slate-600 shadow flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-slate-400"></span> จบการแข่งขันแล้ว
                                    </span>
                                <?php elseif ($is_reg_closed || $is_slots_full): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-600 text-white shadow flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-white"></span> ปิดรับสมัครแล้ว
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-lime-300 animate-pulse"></span> เปิดรับสมัคร
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- ยอดวิว -->
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-900/80 text-white backdrop-blur-sm flex items-center gap-1">
                                <i data-lucide="eye" class="w-3 h-3 text-amber-400"></i>
                                <?= number_format((int)$row['view_count']) ?>
                            </span>

                            <!-- วันที่จัดงานลอยด้านล่างภาพ -->
                            <div class="absolute bottom-3 left-3 right-3 text-white flex items-center justify-between text-xs">
                                <span class="font-bold flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-lime-400"></i>
                                    <?= thai_date($row['race_date']) ?>
                                </span>
                                <span class="text-lime-300 font-semibold">
                                    ปล่อยตัว <?= substr($row['race_time'], 0, 5) ?> น.
                                </span>
                            </div>
                        </div>

                        <!-- เนื้อหาภายในการ์ด -->
                        <div class="p-5 sm:p-6 space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-brand-50 text-brand-800 border border-brand-200">
                                    <?= e($row['category_type']) ?>
                                </span>
                                <?php if ($row['min_price'] > 0): ?>
                                    <span class="text-xs font-bold text-slate-500">
                                        เริ่มต้น <?= format_baht($row['min_price']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="font-bold text-base sm:text-lg text-slate-900 group-hover:text-brand-600 transition-colors line-clamp-1">
                                <a href="event-detail.php?id=<?= $row['id'] ?>">
                                    <?= e($row['title']) ?>
                                </a>
                            </h3>
                            
                            <p class="text-xs text-slate-500 flex items-center gap-1.5 line-clamp-1">
                                <i data-lucide="map-pin" class="w-4 h-4 text-brand-500 flex-shrink-0"></i>
                                <?= e($row['location']) ?>
                            </p>

                            <!-- วันปิดรับสมัคร -->
                            <div class="text-[11px] text-slate-400">
                                ⏳ ปิดรับสมัคร: <?= thai_date($row['registration_end_date'], true) ?>
                            </div>
                        </div>
                    </div>

                    <!-- ปุ่มดูรายละเอียดและสมัคร -->
                    <div class="p-5 sm:p-6 pt-0 flex gap-2.5">
                        <a href="event-detail.php?id=<?= $row['id'] ?>" 
                           class="flex-1 text-center py-2.5 px-3 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                            รายละเอียด
                        </a>
                        
                        <?php if ($can_register): ?>
                            <a href="register.php?event_id=<?= $row['id'] ?>" 
                               class="flex-1 text-center py-2.5 px-3 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white shadow-md shadow-brand-500/20 hover:shadow-lg transition-all">
                                สมัครวิ่ง
                            </a>
                        <?php elseif ($is_event_ended): ?>
                            <a href="results.php?event_id=<?= $row['id'] ?>" 
                               class="flex-1 text-center py-2.5 px-3 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow transition-all">
                                ดูผลการแข่งขัน
                            </a>
                        <?php else: ?>
                            <button disabled class="flex-1 text-center py-2.5 px-3 rounded-xl text-xs font-bold bg-slate-200 text-slate-400 cursor-not-allowed">
                                ปิดรับสมัคร
                            </button>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
