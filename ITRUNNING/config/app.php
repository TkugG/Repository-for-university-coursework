<?php
/**
 * ==========================================================
 * ไฟล์: config/app.php
 * คำอธิบาย: กำหนดค่าคงที่ส่วนกลางและการตั้งค่าพื้นฐานของระบบ
 * ==========================================================
 */

// เริ่มต้น Session เสมอสำหรับระบบแจ้งเตือนและข้อมูลผู้ใช้
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// กำหนด Timezone เป็นประเทศไทย
date_default_timezone_set('Asia/Bangkok');

// ข้อมูลพื้นฐานเว็บไซต์
define('APP_NAME', 'IT วิ่งเข้าป่ามัน');
define('APP_TAGLINE', 'แพลตฟอร์มงานวิ่งเทรลและธรรมชาติ');
define('APP_VERSION', '1.0.0');

// กำหนดโฟลเดอร์สำหรับจัดเก็บไฟล์อัปโหลด
define('ROOT_DIR', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_DIR . '/uploads/');
define('UPLOAD_PHOTO_DIR', ROOT_DIR . '/uploads/photos/');
define('UPLOAD_SLIP_DIR', ROOT_DIR . '/uploads/slips/');
