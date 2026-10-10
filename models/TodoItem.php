<?php
/**
 * Class TodoItem
 * จัดการรายการย่อยของสิ่งที่ต้องทำ (ติ๊กถูก / เพิ่ม / ลบ)
 */
class TodoItem {
    private $conn;
    private $table = "todo_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * เปลี่ยนสถานะการทำงาน (0 = ยังไม่เสร็จ, 1 = เสร็จแล้ว)
     * @param int $id รหัสรายการ
     * @param int $is_done สถานะ (0 หรือ 1)
     * @return bool
     */
    public function toggle($id, $is_done) {
        $query = "UPDATE " . $this->table . " SET is_done = :is_done WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':is_done', $is_done ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * เพิ่มรายการย่อยใหม่เข้าในโน๊ตเดิม
     * @param int $note_id
     * @param string $item_text
     * @return int|bool ID ที่เพิ่มใหม่ หรือ false
     */
    public function create($note_id, $item_text) {
        $query = "INSERT INTO " . $this->table . " (note_id, item_text, is_done) VALUES (:note_id, :item_text, 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':note_id', $note_id, PDO::PARAM_INT);
        $stmt->bindValue(':item_text', trim($item_text));
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * ลบรายการย่อย
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
