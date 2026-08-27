<?php
/**
 * API: ตรวจสอบรายวิชา (AJAX endpoint)
 *
 * GET /api/check_course.php?code=CS101
 * Response: {"found": true, "course_name_th": "...", "credits": "3-0-6"}
 *
 * GET /api/check_course.php?search=คณิตศาสตร์
 * Response: [{"course_code": "...", "course_name_th": "..."}]
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Course.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

// รับเฉพาะ GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

$courseModel = new Course();

// ─── ค้นหาด้วยรหัสวิชา (exact) ───────────────────────────────────────────────
if (!empty($_GET['code'])) {
    $code   = cleanInput($_GET['code']);
    $course = $courseModel->findByCode($code);

    if ($course) {
        echo json_encode([
            'found'          => true,
            'course_code'    => $course['course_code'],
            'course_name_th' => $course['course_name_th'],
            'course_name_en' => $course['course_name_en'],
            'credits'        => $course['credit_theory'] . '-' . $course['credit_practice'] . '-' . $course['credit_self'],
        ]);
    } else {
        echo json_encode(['found' => false]);
    }
    exit();
}

// ─── ค้นหาด้วยชื่อวิชา (keyword) ─────────────────────────────────────────────
if (!empty($_GET['search'])) {
    $keyword = cleanInput($_GET['search']);

    if (mb_strlen($keyword) < 2) {
        http_response_code(400);
        echo json_encode(['error' => 'Keyword ต้องมีอย่างน้อย 2 ตัวอักษร']);
        exit();
    }

    $results = $courseModel->searchByName($keyword);
    echo json_encode($results);
    exit();
}

http_response_code(400);
echo json_encode(['error' => 'กรุณาระบุ ?code=... หรือ ?search=...']);
