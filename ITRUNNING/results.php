<?php
// ==========================================
// 1. นำเข้าไฟล์เชื่อมต่อฐานข้อมูล และแถบเมนู
// ==========================================
require_once "db.php";
require_once "navbar.php";

// ==========================================
// 2. รับค่าค้นหาหมายเลข BIB หรือชื่อนักวิ่ง
// ==========================================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// ดึงรายชื่องานวิ่งสำหรับ Dropdown
$events_list = $conn->query("SELECT id, title FROM events ORDER BY race_date DESC")->fetchAll();

// 3. เขียนคำสั่ง SQL ค้นหาผลการแข่งขัน
$sql = "SELECT r.*, e.title AS event_title 
        FROM race_results r 
        JOIN events e ON r.event_id = e.id 
        WHERE 1=1";

if ($search != '') {
    $sql .= " AND (r.bib_number LIKE '%$search%' OR r.runner_name LIKE '%$search%')";
}

if ($event_id > 0) {
    $sql .= " AND r.event_id = $event_id";
}

$sql .= " ORDER BY r.overall_rank ASC LIMIT 100";

$stmt = $conn->query($sql);
$results = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-8">
    
    <!-- ส่วนหัวหน้าผลการแข่งขัน -->
    <div class="text-center space-y-3">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 text-amber-800 text-xs sm:text-sm font-bold border border-amber-200 shadow-sm">
            <i data-lucide="trophy" class="w-4 h-4 text-amber-500"></i> Race Results & Leaderboard
        </div>
        <h1 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight">
            ผลการแข่งขัน <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-600 to-brand-600">อย่างเป็นทางการ</span>
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 max-w-xl mx-auto">
            ตรวจสอบสถิติเวลาชิปไทม์มิ่ง (Net Time), เวลาปล่อยตัว (Gun Time) และ Pace เฉลี่ยของนักวิ่ง
        </p>
    </div>

    <!-- กล่องค้นหาและตัวกรองงานวิ่ง -->
    <div class="max-w-3xl mx-auto">
        <div class="bg-white p-4 sm:p-6 rounded-3xl shadow-lg shadow-slate-200/60 border border-slate-200">
            <form method="GET" action="results.php" class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-center">
                
                <!-- เลือกงานวิ่ง -->
                <div class="sm:col-span-5">
                    <label class="block text-xs font-bold text-slate-600 mb-1">เลือกรายการแข่งขัน</label>
                    <select name="event_id" class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="0">-- ทุกรายการงานวิ่ง --</option>
                        <?php foreach ($events_list as $ev): ?>
                            <option value="<?= $ev['id'] ?>" <?= $event_id == $ev['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ค้นหา BIB หรือชื่อ -->
                <div class="sm:col-span-5">
                    <label class="block text-xs font-bold text-slate-600 mb-1">หมายเลข BIB หรือชื่อนักวิ่ง</label>
                    <div class="relative">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                               placeholder="เช่น A21-1001 หรือ ณัฐวุฒิ" 
                               class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    </div>
                </div>

                <!-- ปุ่มค้นหา -->
                <div class="sm:col-span-2 pt-1 sm:pt-5 flex gap-2">
                    <button type="submit" class="flex-1 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow transition-colors flex items-center justify-center gap-1">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i> ค้นหา
                    </button>
                    <?php if ($search != '' || $event_id > 0): ?>
                        <a href="results.php" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition-colors" title="ล้างการค้นหา">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    </div>

    <!-- ตารางแสดงผลการแข่งขันแบบ Responsive (Overflow-x-auto ไม่ดันหน้าเว็บล้น) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-md overflow-hidden">
        
        <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 bg-slate-50/50">
            <h3 class="font-bold text-sm sm:text-base text-slate-800 flex items-center gap-2">
                <i data-lucide="list-ordered" class="w-5 h-5 text-brand-600"></i>
                ตารางสรุปผลเวลาการแข่งขัน (พบ <?= count($results) ?> รายการ)
            </h3>
            <span class="text-[11px] text-slate-500 font-medium">⚡ ผลเวลาอัปเดตแบบเรียลไทม์</span>
        </div>

        <?php if (count($results) == 0): ?>
            <div class="p-12 text-center text-slate-400 space-y-3">
                <i data-lucide="search-x" class="w-12 h-12 mx-auto text-slate-300"></i>
                <p class="text-sm font-semibold text-slate-600">ไม่พบข้อมูลผลการแข่งขันที่ตรงกับคำค้นหา</p>
                <p class="text-xs text-slate-400">ลองตรวจสอบหมายเลข BIB หรือพิมพ์ชื่อนักวิ่งใหม่อีกครั้ง</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap min-w-[700px]">
                    <thead class="bg-slate-100 text-slate-600 uppercase text-[11px] font-bold tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-16">อันดับ</th>
                            <th class="py-3.5 px-4">หมายเลข BIB</th>
                            <th class="py-3.5 px-4">ชื่อ-นามสกุล นักวิ่ง</th>
                            <th class="py-3.5 px-4">ชื่องานวิ่ง / ระยะทาง</th>
                            <th class="py-3.5 px-4 font-mono">Net Time (ชิป)</th>
                            <th class="py-3.5 px-4 font-mono">Gun Time</th>
                            <th class="py-3.5 px-4">Pace เฉลี่ย</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($results as $row): ?>
                            <tr class="hover:bg-brand-50/40 transition-colors">
                                
                                <!-- อันดับ Rank -->
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($row['overall_rank'] == 1): ?>
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-400 text-slate-900 font-black text-xs shadow-sm">🥇 1</span>
                                    <?php elseif ($row['overall_rank'] == 2): ?>
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-300 text-slate-900 font-black text-xs shadow-sm">🥈 2</span>
                                    <?php elseif ($row['overall_rank'] == 3): ?>
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-600 text-white font-black text-xs shadow-sm">🥉 3</span>
                                    <?php else: ?>
                                        <span class="font-bold text-slate-700">#<?= $row['overall_rank'] ?></span>
                                    <?php endif; ?>
                                </td>

                                <!-- BIB Number -->
                                <td class="py-3.5 px-4 font-mono font-black text-brand-700 text-sm">
                                    <?= htmlspecialchars($row['bib_number']) ?>
                                </td>

                                <!-- Runner Name -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($row['runner_name']) ?></div>
                                    <div class="text-[10px] text-slate-400">
                                        <?= $row['gender'] == 'M' ? 'ชาย' : 'หญิง' ?> • <?= htmlspecialchars($row['age_group']) ?>
                                    </div>
                                </td>

                                <!-- Event Title & Category -->
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-700 font-medium max-w-xs truncate"><?= htmlspecialchars($row['event_title']) ?></div>
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-brand-50 text-brand-800 border border-brand-200 mt-0.5">
                                        <?= htmlspecialchars($row['category_name']) ?>
                                    </span>
                                </td>

                                <!-- Net Time -->
                                <td class="py-3.5 px-4 font-mono font-extrabold text-emerald-700 text-sm">
                                    <?= htmlspecialchars($row['net_time']) ?>
                                </td>

                                <!-- Gun Time -->
                                <td class="py-3.5 px-4 font-mono text-slate-500">
                                    <?= htmlspecialchars($row['gun_time']) ?>
                                </td>

                                <!-- Pace -->
                                <td class="py-3.5 px-4 font-semibold text-slate-700">
                                    <?= htmlspecialchars($row['avg_pace']) ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

</div>

<?php require_once "footer.php"; ?>
