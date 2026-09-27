<?php

declare(strict_types=1);

/**
 * Smart IT Helpdesk - Database Setup & phpMyAdmin Sync CLI
 */

require_once __DIR__ . '/src/Core/Config.php';

use App\Core\Config;

Config::load(__DIR__ . '/.env');

$host = Config::get('DB_HOST', '127.0.0.1');
$port = (int) Config::get('DB_PORT', '3306');
$database = Config::get('DB_DATABASE', 'it_helpdesk');
$username = Config::get('DB_USERNAME', 'root');
$password = Config::get('DB_PASSWORD', '');

echo "==========================================================\n";
echo "🛠️  SMART IT HELPDESK - DATABASE & phpMyAdmin SETUP\n";
echo "==========================================================\n\n";

echo "📡 ตรวจสอบการเชื่อมต่อไปยัง MySQL Server ({$host}:{$port})...\n";

try {
    // 1. เชื่อมต่อ MySQL Server ระดับ Root
    $rootPdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    $serverVersion = $rootPdo->query("SELECT VERSION()")->fetchColumn();
    echo "✅ เชื่อมต่อ MySQL สำเร็จ! (Version: {$serverVersion})\n\n";

    // 2. สร้างฐานข้อมูลหากยังไม่มี
    echo "📁 สร้างฐานข้อมูล `{$database}` หากยังไม่มีใน phpMyAdmin/MySQL...\n";
    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "✅ ฐานข้อมูล `{$database}` พร้อมใช้งาน!\n\n";

    // 3. เชื่อมต่อเข้าไปในฐานข้อมูลเป้าหมาย
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ]);

    // 4. นำเข้า schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("ไม่พบไฟล์ schema.sql ที่ {$schemaFile}");
    }

    echo "📥 กำลังนำเข้าโครงสร้างตารางและข้อมูลเริ่มต้นจาก schema.sql...\n";
    $sqlContent = file_get_contents($schemaFile);
    
    // รันคำสั่ง SQL ทีละคำสั่ง หรือ multi-query
    $pdo->exec($sqlContent);
    echo "✅ นำเข้า schema.sql เรียบร้อยสมบูรณ์!\n\n";

    // 5. แสดงสรุปตารางและจำนวนข้อมูล
    echo "📊 รายการตารางในฐานข้อมูล `{$database}` (ดูได้ใน phpMyAdmin):\n";
    echo str_repeat('-', 50) . "\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        printf("  - %-25s : %3d รายการ\n", $t, $count);
    }
    echo str_repeat('-', 50) . "\n\n";

    echo "🎉 เชื่อมต่อฐานข้อมูลกับ phpMyAdmin เรียบร้อยแล้ว!\n";
    echo "👉 เปิดดูตารางในเบราว์เซอร์ได้ที่: http://localhost/phpmyadmin\n";
    echo "👉 ฐานข้อมูลชื่อ: {$database}\n\n";
    echo "🔑 บัญชีผู้ใช้สำหรับทดสอบ (รหัสผ่าน: password123):\n";
    echo "   - Admin     : admin@helpdesk.local\n";
    echo "   - Technician: tech1@helpdesk.local\n";
    echo "   - User      : user@helpdesk.local\n\n";
    echo "🚀 สตาร์ทระบบด้วยคำสั่ง: php -S localhost:8000 -t public\n";
    echo "==========================================================\n";

} catch (PDOException $e) {
    echo "❌ เกิดข้อผิดพลาดในการเชื่อมต่อ MySQL: " . $e->getMessage() . "\n";
    echo "\nคำแนะนำ:\n";
    echo "1. ตรวจสอบว่าได้เปิด XAMPP Control Panel แล้วกด Start โมดูล 'MySQL' แล้วหรือยัง\n";
    echo "2. ตรวจสอบค่า DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD ในไฟล์ .env\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "\n";
    exit(1);
}
