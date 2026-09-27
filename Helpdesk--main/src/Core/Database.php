<?php

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private string $driver = 'mysql';

    private function __construct()
    {
        $connection = Config::get('DB_CONNECTION', 'mysql');

        if ($connection === 'sqlite') {
            $this->connectSqlite();
            return;
        }

        // Attempt MySQL connection
        $host = Config::get('DB_HOST', '127.0.0.1');
        $port = Config::get('DB_PORT', '3306');
        $database = Config::get('DB_DATABASE', 'it_helpdesk');
        $username = Config::get('DB_USERNAME', 'root');
        $password = Config::get('DB_PASSWORD', '');

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
            $this->driver = 'mysql';
        } catch (PDOException $e) {
            // If MySQL database doesn't exist yet, attempt to create it if root user
            if (str_contains($e->getMessage(), 'Unknown database')) {
                try {
                    $rootDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                    $rootPdo = new PDO($rootDsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                    $this->pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                    $this->driver = 'mysql';
                    $this->runInitialMigration();
                    return;
                } catch (Exception) {
                    // Fall back to SQLite below
                }
            }

            // If MySQL server is not running or unreachable, fallback to SQLite so the app can still run immediately
            error_log("MySQL connection failed: " . $e->getMessage() . ". Falling back to local SQLite database.");
            $this->connectSqlite();
        }
    }

    private function connectSqlite(): void
    {
        $sqliteRelPath = Config::get('DB_SQLITE_PATH', 'storage/database.sqlite');
        $sqlitePath = dirname(__DIR__, 2) . '/' . $sqliteRelPath;

        $dir = dirname($sqlitePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $isNew = !file_exists($sqlitePath) || filesize($sqlitePath) === 0;

        $this->pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->pdo->exec('PRAGMA foreign_keys = ON;');
        $this->pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));
        $this->pdo->sqliteCreateFunction('GREATEST', fn(...$args) => max($args));
        $this->pdo->sqliteCreateFunction('LEAST', fn(...$args) => min($args));
        $this->driver = 'sqlite';

        // Check if full schema is present
        $hasSpareParts = false;
        try {
            $stmt = $this->pdo->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='spare_parts'");
            $hasSpareParts = (bool)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $hasSpareParts = false;
        }

        if ($isNew || !$hasSpareParts) {
            $this->initSqliteSchema();
        }
    }

    private function initSqliteSchema(): void
    {
        $sql = "
        DROP TABLE IF EXISTS ratings;
        DROP TABLE IF EXISTS comments;
        DROP TABLE IF EXISTS ticket_parts_used;
        DROP TABLE IF EXISTS ticket_repair_logs;
        DROP TABLE IF EXISTS status_logs;
        DROP TABLE IF EXISTS tickets;
        DROP TABLE IF EXISTS spare_parts;
        DROP TABLE IF EXISTS categories;
        DROP TABLE IF EXISTS users;

        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(191) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role TEXT CHECK(role IN ('user', 'technician', 'admin')) NOT NULL DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(150) NOT NULL,
            description TEXT
        );

        CREATE TABLE spare_parts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            part_code VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(150) NOT NULL,
            stock_quantity INTEGER NOT NULL DEFAULT 0,
            unit_price REAL NOT NULL DEFAULT 0.00,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE tickets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            category_id INTEGER NOT NULL,
            technician_id INTEGER,
            title VARCHAR(255) NOT NULL,
            location VARCHAR(255) NOT NULL DEFAULT 'อาคารหลัก',
            description TEXT NOT NULL,
            priority TEXT CHECK(priority IN ('low', 'normal', 'high', 'urgent')) NOT NULL DEFAULT 'normal',
            status TEXT CHECK(status IN ('pending_approval', 'approved', 'assigned', 'en_route', 'in_progress', 'waiting_parts', 'resolved', 'closed', 'cancelled')) NOT NULL DEFAULT 'pending_approval',
            sla_due_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            resolved_at DATETIME,
            closed_at DATETIME,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
            FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE SET NULL
        );

        CREATE TABLE ticket_repair_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ticket_id INTEGER NOT NULL UNIQUE,
            root_cause TEXT NOT NULL,
            solution_note TEXT NOT NULL,
            actual_hours REAL NOT NULL DEFAULT 1.00,
            proof_image_path VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
        );

        CREATE TABLE ticket_parts_used (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ticket_id INTEGER NOT NULL,
            part_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 1,
            status TEXT CHECK(status IN ('requested', 'approved', 'rejected')) NOT NULL DEFAULT 'requested',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
            FOREIGN KEY (part_id) REFERENCES spare_parts(id) ON DELETE RESTRICT
        );

        CREATE TABLE status_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ticket_id INTEGER NOT NULL,
            changed_by INTEGER NOT NULL,
            from_status VARCHAR(50) NOT NULL,
            to_status VARCHAR(50) NOT NULL,
            note TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
            FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ticket_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            body TEXT NOT NULL,
            image_path VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE ratings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ticket_id INTEGER NOT NULL UNIQUE,
            score INTEGER NOT NULL CHECK (score BETWEEN 1 AND 5),
            feedback TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
        );

        -- Seed Default Users
        INSERT INTO users (id, name, email, password_hash, role) VALUES
        (1, 'ผู้จัดการฝ่ายซ่อมบำรุง', 'admin@helpdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
        (2, 'สมชาย ช่างเทคนิคอาคารและไอที', 'tech1@helpdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician'),
        (3, 'วิชัย ช่างไฟและระบบเครือข่าย', 'tech2@helpdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician'),
        (4, 'สมศรี ผู้ใช้งานทั่วไป', 'user@helpdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
        (5, 'อนันต์ เจ้าหน้าที่การตลาด', 'anan@helpdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

        -- Seed Categories
        INSERT INTO categories (id, name, description) VALUES
        (1, 'งานอาคารและระบบปรับอากาศ', 'ระบบปรับอากาศ, ไฟฟ้าส่องสว่าง, พัดลมระบายอากาศ, ประตูและหน้าต่าง'),
        (2, 'อุปกรณ์คอมพิวเตอร์และสำนักงาน', 'คอมพิวเตอร์ตั้งโต๊ะ, จอแสดงผล, เครื่องพิมพ์, เครื่องสแกนเนอร์'),
        (3, 'ระบบเครือข่ายและการสื่อสาร', 'สัญญาณอินเทอร์เน็ตไร้สาย, สายแลน, ระบบเครือข่าย, โทรศัพท์ภายใน'),
        (4, 'งานระบบประปาและสุขภัณฑ์', 'ระบบท่อน้ำดีน้ำทิ้ง, ก๊อกน้ำ, วาล์วน้ำ, ปั๊มน้ำ, สุขภัณฑ์'),
        (5, 'โปรแกรมและสิทธิ์การเข้าใช้งาน', 'ระบบงานองค์กร, บัญชีผู้ใช้งาน, อีเมล, ขอสิทธิ์โฟลเดอร์แชร์');

        -- Seed Spare Parts
        INSERT INTO spare_parts (id, part_code, name, stock_quantity, unit_price) VALUES
        (1, 'AC-CAP-45', 'คาปาซิเตอร์แอร์ 45 ไมโครฟารัด', 15, 350.00),
        (2, 'LED-TUBE-18W', 'หลอดไฟแอลอีดี T8 ขนาด 18 วัตต์ แสงขาว', 50, 120.00),
        (3, 'RAM-DDR4-8G', 'แรม DDR4 ขนาด 8GB ความเร็ว 3200MHz', 20, 890.00),
        (4, 'SSD-500G-M2', 'โซลิดสเตตไดรฟ์ M.2 NVMe ขนาด 500GB', 12, 1450.00),
        (5, 'LAN-CAT6-PATCH', 'สายแลนสำเร็จรูป Cat6 ความยาว 3 เมตร', 40, 85.00),
        (6, 'VALVE-BRASS-12', 'สต็อปวาล์วน้ำทองเหลือง ขนาด 4 หุน', 25, 180.00),
        (7, 'FUSE-THERMAL-10A', 'ฟิวส์ความร้อน 10 แอมป์ 250 โวลต์', 30, 45.00);

        -- Seed Sample Tickets
        INSERT INTO tickets (id, user_id, category_id, technician_id, title, location, description, priority, status, sla_due_at, created_at, updated_at) VALUES
        (1, 4, 1, NULL, 'เครื่องปรับอากาศห้องประชุมใหญ่ชั้น 2 มีน้ำหยดและไม่เย็น', 'อาคาร A ชั้น 2 ห้องประชุม 201', 'เปิดใช้งานช่วงเช้าพบน้ำแอร์หยดลงบนโต๊ะประชุม และอุณหภูมิไม่ลดลง รบกวนรีบเข้าตรวจสอบก่อนช่วงบ่ายครับ', 'high', 'pending_approval', datetime('now', '+8 hours'), datetime('now', '-1 hour'), datetime('now')),
        (2, 5, 2, 2, 'เครื่องคอมพิวเตอร์เปิดไม่ติด มีไฟกระพริบสีส้ม', 'อาคาร B ชั้น 3 แผนกการเงิน โต๊ะ 14', 'กดปุ่มเปิดเครื่องแล้วพัดลมหมุนสั้นๆ แล้วดับ มีเสียงแจ้งเตือน 3 ครั้ง ไม่สามารถเปิดทำงานได้', 'urgent', 'assigned', datetime('now', '+3 hours'), datetime('now', '-2 hours'), datetime('now')),
        (3, 4, 3, 3, 'สัญญาณอินเทอร์เน็ตไร้สายหลุดบ่อยทั้งชั้น 4', 'อาคาร A ชั้น 4 พื้นที่ทำงานส่วนกลาง', 'พนักงานประมาณ 10 คนไม่สามารถเชื่อมต่อสัญญาณอินเทอร์เน็ตได้ สัญญาณขาดหายต่อเนื่อง', 'urgent', 'en_route', datetime('now', '+2 hours'), datetime('now', '-3 hours'), datetime('now')),
        (4, 5, 1, 2, 'เบรกเกอร์เครื่องปรับอากาศตัดการทำงานบ่อยครั้ง', 'อาคาร A ชั้น 3 แผนกบุคคล', 'เมื่อเปิดแอร์ตัวที่ 3 สวิตช์เบรกเกอร์จะตัดไฟลงทันที คาดว่ามีกระแสไฟฟ้ารั่วหรือใช้งานไฟเกิน', 'high', 'in_progress', datetime('now', '+5 hours'), datetime('now', '-5 hours'), datetime('now')),
        (5, 4, 2, 2, 'ฮาร์ดดิสก์เครื่องแม่ข่ายสำรองข้อมูลทำงานช้ามาก', 'ห้องเซิร์ฟเวอร์ ชั้นใต้ดิน B1 ตู้แร็ค 02', 'ถอดตรวจสอบพบไดรฟ์มีข้อผิดพลาด จำเป็นต้องเปลี่ยนไดรฟ์ความเร็วสูงตัวใหม่', 'normal', 'waiting_parts', datetime('now', '+18 hours'), datetime('now', '-1 day'), datetime('now')),
        (6, 5, 4, 2, 'ก๊อกน้ำอ่างล้างมือห้องน้ำชายชั้น 1 น้ำรั่วซึมตลอดเวลา', 'อาคาร B ชั้น 1 ห้องน้ำชาย', 'ทำการเปลี่ยนสต็อปวาล์วทองเหลืองตัวใหม่ ทดสอบเปิดปิดน้ำแล้ว ไม่พบการรั่วซึม', 'normal', 'resolved', datetime('now', '-2 hours'), datetime('now', '-2 days'), datetime('now')),
        (7, 4, 2, 2, 'หน้าจอคอมพิวเตอร์กระพริบเป็นเส้นและดับเป็นช่วงๆ', 'อาคาร A ชั้น 2 แผนกจัดซื้อ โต๊ะ 08', 'ทำการเปลี่ยนสายสัญญาณเชื่อมต่อจอภาพใหม่และทดสอบการแสดงผลเรียบร้อย ใช้งานได้ตามปกติ', 'low', 'closed', datetime('now', '-1 day'), datetime('now', '-4 days'), datetime('now'));

        -- Seed Parts Requests / Used
        INSERT INTO ticket_parts_used (ticket_id, part_id, quantity, status, created_at) VALUES
        (5, 4, 1, 'requested', datetime('now', '-3 hours')),
        (6, 6, 1, 'approved', datetime('now', '-4 hours')),
        (7, 5, 1, 'approved', datetime('now', '-3 days'));

        -- Seed Repair Logs
        INSERT INTO ticket_repair_logs (ticket_id, root_cause, solution_note, actual_hours, proof_image_path, created_at) VALUES
        (6, 'ซีลยางสต็อปวาล์วเดิมเสื่อมสภาพตามอายุการใช้งาน ทำให้ปิดน้ำไม่อยู่', 'ทำการปิดวาล์วหลัก รื้อถอนวาล์วเดิมออก และติดตั้งสต็อปวาล์วทองเหลืองขนาด 4 หุนตัวใหม่ พร้อมพันเทปเกลียวอย่างแน่นหนา ทดสอบแรงดันน้ำเรียบร้อยไม่รั่วซึม', 1.50, 'uploads/proof_sample_plumbing.jpg', datetime('now', '-2 hours')),
        (7, 'พอร์ตเชื่อมต่อสายสัญญาณด้านหลังการ์ดจอหลวมและมีฝุ่นสะสม', 'เป่าทำความสะอาดฝุ่นที่พอร์ต และเปลี่ยนสายสัญญาณเส้นใหม่ ทดสอบเปิดใช้งานต่อเนื่อง 2 ชั่วโมง ไม่พบอาการกระพริบ', 0.75, 'uploads/proof_sample_pc.jpg', datetime('now', '-2 days'));

        -- Seed Status Logs
        INSERT INTO status_logs (ticket_id, changed_by, from_status, to_status, note, created_at) VALUES
        (1, 4, 'none', 'pending_approval', 'ผู้แจ้งสร้างใบแจ้งซ่อมในระบบ', datetime('now', '-1 hour')),
        (2, 5, 'none', 'pending_approval', 'ผู้แจ้งสร้างใบแจ้งซ่อม', datetime('now', '-2 hours')),
        (2, 1, 'pending_approval', 'assigned', 'หัวหน้าช่างอนุมัติและมอบหมายงานให้ สมชาย ช่างเทคนิคอาคารและไอที', datetime('now', '-90 minutes')),
        (3, 4, 'none', 'pending_approval', 'ผู้แจ้งสร้างใบแจ้งซ่อม', datetime('now', '-3 hours')),
        (3, 1, 'pending_approval', 'assigned', 'มอบหมายงานให้ วิชัย ช่างไฟและระบบเครือข่าย', datetime('now', '-2 hours')),
        (3, 3, 'assigned', 'en_route', 'ช่างกดเริ่มเดินทางไปยังหน้างาน อาคาร A ชั้น 4', datetime('now', '-30 minutes')),
        (4, 5, 'none', 'pending_approval', 'ผู้แจ้งสร้างใบแจ้งซ่อม', datetime('now', '-5 hours')),
        (4, 1, 'pending_approval', 'assigned', 'มอบหมายงานให้ สมชาย', datetime('now', '-4 hours')),
        (4, 2, 'assigned', 'en_route', 'ช่างเดินทางไปหน้างาน', datetime('now', '-3 hours')),
        (4, 2, 'en_route', 'in_progress', 'ถึงหน้างานและเริ่มใช้เครื่องมือวัดกระแสไฟฟ้าตรวจสอบเบรกเกอร์', datetime('now', '-2 hours')),
        (5, 4, 'none', 'pending_approval', 'ผู้แจ้งสร้างใบแจ้งซ่อม', datetime('now', '-1 day')),
        (5, 1, 'pending_approval', 'assigned', 'มอบหมายงานให้ สมชาย', datetime('now', '-20 hours')),
        (5, 2, 'assigned', 'in_progress', 'เข้าตรวจสอบสภาพไดรฟ์', datetime('now', '-6 hours')),
        (5, 2, 'in_progress', 'waiting_parts', 'ขอเบิกโซลิดสเตตไดรฟ์ M.2 NVMe ขนาด 500GB จำนวน 1 ชิ้น', datetime('now', '-3 hours')),
        (6, 5, 'in_progress', 'resolved', 'เปลี่ยนวาล์วน้ำตัวใหม่และแนบรูปผลงานเรียบร้อย ส่งมอบงานให้ผู้แจ้ง', datetime('now', '-2 hours')),
        (7, 4, 'resolved', 'closed', 'ผู้แจ้งกดยืนยันปิดงานและให้คะแนนการบริการ 5 ดาว', datetime('now', '-1 day'));

        -- Seed Comments
        INSERT INTO comments (ticket_id, user_id, body, image_path, created_at) VALUES
        (1, 4, 'แนบรูปภาพคราบน้ำที่หยดลงบนโต๊ะประชุมครับ', NULL, datetime('now', '-50 minutes')),
        (3, 3, 'กำลังนำเครื่องวัดสัญญาณและอุปกรณ์สำรองขึ้นไปตรวจสอบที่ชั้น 4 ครับ', NULL, datetime('now', '-25 minutes')),
        (5, 2, 'ส่งคำขอเบิกอะไหล่ให้หัวหน้าช่างพิจารณาแล้วครับ หากอนุมัติจะรีบเข้าเปลี่ยนทันที', NULL, datetime('now', '-3 hours')),
        (7, 4, 'ช่างมาซ่อมแซมได้รวดเร็วและเรียบร้อยมากครับ ขอบคุณครับ', NULL, datetime('now', '-1 day'));

        -- Seed Ratings
        INSERT INTO ratings (ticket_id, score, feedback, created_at) VALUES
        (7, 5, 'บริการรวดเร็วมาก ช่างตรงต่อเวลาและแก้ปัญหาได้ตรงจุด ประทับใจมากครับ', datetime('now', '-1 day'));
        ";

        $this->pdo->exec($sql);
    }

    private function runInitialMigration(): void
    {
        $schemaFile = dirname(__DIR__, 2) . '/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $this->pdo->exec($sql);
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId(): string|int
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}
