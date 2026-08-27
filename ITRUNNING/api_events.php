<?php
// ==========================================
// 1. กำหนด Header ให้ส่งค่าเป็น JSON
// ==========================================
header('Content-Type: application/json; charset=utf-8');

// 2. นำเข้าไฟล์เชื่อมต่อฐานข้อมูล
require_once "db.php";

// 3. ดึงข้อมูลงานวิ่งทั้งหมด
$sql = "SELECT id, title, race_date, race_time, category_type FROM events";
$stmt = $conn->query($sql);
$events = $stmt->fetchAll();

// 4. จัดรูปแบบข้อมูลให้ตรงกับที่ FullCalendar JS ต้องการ
$calendar_events = [];
foreach ($events as $row) {
    // กำหนดสีตามประเภทงานวิ่ง
    $color = '#059669'; // เขียวเริ่มต้น
    if ($row['category_type'] == 'Full Marathon') {
        $color = '#DC2626'; // แดง
    } elseif ($row['category_type'] == 'Half Marathon') {
        $color = '#EA580C'; // ส้ม
    } elseif ($row['category_type'] == 'Mini Marathon') {
        $color = '#0284C7'; // ฟ้า
    } elseif ($row['category_type'] == 'Trail') {
        $color = '#8B5CF6'; // ม่วง
    }

    $calendar_events[] = [
        'id'              => $row['id'],
        'title'           => substr($row['race_time'], 0, 5) . " น. " . $row['title'],
        'start'           => $row['race_date'],
        'url'             => 'event-detail.php?id=' . $row['id'],
        'backgroundColor' => $color,
        'borderColor'     => $color
    ];
}

// 5. ส่งผลลัพธ์ออกไปเป็น JSON
echo json_encode($calendar_events, JSON_UNESCAPED_UNICODE);
?>
