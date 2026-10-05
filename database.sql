-- --------------------------------------------------------
-- Database: project_note_db
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `project_note_db` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `project_note_db`;

-- --------------------------------------------------------
-- 1. ตาราง categories (หมวดหมู่ของโน๊ต)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. ตาราง notes (ข้อมูลโน๊ตหลัก ทั้งแบบข้อความ และ To-do)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NULL,
  `title` VARCHAR(255) NOT NULL,
  `type` ENUM('text', 'todo') NOT NULL DEFAULT 'text',
  `content` TEXT NULL,
  `image` VARCHAR(255) NULL,
  `color` VARCHAR(20) DEFAULT '#ffffff',
  `reminder_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notes_category` 
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. ตาราง todo_items (รายการสิ่งที่ต้องทำย่อยในแต่ละโน๊ต)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `todo_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `note_id` INT NOT NULL,
  `item_text` VARCHAR(255) NOT NULL,
  `is_done` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_todo_note` 
    FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- ข้อมูลตัวอย่างเริ่มต้น (Mock Data) สำหรับทดสอบ
-- --------------------------------------------------------

-- เพิ่มหมวดหมู่เริ่มต้น
INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'ทั่วไป'),
(2, 'การเรียน'),
(3, 'งาน'),
(4, 'ส่วนตัว');

-- เพิ่มตัวอย่างโน๊ตข้อความ (Text Note)
INSERT INTO `notes` (`id`, `category_id`, `title`, `type`, `content`, `color`, `created_at`) VALUES
(1, 2, 'สรุปวิชา OOP บทที่ 1', 'text', 'การเขียนโปรแกรมเชิงวัตถุ ประกอบด้วย Class, Object, Inheritance, Encapsulation และ Polymorphism', '#fef08a', NOW());

-- เพิ่มตัวอย่างโน๊ตสิ่งที่ต้องทำ (To-Do Note)
INSERT INTO `notes` (`id`, `category_id`, `title`, `type`, `color`, `reminder_at`, `created_at`) VALUES
(2, 3, 'สิ่งที่ต้องเตรียมส่งโปรเจกต์', 'todo', '#bbf7d0', DATE_ADD(NOW(), INTERVAL 1 DAY), NOW());

-- เพิ่มรายการย่อยของ To-Do
INSERT INTO `todo_items` (`note_id`, `item_text`, `is_done`) VALUES
(2, 'ออกแบบ Database และตาราง', 1),
(2, 'สร้าง UI หน้าแรกแบบ Responsive', 0),
(2, 'เชื่อมต่อ API ด้วย Fetch', 0);
