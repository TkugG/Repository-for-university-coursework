<?php
/**
 * Class Note
 * ทำหน้าที่จัดการข้อมูลโน๊ตทั้งหมด (CRUD) เชื่อมต่อกับตาราง notes และ todo_items
 */
class Note {
    // 1. เก็บ Object การเชื่อมต่อฐานข้อมูล
    private $conn;
    private $table = "notes";

    // 2. คุณสมบัติของโน๊ต (ตรงกับคอลัมน์ในตาราง)
    public $id;
    public $category_id;
    public $title;
    public $type;
    public $content;
    public $image;
    public $color;
    public $reminder_at;

    /**
     * Constructor: รับ PDO Connection เข้ามาใช้งาน (Dependency Injection)
     * ทำไมต้องทำแบบนี้? เพื่อไม่ต้องสร้างการเชื่อมต่อใหม่ซ้ำๆ ประหยัดทรัพยากร
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * ดึงโน๊ตทั้งหมด พร้อมรองรับการค้นหา กรองหมวดหมู่ และจัดเรียง
     * @param string $sort 'DESC' (ใหม่สุด) หรือ 'ASC' (เก่าสุด)
     * @param int|null $category_id รหัสหมวดหมู่สำหรับกรอง
     * @param string|null $keyword คำค้นหาหัวข้อ
     * @return array รายการโน๊ต
     */
    public function readAll($sort = 'DESC', $category_id = null, $keyword = null) {
        // ดึงข้อมูลโน๊ตพร้อมชื่อหมวดหมู่ (LEFT JOIN ตาราง categories)
        $query = "SELECT n.*, c.name AS category_name 
                  FROM " . $this->table . " n 
                  LEFT JOIN categories c ON n.category_id = c.id 
                  WHERE 1=1 ";

        $params = [];

        // ถ้ามีการเลือกหมวดหมู่ (จากเมนู Hamburger)
        if (!empty($category_id)) {
            $query .= " AND n.category_id = :category_id ";
            $params[':category_id'] = $category_id;
        }

        // ถ้ามีการค้นหาหัวข้อ (จากช่อง Search)
        if (!empty($keyword)) {
            $query .= " AND n.title LIKE :keyword ";
            $params[':keyword'] = "%" . $keyword . "%";
        }

        // กำหนดการจัดเรียง (เก่าสุด - ใหม่สุด)
        $sortOrder = (strtoupper($sort) === 'ASC') ? 'ASC' : 'DESC';
        $query .= " ORDER BY n.created_at " . $sortOrder;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // หากโน๊ตเป็นแบบ 'todo' ให้ดึงรายการย่อย (todo_items) มาแนบไปด้วย
        foreach ($notes as &$note) {
            if ($note['type'] === 'todo') {
                $itemStmt = $this->conn->prepare("SELECT id, item_text, is_done FROM todo_items WHERE note_id = :note_id");
                $itemStmt->execute([':note_id' => $note['id']]);
                $note['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $note['items'] = [];
            }
        }

        return $notes;
    }

    /**
     * บันทึกโน๊ตใหม่ลงฐานข้อมูล
     * @param array $data ข้อมูลโน๊ต
     * @return int|bool ID ของโน๊ตที่เพิ่มใหม่ หรือ false หากผิดพลาด
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (category_id, title, type, content, image, color, reminder_at) 
                  VALUES (:category_id, :title, :type, :content, :image, :color, :reminder_at)";

        $stmt = $this->conn->prepare($query);

        // Binding ค่าเพื่อป้องกัน SQL Injection
        $stmt->bindValue(':category_id', !empty($data['category_id']) ? $data['category_id'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':type', !empty($data['type']) ? $data['type'] : 'text');
        $stmt->bindValue(':content', !empty($data['content']) ? $data['content'] : null);
        $stmt->bindValue(':image', !empty($data['image']) ? $data['image'] : null);
        $stmt->bindValue(':color', !empty($data['color']) ? $data['color'] : '#ffffff');
        $stmt->bindValue(':reminder_at', !empty($data['reminder_at']) ? $data['reminder_at'] : null);

        if ($stmt->execute()) {
            $noteId = $this->conn->lastInsertId();

            // ถ้าเป็นโน๊ต To-Do และมีรายการย่อยส่งมา ให้บันทึกลงตาราง todo_items ด้วย
            if (!empty($data['type']) && $data['type'] === 'todo' && !empty($data['items']) && is_array($data['items'])) {
                $itemQuery = "INSERT INTO todo_items (note_id, item_text, is_done) VALUES (:note_id, :item_text, 0)";
                $itemStmt = $this->conn->prepare($itemQuery);

                foreach ($data['items'] as $itemText) {
                    if (trim($itemText) !== '') {
                        $itemStmt->execute([
                            ':note_id' => $noteId,
                            ':item_text' => trim($itemText)
                        ]);
                    }
                }
            }

            return $noteId;
        }

        return false;
    }

    /**
     * ลบโน๊ตตามรหัส (ตาราง todo_items จะถูกลบอัตโนมัติจาก ON DELETE CASCADE)
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * ดึงโน๊ต To-Do ที่มีกำหนดแจ้งเตือนในเร็วๆ นี้ (Reminder)
     * @return array
     */
    public function getReminders() {
        $query = "SELECT n.*, c.name AS category_name 
                  FROM " . $this->table . " n 
                  LEFT JOIN categories c ON n.category_id = c.id 
                  WHERE n.type = 'todo' 
                    AND n.reminder_at IS NOT NULL 
                    AND n.reminder_at >= NOW() - INTERVAL 1 HOUR 
                  ORDER BY n.reminder_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($notes as &$note) {
            $itemStmt = $this->conn->prepare("SELECT id, item_text, is_done FROM todo_items WHERE note_id = :note_id");
            $itemStmt->execute([':note_id' => $note['id']]);
            $note['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $notes;
    }
}
