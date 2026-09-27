-- ==========================================================
-- SMART IT & FACILITY HELPDESK SYSTEM
-- Database Schema for MySQL 8.0+ / MariaDB (phpMyAdmin)
-- Charset: utf8mb4 (Full Unicode / Thai Support)
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `it_helpdesk` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `it_helpdesk`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `ratings`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `status_logs`;
DROP TABLE IF EXISTS `ticket_repair_logs`;
DROP TABLE IF EXISTS `ticket_parts_used`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `spare_parts`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------
-- 1. Table: users
-- ----------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'technician', 'admin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_role` (`role`),
  INDEX `idx_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. Table: categories
-- ----------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. Table: spare_parts (คลังอะไหล่และอุปกรณ์ซ่อมบำรุง)
-- ----------------------------------------------------------
CREATE TABLE `spare_parts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `part_code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_part_code` (`part_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. Table: tickets (ใบแจ้งซ่อม / Work Order)
-- ----------------------------------------------------------
CREATE TABLE `tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `technician_id` INT NULL,
  `title` VARCHAR(255) NOT NULL,
  `location` VARCHAR(255) NOT NULL DEFAULT 'อาคารหลัก',
  `description` LONGTEXT NOT NULL,
  `priority` ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
  `status` ENUM('pending_approval', 'approved', 'assigned', 'en_route', 'in_progress', 'waiting_parts', 'resolved', 'closed', 'cancelled') NOT NULL DEFAULT 'pending_approval',
  `sla_due_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resolved_at` DATETIME NULL,
  `closed_at` DATETIME NULL,
  CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tickets_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_tickets_technician` FOREIGN KEY (`technician_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_ticket_status` (`status`),
  INDEX `idx_ticket_priority` (`priority`),
  INDEX `idx_ticket_user` (`user_id`),
  INDEX `idx_ticket_technician` (`technician_id`),
  INDEX `idx_ticket_sla` (`sla_due_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 5. Table: ticket_repair_logs (บันทึกผลการซ่อมและสาเหตุของปัญหา)
-- ----------------------------------------------------------
CREATE TABLE `ticket_repair_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL UNIQUE,
  `root_cause` TEXT NOT NULL,
  `solution_note` TEXT NOT NULL,
  `actual_hours` DECIMAL(5,2) NOT NULL DEFAULT 1.00,
  `proof_image_path` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_repair_logs_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 6. Table: ticket_parts_used (รายการเบิกใช้อะไหล่ในใบแจ้งซ่อม)
-- ----------------------------------------------------------
CREATE TABLE `ticket_parts_used` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `part_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `status` ENUM('requested', 'approved', 'rejected') NOT NULL DEFAULT 'requested',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_parts_used_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_parts_used_part` FOREIGN KEY (`part_id`) REFERENCES `spare_parts`(`id`) ON DELETE RESTRICT,
  INDEX `idx_parts_ticket` (`ticket_id`),
  INDEX `idx_parts_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 7. Table: status_logs (ประวัติไทม์ไลน์การเปลี่ยนสถานะงาน)
-- ----------------------------------------------------------
CREATE TABLE `status_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `changed_by` INT NOT NULL,
  `from_status` VARCHAR(50) NOT NULL,
  `to_status` VARCHAR(50) NOT NULL,
  `note` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_logs_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_logs_user` FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_logs_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 8. Table: comments (การพูดคุยสนทนาและรูปภาพประกอบ)
-- ----------------------------------------------------------
CREATE TABLE `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `body` TEXT NOT NULL,
  `image_path` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_comments_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_comments_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 9. Table: ratings (การประเมินความพึงพอใจ 1-5 ดาว)
-- ----------------------------------------------------------
CREATE TABLE `ratings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL UNIQUE,
  `score` TINYINT UNSIGNED NOT NULL CHECK (`score` BETWEEN 1 AND 5),
  `feedback` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ratings_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==========================================================
-- SEED DATA (ข้อมูลเริ่มต้นและตัวอย่างการทดสอบระบบ)
-- รหัสผ่านเริ่มต้นทุกบัญชีคือ: password123
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ==========================================================

-- 1. ผู้ใช้งานเริ่มต้น
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'ผู้จัดการฝ่ายซ่อมบำรุง', 'admin@helpdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()),
(2, 'สมชาย ช่างเทคนิคอาคารและไอที', 'tech1@helpdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician', NOW()),
(3, 'วิชัย ช่างไฟและระบบเครือข่าย', 'tech2@helpdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'technician', NOW()),
(4, 'สมศรี ผู้ใช้งานทั่วไป', 'user@helpdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW()),
(5, 'อนันต์ เจ้าหน้าที่การตลาด', 'anan@helpdesk.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW());

-- 2. หมวดหมู่งานซ่อมบำรุง
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'งานอาคารและระบบปรับอากาศ', 'ระบบปรับอากาศ, ไฟฟ้าส่องสว่าง, พัดลมระบายอากาศ, ประตูและหน้าต่าง'),
(2, 'อุปกรณ์คอมพิวเตอร์และสำนักงาน', 'คอมพิวเตอร์ตั้งโต๊ะ, จอแสดงผล, เครื่องพิมพ์, เครื่องสแกนเนอร์'),
(3, 'ระบบเครือข่ายและการสื่อสาร', 'สัญญาณอินเทอร์เน็ตไร้สาย, สายแลน, ระบบเครือข่าย, โทรศัพท์ภายใน'),
(4, 'งานระบบประปาและสุขภัณฑ์', 'ระบบท่อน้ำดีน้ำทิ้ง, ก๊อกน้ำ, วาล์วน้ำ, ปั๊มน้ำ, สุขภัณฑ์'),
(5, 'โปรแกรมและสิทธิ์การเข้าใช้งาน', 'ระบบงานองค์กร, บัญชีผู้ใช้งาน, อีเมล, ขอสิทธิ์โฟลเดอร์แชร์');

-- 3. รายการอะไหล่ในคลัง
INSERT INTO `spare_parts` (`id`, `part_code`, `name`, `stock_quantity`, `unit_price`, `created_at`) VALUES
(1, 'AC-CAP-45', 'คาปาซิเตอร์แอร์ 45 ไมโครฟารัด', 15, 350.00, NOW()),
(2, 'LED-TUBE-18W', 'หลอดไฟแอลอีดี T8 ขนาด 18 วัตต์ แสงขาว', 50, 120.00, NOW()),
(3, 'RAM-DDR4-8G', 'แรม DDR4 ขนาด 8GB ความเร็ว 3200MHz', 20, 890.00, NOW()),
(4, 'SSD-500G-M2', 'โซลิดสเตตไดรฟ์ M.2 NVMe ขนาด 500GB', 12, 1450.00, NOW()),
(5, 'LAN-CAT6-PATCH', 'สายแลนสำเร็จรูป Cat6 ความยาว 3 เมตร', 40, 85.00, NOW()),
(6, 'VALVE-BRASS-12', 'สต็อปวาล์วน้ำทองเหลือง ขนาด 4 หุน', 25, 180.00, NOW()),
(7, 'FUSE-THERMAL-10A', 'ฟิวส์ความร้อน 10 แอมป์ 250 โวลต์', 30, 45.00, NOW());

-- 4. ตัวอย่างใบแจ้งซ่อม (Tickets)
INSERT INTO `tickets` (`id`, `user_id`, `category_id`, `technician_id`, `title`, `location`, `description`, `priority`, `status`, `sla_due_at`, `created_at`, `updated_at`, `resolved_at`, `closed_at`) VALUES
(1, 4, 1, NULL, 'เครื่องปรับอากาศห้องประชุมใหญ่ชั้น 2 มีน้ำหยดและไม่เย็น', 'อาคาร A ชั้น 2 ห้องประชุม 201', 'เปิดใช้งานช่วงเช้าพบน้ำแอร์หยดลงบนโต๊ะประชุม และอุณหภูมิไม่ลดลง รบกวนรีบเข้าตรวจสอบก่อนช่วงบ่ายครับ', 'high', 'pending_approval', DATE_ADD(NOW(), INTERVAL 8 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR), NOW(), NULL, NULL),
(2, 5, 2, 2, 'เครื่องคอมพิวเตอร์เปิดไม่ติด มีไฟกระพริบสีส้ม', 'อาคาร B ชั้น 3 แผนกการเงิน โต๊ะ 14', 'กดปุ่มเปิดเครื่องแล้วพัดลมหมุนสั้นๆ แล้วดับ มีเสียงแจ้งเตือน 3 ครั้ง ไม่สามารถเปิดทำงานได้', 'urgent', 'assigned', DATE_ADD(NOW(), INTERVAL 3 HOUR), DATE_SUB(NOW(), INTERVAL 2 HOUR), NOW(), NULL, NULL),
(3, 4, 3, 3, 'สัญญาณอินเทอร์เน็ตไร้สายหลุดบ่อยทั้งชั้น 4', 'อาคาร A ชั้น 4 พื้นที่ทำงานส่วนกลาง', 'พนักงานประมาณ 10 คนไม่สามารถเชื่อมต่อสัญญาณอินเทอร์เน็ตได้ สัญญาณขาดหายต่อเนื่อง', 'urgent', 'en_route', DATE_ADD(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 3 HOUR), NOW(), NULL, NULL),
(4, 5, 1, 2, 'เบรกเกอร์เครื่องปรับอากาศตัดการทำงานบ่อยครั้ง', 'อาคาร A ชั้น 3 แผนกบุคคล', 'เมื่อเปิดแอร์ตัวที่ 3 สวิตช์เบรกเกอร์จะตัดไฟลงทันที คาดว่ามีกระแสไฟฟ้ารั่วหรือใช้งานไฟเกิน', 'high', 'in_progress', DATE_ADD(NOW(), INTERVAL 5 HOUR), DATE_SUB(NOW(), INTERVAL 5 HOUR), NOW(), NULL, NULL),
(5, 4, 2, 2, 'ฮาร์ดดิสก์เครื่องแม่ข่ายสำรองข้อมูลทำงานช้ามาก', 'ห้องเซิร์ฟเวอร์ ชั้นใต้ดิน B1 ตู้แร็ค 02', 'ถอดตรวจสอบพบไดรฟ์มีข้อผิดพลาด จำเป็นต้องเปลี่ยนไดรฟ์ความเร็วสูงตัวใหม่', 'normal', 'waiting_parts', DATE_ADD(NOW(), INTERVAL 18 HOUR), DATE_SUB(NOW(), INTERVAL 1 DAY), NOW(), NULL, NULL),
(6, 5, 4, 2, 'ก๊อกน้ำอ่างล้างมือห้องน้ำชายชั้น 1 น้ำรั่วซึมตลอดเวลา', 'อาคาร B ชั้น 1 ห้องน้ำชาย', 'ทำการเปลี่ยนสต็อปวาล์วทองเหลืองตัวใหม่ ทดสอบเปิดปิดน้ำแล้ว ไม่พบการรั่วซึม', 'normal', 'resolved', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 2 DAY), NOW(), DATE_SUB(NOW(), INTERVAL 2 HOUR), NULL),
(7, 4, 2, 2, 'หน้าจอคอมพิวเตอร์กระพริบเป็นเส้นและดับเป็นช่วงๆ', 'อาคาร A ชั้น 2 แผนกจัดซื้อ โต๊ะ 08', 'ทำการเปลี่ยนสายสัญญาณเชื่อมต่อจอภาพใหม่และทดสอบการแสดงผลเรียบร้อย ใช้งานได้ตามปกติ', 'low', 'closed', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY), NOW(), DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));

