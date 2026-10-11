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

        // ถ้ามีการเลือกหมวดหมู่ (จากเมนู Hamburger หรือ Sidebar)
        if (!empty($category_id)) {
            $query .= " AND n.category_id = :category_id ";
            $params[':category_id'] = $category_id;
        }

        // ถ้ามีการค้นหาหัวข้อ (จากช่อง Search)
        if (!empty($keyword)) {
            $query .= " AND (n.title LIKE :keyword OR n.content LIKE :keyword) ";
            $params[':keyword'] = "%" . $keyword . "%";
        }

        // กำหนดการจัดเรียง (เก่าสุด - ใหม่สุด)
        $sortOrder = (strtoupper($sort) === 'ASC') ? 'ASC' : 'DESC';
        $query .= " ORDER BY n.created_at " . $sortOrder;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // หากโน๊ตเป็นแบบ 'todo' ให้ดึงรายการย่อยมาแนบทั้ง items และ todo_items เพื่อรองรับ Frontend
        foreach ($notes as &$note) {
            if ($note['type'] === 'todo') {
                $itemStmt = $this->conn->prepare("SELECT id, item_text, is_done FROM todo_items WHERE note_id = :note_id");
                $itemStmt->execute([':note_id' => $note['id']]);
                $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
                $note['items'] = $items;
                $note['todo_items'] = $items; // รองรับ Frontend ที่เรียกหา todo_items
            } else {
                $note['items'] = [];
                $note['todo_items'] = [];
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

            // รองรับทั้ง items (Array) และ todo_items (Array หรือ JSON String)
            $todoItems = [];
            if (!empty($data['todo_items'])) {
                $todoItems = is_array($data['todo_items']) ? $data['todo_items'] : json_decode($data['todo_items'], true);
            } elseif (!empty($data['items'])) {
                $todoItems = is_array($data['items']) ? $data['items'] : json_decode($data['items'], true);
            }

            if (!empty($data['type']) && $data['type'] === 'todo' && !empty($todoItems) && is_array($todoItems)) {
                $itemQuery = "INSERT INTO todo_items (note_id, item_text, is_done) VALUES (:note_id, :item_text, 0)";
                $itemStmt = $this->conn->prepare($itemQuery);

                foreach ($todoItems as $item) {
                    $itemText = is_array($item) ? ($item['item_text'] ?? '') : $item;
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
     * แก้ไขโน๊ตเดิม (Update)
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET category_id = :category_id,
                      title = :title,
                      type = :type,
                      content = :content,
                      color = :color,
                      reminder_at = :reminder_at ";

        if (!empty($data['image'])) {
            $query .= ", image = :image ";
        }
        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':category_id', !empty($data['category_id']) ? $data['category_id'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':type', !empty($data['type']) ? $data['type'] : 'text');
        $stmt->bindValue(':content', !empty($data['content']) ? $data['content'] : null);
        $stmt->bindValue(':color', !empty($data['color']) ? $data['color'] : '#ffffff');
        $stmt->bindValue(':reminder_at', !empty($data['reminder_at']) ? $data['reminder_at'] : null);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if (!empty($data['image'])) {
            $stmt->bindValue(':image', $data['image']);
        }

        $success = $stmt->execute();

        // อัปเดตข้อย่อยของ To-Do ถ้ามีส่งมา
        if ($success && !empty($data['type']) && $data['type'] === 'todo') {
            $todoItems = [];
            if (!empty($data['todo_items'])) {
                $todoItems = is_array($data['todo_items']) ? $data['todo_items'] : json_decode($data['todo_items'], true);
            } elseif (!empty($data['items'])) {
                $todoItems = is_array($data['items']) ? $data['items'] : json_decode($data['items'], true);
            }

            if (!empty($todoItems) && is_array($todoItems)) {
                // ลบรายการเดิมออกแล้วบันทึกใหม่
                $delStmt = $this->conn->prepare("DELETE FROM todo_items WHERE note_id = :note_id");
                $delStmt->execute([':note_id' => $id]);

                $insStmt = $this->conn->prepare("INSERT INTO todo_items (note_id, item_text, is_done) VALUES (:note_id, :item_text, 0)");
                foreach ($todoItems as $item) {
                    $itemText = is_array($item) ? ($item['item_text'] ?? '') : $item;
                    if (trim($itemText) !== '') {
                        $insStmt->execute([
                            ':note_id' => $id,
                            ':item_text' => trim($itemText)
                        ]);
                    }
                }
            }
        }

        return $success;
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
                    AND n.reminder_at <= NOW() + INTERVAL 1 DAY 
                  ORDER BY n.reminder_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($notes as &$note) {
            $itemStmt = $this->conn->prepare("SELECT id, item_text, is_done FROM todo_items WHERE note_id = :note_id");
            $itemStmt->execute([':note_id' => $note['id']]);
            $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
            $note['items'] = $items;
            $note['todo_items'] = $items;
        }

        return $notes;
    }
}
