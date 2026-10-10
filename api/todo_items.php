<?php
// ตั้งค่า Header JSON และ CORS
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/TodoItem.php';

$database = new Database();
$db = $database->connect();

if (!$db) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "เชื่อมต่อฐานข้อมูลไม่สำเร็จ"]);
    exit();
}

$todoItem = new TodoItem($db);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // รับข้อมูลจาก JSON หรือ FormData
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }

        // กรณีที่ 1: ติ๊กถูก / ยกเลิกติ๊กถูก (Toggle Done)
        if (isset($data['action']) && $data['action'] === 'toggle') {
            if (!isset($data['id']) || !isset($data['is_done'])) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "กรุณาระบุ id และ is_done"]);
                exit();
            }

            $success = $todoItem->toggle(intval($data['id']), intval($data['is_done']));
            echo json_encode(["success" => $success, "message" => $success ? "อัปเดตสถานะสำเร็จ" : "อัปเดตล้มเหลว"]);
            exit();
        }

        // กรณีที่ 2: เพิ่มข้อใหม่ในโน๊ตเดิม
        if (isset($data['note_id']) && !empty($data['item_text'])) {
            $newId = $todoItem->create(intval($data['note_id']), $data['item_text']);
            if ($newId) {
                http_response_code(201);
                echo json_encode(["success" => true, "id" => $newId, "message" => "เพิ่มรายการสำเร็จ"]);
            } else {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "เพิ่มรายการไม่สำเร็จ"]);
            }
            exit();
        }

        http_response_code(400);
        echo json_encode(["success" => false, "message" => "ข้อมูลไม่ถูกต้อง"]);
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        if (!$id) {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? intval($input['id']) : null;
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "กรุณาระบุ id ที่ต้องการลบ"]);
            exit();
        }

        $success = $todoItem->delete($id);
        echo json_encode(["success" => $success, "message" => $success ? "ลบรายการสำเร็จ" : "ลบรายการล้มเหลว"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method Not Allowed"]);
        break;
}
