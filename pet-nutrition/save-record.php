<?php
header('Content-Type: application/json');
require_once 'config/db.php';
require_once 'classes/Nutrition.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $nutritionObj = new Nutrition($pdo);
    if ($nutritionObj->saveRecord($data)) {
        echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลลงฐานข้อมูลเรียบร้อยแล้ว!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึก']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูลส่งมา']);
}
?>