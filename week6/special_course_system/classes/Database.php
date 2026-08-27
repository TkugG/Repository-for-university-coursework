<?php
/**
 * Database — Singleton wrapper รอบ PDO
 *
 * การใช้งาน:
 *   $pdo = Database::getInstance()->getConnection();
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
        } catch (PDOException $e) {
            // บันทึก error จริง แต่ไม่แสดง detail ให้ผู้ใช้
            error_log('[DB] Connection failed: ' . $e->getMessage());
            http_response_code(503);
            exit('เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล กรุณาลองใหม่อีกครั้ง');
        }
    }

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    // ป้องกัน clone และ unserialize เพื่อรักษา Singleton
    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \RuntimeException('Cannot unserialize a singleton.');
    }
}
