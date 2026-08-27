<?php
// ==========================================
// 1. เริ่มต้น Session เสมอสำหรับระบบแจ้งเตือน
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 2. กำหนดค่าการเชื่อมต่อฐานข้อมูล MySQL
// ==========================================
$host = "sql103.infinityfree.com";
$user = "if0_42700951";
$pass = "yYrTSEBeXUhHis";
$dbname = "if0_42700951_runlan_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $e->getMessage());
}
?>
