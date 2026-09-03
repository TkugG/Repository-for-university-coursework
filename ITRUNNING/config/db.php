<?php
/**
 * ==========================================================
 * ไฟล์: config/db.php
 * คำอธิบาย: การเชื่อมต่อฐานข้อมูล MySQL (รองรับทั้ง XAMPP และ Hosting)
 * ==========================================================
 */

require_once __DIR__ . '/app.php';

// ตรวจสอบสภาพแวดล้อม: รันบน Hosting ออนไลน์ (InfinityFree) หรือ Localhost (XAMPP)
$is_infinityfree = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false;

if ($is_infinityfree) {
    // 🌐 การตั้งค่าสำหรับ InfinityFree Hosting
    $host   = 'sql103.infinityfree.com';
    $port   = '3306';
    $dbname = 'if0_42700951_runlan_db';
    $user   = 'if0_42700951';
    $pass   = 'yYrTSEBeXUhHis';
} else {
    // 💻 การตั้งค่าสำหรับ Localhost / XAMPP (ค่าเริ่มต้น)
    $host   = getenv('DB_HOST') ?: '127.0.0.1';
    $port   = getenv('DB_PORT') ?: '3306';
    $dbname = getenv('DB_NAME') ?: 'runlan_db';
    $user   = getenv('DB_USER') ?: 'root';
    $pass   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
    ];
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // แสดงข้อความแจ้งเตือนข้อผิดพลาด
    die("⚠️ ไม่สามารถเชื่อมต่อฐานข้อมูลได้: " . $e->getMessage() . "<br><small>กรุณาตรวจสอบว่าเปิด MySQL ใน XAMPP Control Panel แล้ว และมีฐานข้อมูลชื่อ <code>$dbname</code></small>");
}
