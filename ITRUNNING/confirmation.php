<?php
/**
 * ==========================================================
 * ไฟล์: confirmation.php
 * คำอธิบาย: หน้ายืนยันการจอง บัตรจำลอง E-Ticket สำหรับพิมพ์
 * ==========================================================
 */

require_once __DIR__ . '/config/db.php';

// รับรหัส Booking Code จาก URL หรือฟอร์มค้นหา
$booking_code = isset($_GET['booking_code']) ? trim($_GET['booking_code']) : '';
$reg = null;

if ($booking_code !== '') {
    // ดึงข้อมูลการสมัคร พร้อมเชื่อมตาราง events และ categories
    $sql = "SELECT r.*, e.title AS event_title, e.location AS event_location, e.race_date, e.race_time, c.category_name, c.price 
            FROM registrations r 
            JOIN events e ON r.event_id = e.id 
            JOIN event_categories c ON r.category_id = c.id 
            WHERE r.booking_code = :code LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':code' => $booking_code]);
    $reg = $stmt->fetch();
}

$page_title = $reg ? "E-Ticket: " . $reg['booking_code'] : "ค้นหาบัตร E-Ticket";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-2xl mx-auto px-4 py-8 ticket-container">
    
    <?php if (!$reg): ?>
        <!-- กรณีไม่พบข้อมูลการจอง -->
        <div class="bg-white p-8 sm:p-10 rounded-3xl border border-slate-200 text-center space-y-4 shadow-sm no-print">
            <div class="w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mx-auto font-bold">
                <i data-lucide="ticket" class="w-7 h-7"></i>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-800">ค้นหาบัตร E-Ticket นักวิ่ง</h2>
            <p class="text-xs sm:text-sm text-slate-500">กรุณากรอกรหัสการจอง (Booking Code) เช่น RUN-2026-XXXX</p>
            
            <form action="confirmation.php" method="GET" class="flex flex-col sm:flex-row gap-2 max-w-md mx-auto pt-2">
                <input type="text" name="booking_code" required placeholder="กรอก Booking Code..." 
                       class="flex-1 px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl uppercase focus:outline-none focus:ring-2 focus:ring-brand-500">
                <button type="submit" class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-sm shadow-md transition-colors flex items-center justify-center gap-1.5">
                    <i data-lucide="search" class="w-4 h-4"></i> ค้นหา
                </button>
            </form>
        </div>
    <?php else: ?>
        
        <!-- แถบปุ่มด้านบนสำหรับหน้าจอปกติ (ซ่อนตอนพิมพ์) -->
        <div class="flex items-center justify-between mb-4 no-print">
            <a href="index.php" class="back-btn inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-brand-600">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> กลับหน้าหลัก
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 shadow transition-colors">
                <i data-lucide="printer" class="w-4 h-4"></i> สั่งพิมพ์บัตร (Print E-Ticket)
            </button>
        </div>

        <!-- ตัวบัตร E-Ticket Card -->
        <div class="bg-white rounded-3xl border-2 border-slate-200 overflow-hidden shadow-xl" id="printable-ticket">
            
            <!-- 1. ส่วนหัวบัตรสีเขียวสดใส -->
            <div class="bg-gradient-to-r from-brand-800 via-brand-700 to-slate-900 text-white p-6 sm:p-7 relative">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <span class="inline-block text-[10px] bg-white/20 px-3 py-1 rounded-full uppercase font-bold tracking-wider text-lime-300">
                            OFFICIAL E-TICKET PASS
                        </span>
                        <h1 class="text-xl sm:text-2xl font-black text-white leading-snug">
                            <?= e($reg['event_title']) ?>
                        </h1>
                        <p class="text-xs text-brand-100 flex items-center gap-1.5 pt-1">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-lime-400"></i>
                            <?= e($reg['event_location']) ?>
                        </p>
                    </div>

                    <!-- โลโก้ย่อมุมขวาบน -->
                    <div class="text-right hidden sm:block">
                        <span class="text-sm font-black tracking-tight block">IT วิ่งเข้าป่ามัน</span>
                        <span class="text-[9px] text-lime-300 font-bold block uppercase">RunLan 2026</span>
                    </div>
                </div>
            </div>

            <!-- 2. แถบรอยปรุคูปอง (Coupon Notch) -->
            <div class="bg-slate-100 py-1.5 px-4 text-center border-y border-dashed border-slate-300 text-[10px] font-mono text-slate-500 uppercase tracking-widest">
                ••• RUNNER PASS CONFIRMATION •••
            </div>

            <!-- 3. ข้อมูลนักวิ่งและรายละเอียดการแข่งขัน -->
            <div class="p-6 sm:p-8 space-y-6">
                
                <!-- ข้อมูลรูปและชื่อผู้สมัคร -->
                <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    
                    <!-- รูปถ่ายหน้าตรงนักวิ่ง -->
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-slate-200 border-2 border-white shadow-sm flex-shrink-0">
                        <?php 
                            $photo_url = get_upload_url($reg['runner_photo'] ?? '', 'photos');
                            if (!empty($photo_url) && file_exists(ROOT_DIR . '/' . $photo_url)): 
                        ?>
                            <img src="<?= e($photo_url) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs text-center p-2">ไม่มีรูปถ่าย</div>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-1">
                        <span class="text-[11px] text-slate-400 font-medium">ชื่อ-นามสกุล นักวิ่ง:</span>
                        <div class="text-base sm:text-lg font-black text-slate-900"><?= e($reg['full_name']) ?></div>
                        <div class="inline-flex items-center gap-2 pt-0.5">
                            <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-brand-100 text-brand-800">
                                เสื้อไซส์: <?= e($reg['shirt_size']) ?>
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800">
                                สถานะ: ยืนยันแล้ว
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ตารางข้อมูลกำหนดการและรหัส -->
                <div class="grid grid-cols-2 gap-3 text-xs sm:text-sm">
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-[11px] text-slate-400 block mb-0.5">Booking Code:</span>
                        <strong class="text-brand-700 font-mono text-sm sm:text-base font-black"><?= e($reg['booking_code']) ?></strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-[11px] text-slate-400 block mb-0.5">ประเภทระยะทาง:</span>
                        <strong class="text-slate-800 font-bold"><?= e($reg['category_name']) ?></strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-[11px] text-slate-400 block mb-0.5">วันและเวลาปล่อยตัว:</span>
                        <strong class="text-slate-800 font-bold"><?= thai_date($reg['race_date']) ?> (<?= substr($reg['race_time'], 0, 5) ?> น.)</strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-[11px] text-slate-400 block mb-0.5">ยอดเงินที่ชำระ:</span>
                        <strong class="text-brand-600 font-bold"><?= format_baht($reg['price']) ?></strong>
                    </div>
                </div>

                <!-- ส่วน QR Code และคำแนะนำ -->
                <div class="text-center pt-2 space-y-2">
                    <div class="inline-block p-3 bg-white rounded-2xl border-2 border-slate-200 shadow-sm">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?= urlencode($reg['booking_code']) ?>" 
                             alt="QR Code" class="w-28 h-28 mx-auto">
                    </div>
                    <div class="text-[11px] text-slate-500 font-medium">
                        กรุณาแสดงบัตร E-Ticket ใบนี้ (หรือบันทึกภาพหน้าจอ) พร้อมบัตรประชาชนตัวจริง ณ จุดรับอุปกรณ์และเบอร์วิ่ง (BIB)
                    </div>
                </div>

            </div>

            <!-- ท้ายบัตรสำหรับสั่งพิมพ์ -->
            <div class="bg-slate-50 p-4 border-t border-slate-100 text-center no-print">
                <button onclick="window.print()" class="w-full sm:w-auto px-8 py-3 rounded-2xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-md transition-all flex items-center justify-center gap-2 mx-auto">
                    <i data-lucide="printer" class="w-4 h-4"></i> สั่งพิมพ์บัตร E-Ticket
                </button>
            </div>

        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
