<?php
// 1. ตั้งค่า Header ให้รองรับการส่งข้อมูลกลับเป็น JSON และ CORS
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// จัดการกรณี Browser ส่งคำขอแบบ OPTIONS (Preflight Request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. โหลด Model ที่จำเป็นเข้ามาใช้งาน
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Note.php';
require_once __DIR__ . '/../models/TodoItem.php';

// สร้างการเชื่อมต่อฐานข้อมูล
$database = new Database();
$db = $database->connect();

if (!$db) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "ไม่สามารถเชื่อมต่อฐานข้อมูลได้"]);
    exit();
}

$note = new Note($db);
$todoItem = new TodoItem($db);
$method = $_SERVER['REQUEST_METHOD'];

// 3. จัดการคำขอตาม HTTP Method
switch ($method) {
    case 'GET':
        // กรณีดึงเฉพาะโน๊ตที่มีการแจ้งเตือน (Reminder)
        if (isset($_GET['reminders'])) {
            $reminders = $note->getReminders();
            echo json_encode([
                "success" => true,
                "count" => count($reminders),
                "data" => $reminders
            ], JSON_UNESCAPED_UNICODE);
            break;
        }

        // ดึงพารามิเตอร์ (รองรับทั้ง ?keyword= และ ?search= ของหน้าบ้าน)
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'DESC';
        $categoryId = !empty($_GET['category_id']) ? intval($_GET['category_id']) : null;
        $keyword = !empty($_GET['keyword']) ? trim($_GET['keyword']) : (!empty($_GET['search']) ? trim($_GET['search']) : null);

        $notes = $note->readAll($sort, $categoryId, $keyword);

        echo json_encode([
            "success" => true,
            "count" => count($notes),
            "data" => $notes
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'POST':
        // รวมข้อมูลที่ส่งมาจากทั้ง FormData, JSON body และ $_POST
        $data = $_POST;
        if (empty($data)) {
            $input = json_decode(file_get_contents('php://input'), true);
            $data = is_array($input) ? $input : [];
        }

        // ตรวจสอบ action ที่หน้าบ้านอาจส่งมาทาง URL หรือ Body
        $action = isset($_GET['action']) ? $_GET['action'] : (isset($data['action']) ? $data['action'] : null);

        // --- Action 1: ติ๊กถูก To-Do Item (toggle_todo) ---
        if ($action === 'toggle_todo') {
            $itemId = isset($data['item_id']) ? intval($data['item_id']) : (isset($data['id']) ? intval($data['id']) : null);
            $isDone = isset($data['is_done']) ? intval($data['is_done']) : 0;

            if (!$itemId) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "ไม่พบรหัสรายการ (item_id)"]);
                exit();
            }

            $success = $todoItem->toggle($itemId, $isDone);
            echo json_encode(["success" => $success, "message" => $success ? "อัปเดตสถานะสำเร็จ" : "อัปเดตล้มเหลว"]);
            exit();
        }

        // --- Action 2: ลบโน๊ตผ่าน POST (delete) ---
        if ($action === 'delete') {
            $id = isset($_GET['id']) ? intval($_GET['id']) : (isset($data['id']) ? intval($data['id']) : null);
            if (!$id) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "กรุณาระบุรหัสโน๊ตที่ต้องการลบ (id)"]);
                exit();
            }

            $success = $note->delete($id);
            echo json_encode(["success" => $success, "message" => $success ? "ลบโน๊ตเรียบร้อยแล้ว" : "ไม่สามารถลบโน๊ตได้"]);
            exit();
        }

        // --- จัดการไฟล์รูปภาพ (ถ้ามี) ---
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid('note_', true) . '.' . strtolower($fileExt);
            $targetPath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imageName = $newFileName;
            }
        }
        if ($imageName) {
            $data['image'] = $imageName;
        }

        // --- Action 3: แก้ไขโน๊ตเดิม (update) ---
        if ($action === 'update' || !empty($data['id'])) {
            $id = !empty($data['id']) ? intval($data['id']) : (!empty($_GET['id']) ? intval($_GET['id']) : null);
            if (!$id) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "ไม่พบรหัสโน๊ตที่จะแก้ไข"]);
                exit();
            }

            $success = $note->update($id, $data);
            echo json_encode([
                "success" => $success,
                "message" => $success ? "แก้ไขโน๊ตสำเร็จ" : "เกิดข้อผิดพลาดในการแก้ไข",
                "id" => $id
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // --- Action 4: สร้างโน๊ตใหม่ (Create) ---
        if (empty($data['title'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "กรุณาระบุหัวข้อโน๊ต (title)"], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $newId = $note->create($data);

        if ($newId) {
            http_response_code(201);
            echo json_encode([
                "success" => true,
                "message" => "บันทึกโน๊ตสำเร็จ",
                "id" => $newId
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาดในการบันทึกโน๊ต"], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'DELETE':
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        if (!$id) {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? intval($input['id']) : null;
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "กรุณาระบุรหัสโน๊ตที่ต้องการลบ (id)"], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($note->delete($id)) {
            echo json_encode(["success" => true, "message" => "ลบโน๊ตเรียบร้อยแล้ว"], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "ไม่สามารถลบโน๊ตได้"], JSON_UNESCAPED_UNICODE);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Method Not Allowed"], JSON_UNESCAPED_UNICODE);
        break;
}