-- 5. บันทึกผลการซ่อม (Repair Logs)
INSERT INTO `ticket_repair_logs` (`id`, `ticket_id`, `root_cause`, `solution_note`, `actual_hours`, `proof_image_path`, `created_at`) VALUES
(1, 6, 'ซีลยางสต็อปวาล์วเดิมเสื่อมสภาพตามอายุการใช้งาน ทำให้ปิดน้ำไม่อยู่', 'ทำการปิดวาล์วหลัก รื้อถอนวาล์วเดิมออก และติดตั้งสต็อปวาล์วทองเหลืองขนาด 4 หุนตัวใหม่ พร้อมพันเทปเกลียวอย่างแน่นหนา ทดสอบแรงดันน้ำเรียบร้อยไม่รั่วซึม', 1.50, 'uploads/proof_sample_plumbing.jpg', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 7, 'พอร์ตเชื่อมต่อสายสัญญาณด้านหลังการ์ดจอหลวมและมีฝุ่นสะสม', 'เป่าทำความสะอาดฝุ่นที่พอร์ต และเปลี่ยนสายสัญญาณเส้นใหม่ ทดสอบเปิดใช้งานต่อเนื่อง 2 ชั่วโมง ไม่พบอาการกระพริบ', 0.75, 'uploads/proof_sample_pc.jpg', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- 6. การเบิกใช้อะไหล่ (Parts Used)
INSERT INTO `ticket_parts_used` (`id`, `ticket_id`, `part_id`, `quantity`, `status`, `created_at`) VALUES
(1, 5, 4, 1, 'requested', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 6, 6, 1, 'approved', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(3, 7, 5, 1, 'approved', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- 7. ประวัติสถานะ (Status Logs)
INSERT INTO `status_logs` (`ticket_id`, `changed_by`, `from_status`, `to_status`, `note`, `created_at`) VALUES
(1, 4, 'none', 'pending_approval', 'ผู้แจ้งสร้างใบแจ้งซ่อมในระบบ', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 5, 'none', 'pending_approval', 'ผู้แจ้งสร้างใบแจ้งซ่อม', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 1, 'pending_approval', 'assigned', 'หัวหน้าช่างอนุมัติและมอบหมายงานให้ สมชาย ช่างเทคนิคอาคารและไอที', DATE_SUB(NOW(), INTERVAL 90 MINUTE)),
(3, 4, 'none', 'pending_approval', 'ผู้แจ้งสร้างใบแจ้งซ่อม', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(3, 1, 'pending_approval', 'assigned', 'มอบหมายงานให้ วิชัย ช่างไฟและระบบเครือข่าย', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3, 3, 'assigned', 'en_route', 'ช่างกดเริ่มเดินทางไปยังหน้างาน อาคาร A ชั้น 4', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(4, 5, 'none', 'pending_approval', 'ผู้แจ้งสร้างใบแจ้งซ่อม', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(4, 1, 'pending_approval', 'assigned', 'มอบหมายงานให้ สมชาย', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(4, 2, 'assigned', 'en_route', 'ช่างเดินทางไปหน้างาน', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(4, 2, 'en_route', 'in_progress', 'ถึงหน้างานและเริ่มใช้เครื่องมือวัดกระแสไฟฟ้าตรวจสอบเบรกเกอร์', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(5, 4, 'none', 'pending_approval', 'ผู้แจ้งสร้างใบแจ้งซ่อม', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 1, 'pending_approval', 'assigned', 'มอบหมายงานให้ สมชาย', DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(5, 2, 'assigned', 'in_progress', 'เข้าตรวจสอบสภาพไดรฟ์', DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(5, 2, 'in_progress', 'waiting_parts', 'ขอเบิกโซลิดสเตตไดรฟ์ M.2 NVMe ขนาด 500GB จำนวน 1 ชิ้น', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(6, 5, 'in_progress', 'resolved', 'เปลี่ยนวาล์วน้ำตัวใหม่และแนบรูปผลงานเรียบร้อย ส่งมอบงานให้ผู้แจ้ง', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(7, 4, 'resolved', 'closed', 'ผู้แจ้งกดยืนยันปิดงานและให้คะแนนการบริการ 5 ดาว', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- 8. ความคิดเห็น (Comments)
INSERT INTO `comments` (`ticket_id`, `user_id`, `body`, `image_path`, `created_at`) VALUES
(1, 4, 'แนบรูปภาพคราบน้ำที่หยดลงบนโต๊ะประชุมครับ', NULL, DATE_SUB(NOW(), INTERVAL 50 MINUTE)),
(3, 3, 'กำลังนำเครื่องวัดสัญญาณและอุปกรณ์สำรองขึ้นไปตรวจสอบที่ชั้น 4 ครับ', NULL, DATE_SUB(NOW(), INTERVAL 25 MINUTE)),
(5, 2, 'ส่งคำขอเบิกอะไหล่ให้หัวหน้าช่างพิจารณาแล้วครับ หากอนุมัติจะรีบเข้าเปลี่ยนทันที', NULL, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(7, 4, 'ช่างมาซ่อมแซมได้รวดเร็วและเรียบร้อยมากครับ ขอบคุณครับ', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- 9. ประเมินความพึงพอใจ (Ratings)
INSERT INTO `ratings` (`ticket_id`, `score`, `feedback`, `created_at`) VALUES
(7, 5, 'บริการรวดเร็วมาก ช่างตรงต่อเวลาและแก้ปัญหาได้ตรงจุด ประทับใจมากครับ', DATE_SUB(NOW(), INTERVAL 1 DAY));
