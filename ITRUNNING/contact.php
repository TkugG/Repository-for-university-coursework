<?php
/**
 * ==========================================================
 * ไฟล์: contact.php
 * คำอธิบาย: หน้าติดต่อเราและส่งข้อความถึงทีมงาน
 * ==========================================================
 */

require_once __DIR__ . '/config/db.php';

// ตรวจสอบการส่งข้อความติดต่อ (POST)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? 'สอบถามข้อมูลทั่วไป');
    $message = trim($_POST['message'] ?? '');

    try {
        // บันทึกข้อมูลลงตาราง contact_messages
        $sql = "INSERT INTO contact_messages (name, email, phone, subject, message) 
                VALUES (:name, :email, :phone, :subject, :msg)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name'    => $name,
            ':email'   => $email,
            ':phone'   => $phone,
            ':subject' => $subject,
            ':msg'     => $message
        ]);

        $_SESSION['msg'] = "ส่งข้อความติดต่อเรียบร้อยแล้ว! เจ้าหน้าที่จะติดต่อกลับโดยเร็วที่สุด";
    } catch (Exception $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการส่งข้อความ: " . $e->getMessage();
    }

    header("Location: contact.php");
    exit;
}

$page_title = "ติดต่อเรา (Contact Us)";
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8 space-y-8">
    
    <!-- หัวข้อหน้า -->
    <div class="text-center space-y-2">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-50 text-sky-700 text-xs sm:text-sm font-bold border border-sky-200">
            <i data-lucide="headset" class="w-4 h-4 text-sky-500"></i> Get In Touch
        </div>
        <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 flex items-center justify-center gap-2">
            ติดต่อเรา (Contact Us)
        </h1>
        <p class="text-xs sm:text-sm text-slate-500">สอบถามข้อมูลการสมัคร แจ้งปัญหาชำระเงิน หรือปรึกษาการจัดงาน</p>
    </div>

    <!-- ข้อมูลติดต่อ 3 กล่อง -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <strong class="text-slate-800 block text-sm flex items-center gap-1.5">
                <i data-lucide="map-pin" class="w-4 h-4 text-brand-600"></i> ที่อยู่สำนักงาน
            </strong>
            <p class="text-slate-500">เขตป่ามันข้างๆ สำนักเทคโนโลยีสาขาสนเทศ จตุจักร กรุงเทพฯ</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <strong class="text-slate-800 block text-sm flex items-center gap-1.5">
                <i data-lucide="phone" class="w-4 h-4 text-brand-600"></i> โทรศัพท์ Hotline
            </strong>
            <p class="text-slate-500">088-123-4567<br>(จันทร์-ศุกร์ 08:30 - 17:30 น.)</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-1">
            <strong class="text-slate-800 block text-sm flex items-center gap-1.5">
                <i data-lucide="message-circle" class="w-4 h-4 text-emerald-600"></i> LINE Official
            </strong>
            <p class="text-slate-500">LINE: <strong>@itwingkhaopaman</strong><br>ตอบไวภายใน 15 นาที</p>
        </div>
    </div>

    <!-- ฟอร์มส่งข้อความติดต่อ -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-md space-y-4">
        <h2 class="text-lg font-bold text-slate-900 border-b border-slate-100 pb-3">
            ✉️ แบบฟอร์มส่งข้อความติดต่อทีมงาน
        </h2>

        <form action="contact.php" method="POST" class="space-y-4 text-xs sm:text-sm">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">ชื่อ-นามสกุล <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="เช่น นายสมชาย ใจดี" 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">เบอร์โทรศัพท์ <span class="text-rose-500">*</span></label>
                    <input type="tel" name="phone" required placeholder="08XXXXXXXX" 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">อีเมลติดต่อ <span class="text-rose-500">*</span></label>
                <input type="email" name="email" required placeholder="runner@example.com" 
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">หัวข้อเรื่อง</label>
                <select name="subject" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="สอบถามเรื่องการสมัครและรับบัตร E-Ticket">สอบถามเรื่องการสมัครและรับบัตร E-Ticket</option>
                    <option value="แจ้งปัญหาการชำระเงินและสลิป">แจ้งปัญหาการชำระเงินและสลิป</option>
                    <option value="ขอเปลี่ยนไซส์เสื้อวิ่ง">ขอเปลี่ยนไซส์เสื้อวิ่ง</option>
                    <option value="สอบถามผลการแข่งขัน">สอบถามผลการแข่งขัน</option>
                    <option value="อื่นๆ">เรื่องอื่นๆ</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">ข้อความรายละเอียด <span class="text-rose-500">*</span></label>
                <textarea name="message" rows="3" required placeholder="พิมพ์ข้อความที่ต้องการติดต่อ..." 
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <button type="submit" class="w-full py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-2xl shadow-md transition-colors flex items-center justify-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i>
                ส่งข้อความ
            </button>
        </form>
    </div>

    <!-- คำถามที่พบบ่อย FAQ -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 space-y-3">
        <h3 class="font-bold text-sm text-slate-800 mb-2">❓ คำถามที่พบบ่อย (FAQ)</h3>
        
        <details class="text-xs text-slate-600 border-b border-slate-100 pb-2 cursor-pointer">
            <summary class="font-bold text-slate-800">1. โอนเงินแล้วต้องรอยืนยันสลิปกี่ชั่วโมง?</summary>
            <p class="mt-1.5 pl-2 text-slate-500">เจ้าหน้าที่จะตรวจสอบสลิปภายใน 1-2 ชั่วโมง คุณสามารถใช้ Booking Code ตรวจดูบัตร E-Ticket ได้ทันที</p>
        </details>

        <details class="text-xs text-slate-600 border-b border-slate-100 pb-2 cursor-pointer">
            <summary class="font-bold text-slate-800">2. วันรับ BIB ต้องเตรียมหลักฐานอะไรบ้าง?</summary>
            <p class="mt-1.5 pl-2 text-slate-500">แสดงบัตร E-Ticket ในมือถือ พร้อมบัตรประชาชนตัวจริง ณ จุดลงทะเบียน</p>
        </details>

        <details class="text-xs text-slate-600 cursor-pointer">
            <summary class="font-bold text-slate-800">3. ให้ผู้อื่นรับ BIB แทนได้หรือไม่?</summary>
            <p class="mt-1.5 pl-2 text-slate-500">รับแทนได้ โดยเตรียมภาพถ่ายบัตร E-Ticket และภาพสำเนาบัตรประชาชนของผู้สมัคร</p>
        </details>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
