<?php
/**
 * ==========================================================
 * ไฟล์: organizer.php
 * คำอธิบาย: หน้าฟอร์มสำหรับผู้จัดงานและประชาสัมพันธ์งานวิ่ง
 * ==========================================================
 */

require_once __DIR__ . '/config/db.php';

// ตรวจสอบการส่งฟอร์มของผู้จัดงาน (POST)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $organizer_name   = trim($_POST['organizer_name'] ?? '');
    $organization     = trim($_POST['organization'] ?? '');
    $event_title      = trim($_POST['event_title'] ?? '');
    $expected_runners = (int)($_POST['expected_runners'] ?? 0);
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $message          = trim($_POST['message'] ?? '');

    try {
        // ตรวจสอบหรือสร้างตาราง organizer_inquiries ถ้ายังไม่มี
        $conn->exec("CREATE TABLE IF NOT EXISTS `organizer_inquiries` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `organizer_name` VARCHAR(150) NOT NULL,
            `organization` VARCHAR(150) NULL,
            `event_title` VARCHAR(255) NOT NULL,
            `expected_runners` INT UNSIGNED NOT NULL DEFAULT 0,
            `email` VARCHAR(150) NOT NULL,
            `phone` VARCHAR(30) NOT NULL,
            `message` TEXT NULL,
            `status` ENUM('new', 'contacted', 'closed') NOT NULL DEFAULT 'new',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $sql = "INSERT INTO organizer_inquiries (organizer_name, organization, event_title, expected_runners, email, phone, message)
                VALUES (:name, :org, :title, :runners, :email, :phone, :msg)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name'    => $organizer_name,
            ':org'     => $organization,
            ':title'   => $event_title,
            ':runners' => $expected_runners,
            ':email'   => $email,
            ':phone'   => $phone,
            ':msg'     => $message
        ]);

        $_SESSION['msg'] = "ส่งคำขอจัดงานเรียบร้อยแล้ว! ทีมงานจะติดต่อกลับโดยเร็วที่สุด";
    } catch (Exception $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการส่งข้อมูล: " . $e->getMessage();
    }

    header("Location: organizer.php");
    exit;
}

