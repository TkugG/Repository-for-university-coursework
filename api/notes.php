<?php
// 1. ตั้งค่า Header ให้รองรับการส่งข้อมูลกลับเป็น JSON
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

// สร้างการเชื่อมต่อฐานข้อมูลและส่งให้ Note Model
$database = new Database();
$db = $database->connect();

if (!$db) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "ไม่สามารถเชื่อมต่อฐานข้อมูลได้"]);
    exit();
}

$note = new Note($db);
$method = $_SERVER['REQUEST_METHOD'];

// 3. จัดการคำขอตาม HTTP Method
switch ($method) {
    case 'GET':
        // ดึงพารามิเตอร์จาก URL เช่น ?sort=ASC&category_id=2&keyword=งาน
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'DESC';
        $categoryId = !empty($_GET['category_id']) ? intval($_GET['category_id']) : null;
        $keyword = !empty($_GET['keyword']) ? trim($_GET['keyword']) : null;

        $notes = $note->readAll($sort, $categoryId, $keyword);

        echo json_encode([
            "success" => true,
            "count" => count($notes),
            "data" => $notes
        ], JSON_UNESCAPED_UNICODE);
        break;

    case 'POST':
        // รองรับทั้งข้อมูลที่ส่งมาจาก FormData (มีไฟล์รูป) และแบบ JSON
        $data = $_POST;
        if (empty($data)) {
            $input = json_decode(file_get_contents('php://input'), true);
            $data = is_array($input) ? $input : [];
        }

        // ตรวจสอบว่ามีหัวข้อหรือไม่ (จำเป็นต้องมี)
        if (empty($data['title'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "กรุณาระบุหัวข้อโน๊ต (title)"], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // จัดการอัปโหลดรูปภาพ (ถ้ามีไฟล์ส่งมา)
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
        $data['image'] = $imageName;

        // บันทึกโน๊ต
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
        // รับ id จาก URL เช่น ?id=1 หรือจาก JSON Body
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
