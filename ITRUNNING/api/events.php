<?php
/**
 * ==========================================================
 * ไฟล์: api/events.php
 * คำอธิบาย: REST JSON API ส่งข้อมูลปฏิทินงานวิ่งสำหรับ FullCalendar JS
 * ==========================================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

try {
    // ดึงข้อมูลงานวิ่งทั้งหมด
    $sql = "SELECT id, title, race_date, race_time, category_type FROM events ORDER BY race_date ASC";
    $stmt = $conn->query($sql);
    $events = $stmt->fetchAll();

    // จัดรูปแบบข้อมูลให้ตรงกับที่ FullCalendar JS ต้องการ
    $calendar_events = [];
    foreach ($events as $row) {
        // กำหนดสีตามประเภทงานวิ่ง
        $color = '#059669'; // เขียวเริ่มต้น (Fun Run)
        $cat = strtolower($row['category_type'] ?? '');
        
        if (str_contains($cat, 'full') || str_contains($cat, '42')) {
            $color = '#DC2626'; // แดง
        } elseif (str_contains($cat, 'half') || str_contains($cat, '21')) {
            $color = '#EA580C'; // ส้ม
        } elseif (str_contains($cat, 'mini') || str_contains($cat, '10')) {
            $color = '#0284C7'; // ฟ้า
        } elseif (str_contains($cat, 'trail')) {
            $color = '#8B5CF6'; // ม่วง
        }

        $time_str = !empty($row['race_time']) ? substr($row['race_time'], 0, 5) . " น. " : "";

        $calendar_events[] = [
            'id'              => $row['id'],
            'title'           => $time_str . $row['title'],
            'start'           => $row['race_date'],
            'url'             => 'event-detail.php?id=' . $row['id'],
            'backgroundColor' => $color,
            'borderColor'     => $color
        ];
    }

    echo json_encode($calendar_events, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load events: ' . $e->getMessage()]);
}