$page_title = "สำหรับผู้จัดงานและประชาสัมพันธ์ (Organizer & PR)";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto px-4 py-8 space-y-10">
    
    <!-- ส่วนหัว Hero Section สำหรับผู้จัดงาน -->
    <div class="bg-gradient-to-br from-brand-800 via-brand-700 to-slate-900 text-white rounded-3xl p-8 sm:p-12 shadow-xl relative overflow-hidden text-center space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 text-lime-300 text-xs font-bold border border-white/20">
            <i data-lucide="megaphone" class="w-4 h-4"></i>
            Organizer & PR Solutions
        </div>
        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">
            โซลูชันครบวงจรสำหรับ <span class="text-lime-400">ผู้จัดงานวิ่ง</span>
        </h1>
        <p class="text-xs sm:text-sm text-brand-100 max-w-2xl mx-auto leading-relaxed">
            ตั้งแต่ระบบรับสมัครที่เสถียรที่สุด, สื่อโปรโมตและประชาสัมพันธ์เข้าถึงนักวิ่งนับหมื่น, ไปจนถึงระบบจับเวลาชิปไทม์มิ่งแม่นยำระดับสากล
        </p>
        <div class="pt-2">
            <a href="#inquiry-form" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-xs sm:text-sm font-bold text-slate-900 bg-lime-400 hover:bg-lime-300 shadow-lg transition-all transform hover:-translate-y-0.5">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                ยื่นเรื่องจัดงาน / ขอใบเสนอราคา
            </a>
        </div>
    </div>

    <!-- รายละเอียดบริการหลักทั้ง 4 ด้าน -->
    <div class="space-y-4">
        <div class="text-center">
            <span class="text-xs font-bold text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-200">Our Services</span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-2">บริการที่เราพร้อมดูแลงานของคุณ</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            
            <!-- บริการที่ 1: ระบบรับสมัคร -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">1. ระบบรับสมัคร & E-Ticket ออนไลน์</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    ระบบลงทะเบียนที่รองรับนักวิ่งพร้อมกันหลักหมื่นคน ตัดโควตาเรียลไทม์ ป้องกัน Overbooking ออกบัตร E-Ticket พร้อม QR Code ตรวจสอบตัวตนรวดเร็ว
                </p>
                <ul class="text-xs text-slate-600 space-y-1.5 pt-2 border-t border-slate-100">
                    <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i> อัปโหลดรูปหน้าตรงสำหรับพิมพ์บน BIB</li>
                    <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i> รองรับ PromptPay และระบบยืนยันสลิป</li>
                </ul>
            </div>

            <!-- บริการที่ 2: สื่อประชาสัมพันธ์และโปรโมตงานวิ่ง -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-lime-50 text-lime-700 flex items-center justify-center font-bold">
                    <i data-lucide="megaphone" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">2. บริการสื่อประชาสัมพันธ์และโปรโมตงานวิ่ง (PR & Media)</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    ผลักดันงานวิ่งของคุณให้เป็นที่รู้จัก เข้าถึงกลุ่มนักวิ่งตัวจริงทั่วประเทศ ทั้งสายถนนและสายเทรลธรรมชาติ ผ่านช่องทางสื่อครบวงจร
                </p>
                <ul class="text-xs text-slate-600 space-y-1.5 pt-2 border-t border-slate-100">
                    <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i> ปักหมุดแบนเนอร์เด่นบนหน้าแรกและปฏิทินงานวิ่ง</li>
                    <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i> โพสต์โปรโมตในเพจและกลุ่มนักวิ่งยอดนิยม</li>
                    <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i> รายงานสถิติยอดผู้เข้าชม (View Count) แบบโปร่งใส</li>
                </ul>
            </div>

            <!-- บริการที่ 3: ระบบจับเวลาชิปไทม์มิ่ง -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <i data-lucide="timer" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">3. ชิปไทม์มิ่ง & ประกาศผลสด (Live Results)</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    อุปกรณ์จับเวลา RFID คุณภาพระดับสากล ซุ้ม Start/Finish Line แม่นยำระดับเสี้ยววินาที รายงานผล Gun Time และ Net Time สดบนหน้าเว็บทันใจ
                </p>
                <ul class="text-xs text-slate-600 space-y-1.5 pt-2 border-t border-slate-100">
                    <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i> ค้นหาผลการแข่งขันด้วยหมายเลข BIB ได้ทันที</li>
                    <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i> สรุปผลจัดอันดับ Overall และตามรุ่นอายุ</li>
                </ul>
            </div>

            <!-- บริการที่ 4: ออกแบบและผลิตเสื้อ/เหรียญ -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                    <i data-lucide="award" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">4. ผลิตเสื้อ เหรียญที่ระลึก และ BIB</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    บริการออกแบบและสั่งผลิตเสื้อวิ่งเนื้อผ้า Aerocool คุณภาพสูง เหรียญรางวัลโลหะ/ไม้งานคราฟต์ และเบอร์วิ่ง BIB เคลือบกันน้ำทนทาน
                </p>
                <ul class="text-xs text-slate-600 space-y-1.5 pt-2 border-t border-slate-100">
                    <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i> ดีไซน์สวยงาม โดดเด่นตามเอกลักษณ์ของงาน</li>
                    <li class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i> ควบคุมคุณภาพมาตรฐานโรงงานตรงเวลา</li>
                </ul>
            </div>

        </div>
    </div>

    <!-- ฟอร์มติดต่อยื่นเรื่องจัดงาน -->
    <div id="inquiry-form" class="bg-white p-6 sm:p-10 rounded-3xl border border-slate-200 shadow-lg space-y-6">
        <div>
            <span class="text-xs font-bold text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-200">Contact Organizer Team</span>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 mt-2">
                📝 ฟอร์มยื่นเรื่องจัดงาน / ขอรับคำปรึกษาและใบเสนอราคา
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">กรอกข้อมูลเบื้องต้นเกี่ยวกับงานวิ่งของคุณ ทีมงานจะติดต่อกลับภายใน 24 ชั่วโมง</p>
        </div>

        <form action="organizer.php" method="POST" class="space-y-4 text-xs sm:text-sm">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">ชื่อ-นามสกุล ผู้ติดต่อ <span class="text-rose-500">*</span></label>
                    <input type="text" name="organizer_name" required placeholder="เช่น คุณสมศักดิ์ ผู้จัดงาน" 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">ชื่อหน่วยงาน / บริษัท / ชมรม</label>
                    <input type="text" name="organization" placeholder="เช่น สมาคมกีฬาแห่งประเทศไทย" 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">ชื่องานวิ่งที่ต้องการจัด / ประชาสัมพันธ์ <span class="text-rose-500">*</span></label>
                <input type="text" name="event_title" required placeholder="เช่น เขาใหญ่ ไนท์เทรล 2027" 
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">จำนวนนักวิ่งที่คาดการณ์</label>
                    <select name="expected_runners" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="300">300 - 500 คน (งานขนาดเล็ก)</option>
                        <option value="1000" selected>500 - 1,500 คน (งานขนาดกลาง)</option>
                        <option value="3000">1,500 - 3,000 คน (งานมาตรฐาน)</option>
                        <option value="5000">มากกว่า 3,000 คนขึ้นไป (งานใหญ่)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">อีเมลติดต่อ <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" required placeholder="organizer@example.com" 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">เบอร์โทรศัพท์ <span class="text-rose-500">*</span></label>
                    <input type="tel" name="phone" required placeholder="08XXXXXXXX" 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">รายละเอียดงาน หรือบริการที่สนใจ</label>
                <textarea name="message" rows="3" placeholder="ระบุประเภทระยะทาง หรือบริการที่ต้องการ เช่น รับสมัครออนไลน์ + สื่อประชาสัมพันธ์ + ชิปไทม์มิ่ง..." 
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <button type="submit" class="w-full py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-2xl shadow-lg transition-colors flex items-center justify-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i>
                ส่งข้อมูลเพื่อขอรับคำปรึกษาและใบเสนอราคา
            </button>
        </form>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
