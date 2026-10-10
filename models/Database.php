<?php
/**
 * Class Database
 * ทำหน้าที่เชื่อมต่อฐานข้อมูล MySQL ผ่าน PDO เพียงจุดเดียว
 */
class Database {
    // 1. ตั้งค่าการเชื่อมต่อ (ค่าเริ่มต้นของ XAMPP มักเป็น root และไม่มีรหัสผ่าน)
    private $host = "localhost";
    private $db_name = "project_note_db";
    private $username = "root";
    private $password = "";
    private $conn = null;

    /**
     * ฟังก์ชันเปิดการเชื่อมต่อฐานข้อมูล
     * @return PDO|null
     */
    public function connect() {
        $this->conn = null;

        try {
            // Data Source Name (DSN) ระบุชนิดฐานข้อมูล โฮสต์ ชื่อตาราง และ charset
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            // สร้าง Object PDO สำหรับเชื่อมต่อ
            $this->conn = new PDO($dsn, $this->username, $this->password);

            // ตั้งค่าให้แจ้งเตือนข้อผิดพลาดเป็น Exception (เพื่อให้จับบั๊กได้ง่าย)
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // ตั้งค่าให้ดึงข้อมูลออกมาเป็น Associative Array เป็นค่าเริ่มต้น (ใช้ง่าย เช่น $row['title'])
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // หากเชื่อมต่อไม่สำเร็จ ให้แสดงข้อความแจ้งเตือน
            echo "เชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage();
        }

        return $this->conn;
    }
}
