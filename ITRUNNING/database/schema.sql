-- ==========================================================
-- โครงสร้างฐานข้อมูลระบบเว็บวิ่ง: IT วิ่งเข้าป่ามัน (RunLan Platform)
-- ไฟล์: database/schema.sql
-- ==========================================================

-- ลบตารางเดิมถ้ามี
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `race_results`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `event_categories`;
DROP TABLE IF EXISTS `events`;

-- 1. ตารางข้อมูลงานวิ่ง (events)
CREATE TABLE `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL COMMENT 'ชื่องานวิ่ง',
    `location` VARCHAR(255) NOT NULL COMMENT 'สถานที่จัดงาน (จังหวัด/สถานที่)',
    `race_date` DATE NOT NULL COMMENT 'วันจัดกิจกรรมแข่งขันวิ่ง',
    `race_time` TIME NOT NULL DEFAULT '05:00:00' COMMENT 'เวลาปล่อยตัว (เช่น 03:30:00, 05:00:00)',
    `event_date` DATE NOT NULL COMMENT 'วันจัดกิจกรรมวิ่ง',
    `registration_end_date` DATETIME NOT NULL COMMENT 'วันปิดรับสมัคร',
    `category_type` VARCHAR(100) NOT NULL DEFAULT 'Mini Marathon' COMMENT 'ประเภทระยะหลัก',
    `categories` TEXT NULL COMMENT 'สรุปประเภทระยะทาง เช่น 21.1K, 10K, 5K',
    `view_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'จำนวนยอดผู้เข้าชมงานนี้',
    `rewards_detail` TEXT NULL COMMENT 'รายละเอียดของรางวัล เหรียญ เสื้อ ถ้วยรางวัล',
    `banner_image` VARCHAR(255) NOT NULL DEFAULT 'https://images.unsplash.com/photo-1513593771513-7b58b6c4af38?auto=format&fit=crop&w=1200&q=80' COMMENT 'รูปภาพแบนเนอร์ของงาน',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่สร้างรายการ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ตารางหมวดหมู่/ระยะทางของงานวิ่ง (event_categories)
CREATE TABLE `event_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT NOT NULL COMMENT 'รหัสงานวิ่ง',
    `category_name` VARCHAR(100) NOT NULL COMMENT 'ชื่อประเภท เช่น Marathon (42.195K), Half Marathon (21.1K)',
    `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'ราคาค่าสมัคร (บาท)',
    `max_slots` INT UNSIGNED NOT NULL DEFAULT 100 COMMENT 'จำนวนรับสมัครสูงสุด (โควตา)',
    `booked_slots` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'จำนวนที่ถูกจองแล้ว',
    CONSTRAINT `fk_category_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ตารางการลงทะเบียนสมัครวิ่ง (registrations)
CREATE TABLE `registrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT NOT NULL COMMENT 'รหัสงานวิ่ง',
    `category_id` INT NOT NULL COMMENT 'รหัสหมวดหมู่งานวิ่ง',
    `booking_code` VARCHAR(50) NOT NULL COMMENT 'รหัสการจอง (เช่น RUN-2026-XXXX)',
    `full_name` VARCHAR(150) NOT NULL COMMENT 'ชื่อ-นามสกุล ผู้สมัคร',
    `email` VARCHAR(150) NOT NULL COMMENT 'อีเมลผู้สมัคร',
    `phone` VARCHAR(20) NOT NULL COMMENT 'เบอร์โทรศัพท์',
    `shirt_size` VARCHAR(10) NOT NULL COMMENT 'ไซส์เสื้อ (S, M, L, XL, 2XL, 3XL)',
    `runner_photo` VARCHAR(255) NULL COMMENT 'ชื่อไฟล์รูปถ่ายหน้าตรงนักวิ่ง',
    `slip_image` VARCHAR(255) NOT NULL COMMENT 'ชื่อไฟล์สลิปหลักฐานการโอนเงิน',
    `payment_status` ENUM('pending', 'confirmed') NOT NULL DEFAULT 'pending' COMMENT 'สถานะการชำระเงิน',
    `registered_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'เวลาที่ทำการลงทะเบียน',
    UNIQUE KEY `uq_booking_code` (`booking_code`),
    UNIQUE KEY `uq_event_email` (`event_id`, `email`),
    CONSTRAINT `fk_reg_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_reg_category` FOREIGN KEY (`category_id`) REFERENCES `event_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ตารางผลการแข่งขัน (race_results)
CREATE TABLE `race_results` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT NOT NULL COMMENT 'รหัสงานวิ่ง',
    `bib_number` VARCHAR(20) NOT NULL COMMENT 'หมายเลข BIB',
    `runner_name` VARCHAR(150) NOT NULL COMMENT 'ชื่อ-นามสกุล นักวิ่ง',
    `gender` ENUM('M', 'F') NOT NULL DEFAULT 'M' COMMENT 'เพศ (M = ชาย, F = หญิง)',
    `age_group` VARCHAR(50) NOT NULL DEFAULT 'Overall' COMMENT 'กลุ่มอายุ เช่น M 20-29, F 30-39',
    `category_name` VARCHAR(100) NOT NULL COMMENT 'ระยะทาง เช่น Half Marathon (21.1 Km)',
    `gun_time` VARCHAR(20) NOT NULL COMMENT 'เวลา Gun Time',
    `net_time` VARCHAR(20) NOT NULL COMMENT 'เวลา Net Time ชิปไทม์มิ่ง',
    `overall_rank` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'อันดับรวมทุกรุ่น',
    `gender_rank` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'อันดับตามเพศ',
    `division_rank` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'อันดับตามกลุ่มอายุ',
    `avg_pace` VARCHAR(20) NOT NULL DEFAULT '04:00 min/km' COMMENT 'ความเร็วเฉลี่ย Pace',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_result_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_bib_name` (`bib_number`, `runner_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ตารางข้อความติดต่อ (contact_messages)
CREATE TABLE `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL COMMENT 'ชื่อ-นามสกุล ผู้ติดต่อ',
    `email` VARCHAR(150) NOT NULL COMMENT 'อีเมลติดต่อ',
    `phone` VARCHAR(30) NOT NULL COMMENT 'เบอร์โทรศัพท์',
    `subject` VARCHAR(150) NOT NULL DEFAULT 'สอบถามข้อมูลทั่วไป' COMMENT 'หัวข้อเรื่อง',
    `message` TEXT NOT NULL COMMENT 'ข้อความรายละเอียด',
    `status` ENUM('unread', 'read', 'replied') NOT NULL DEFAULT 'unread' COMMENT 'สถานะการดำเนินเรื่อง',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- MOCK DATA: รายการงานวิ่ง 16 รายการ
-- ==========================================================

INSERT INTO `events` (`id`, `title`, `location`, `race_date`, `race_time`, `event_date`, `registration_end_date`, `category_type`, `categories`, `view_count`, `rewards_detail`, `banner_image`, `created_at`) VALUES
(1, '12 สิงหา ฮาล์ฟมาราธอน กรุงเทพฯ 2026 (Mother\'s Day Run)', 'ศูนย์การประชุมแห่งชาติสิริกิติ์ กรุงเทพฯ', '2026-08-12', '04:30:00', '2026-08-12', '2026-08-05 23:59:59', 'Half Marathon', '21.1K, 10K, 5K, 1.8K Walk', 1850, '🥇 เสื้อ Finisher ลายดอกมะลิพิเศษ\n🏅 เหรียญที่ระลึกชุบทองคำขาว\n🏆 ถ้วยรางวัลคู่แม่ลูก และ Top 10 ทุกกลุ่มอายุ', 'https://images.unsplash.com/photo-1513593771513-7b58b6c4af38?auto=format&fit=crop&w=1200&q=80', NOW()),
(2, 'แม่เมาะ ฮาล์ฟมาราธอน ลำปาง 2026 (Mae Moh Half Marathon)', 'กฟผ. แม่เมาะ อ.แม่เมาะ จ.ลำปาง', '2026-08-23', '05:00:00', '2026-08-23', '2026-08-15 23:59:59', 'Half Marathon', '21.1K, 10.5K, 5K', 1420, '🌿 เหรียญพลังงานสีเขียวลายทุ่งบัวตองแม่เมาะ\n👕 เสื้อวิ่งเนื้อผ้าไมโครโพลีเอสเตอร์\n🏆 เงินรางวัลรวมกว่า 300,000 บาท', 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?auto=format&fit=crop&w=1200&q=80', NOW()),
(3, 'ภูเก็ต ซันเซ็ต บีชรัน 2026 (Phuket Sunset Beach Run)', 'หาดในหาน ต.ราไวย์ จ.ภูเก็ต', '2026-08-29', '17:00:00', '2026-08-29', '2026-08-27 23:59:59', 'Fun Run', '10K, 5K, 3K Family Run', 2190, '🌅 เหรียญพระอาทิตย์ตกน้ำเรืองแสง\n🎽 เสื้อวิ่งชายหาด Singlet แห้งไวพิเศษ\n🎉 ปาร์ตี้โฟมและดนตรีริมหาด', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', NOW()),
(4, 'อยุธยา มินิมาราธอน มรดกโลก 2026 (Ayutthaya Heritage Run)', 'อุทยานประวัติศาสตร์พระนครศรีอยุธยา จ.อยุธยา', '2026-08-30', '05:30:00', '2026-08-30', '2026-08-28 23:59:59', 'Mini Marathon', '10K, 5K Fun Run', 1650, '🏛️ เหรียญโลหะโบราณลายวัดพระศรีสรรเพชญ์\n👕 เสื้อวิ่งลายไทยคราฟต์\n🏆 ถ้วยรางวัลศิลาจำลองอยุธยา', 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=1200&q=80', NOW()),
(5, 'หัวหิน มินิมาราธอน เลียบหาด 2026 (Hua Hin Beach Mini Run)', 'สวนสาธารณะโผน กิ่งเพชร จ.ประจวบคีรีขันธ์', '2026-09-06', '05:30:00', '2026-09-06', '2026-09-01 23:59:59', 'Mini Marathon', '10.5K, 5K Fun Run', 1380, '🌊 เหรียญรางวัลรูปเปลือกหอยและม้าหัวหิน\n👕 เสื้อวิ่งสีครามน้ำทะเล\n🏆 โล่รางวัล Overall ชาย/หญิง', 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&w=1200&q=80', NOW()),
(6, 'เมืองกาญจน์ เทรล แอนด์ ริเวอร์แคว 2026 (River Kwai Trail)', 'สะพานข้ามแม่น้ำแคว จ.กาญจนบุรี', '2026-09-13', '05:00:00', '2026-09-13', '2026-09-05 23:59:59', 'Trail', '30K Trail, 15K Trail, 7K Fun Trail', 1980, '⛰️ เหรียญไม้สักฉลุลายทางรถไฟสายมรณะ\n🎒 เป้น้ำและกระบอกน้ำพับได้\n🏆 ถ้วยรางวัลงานหินกาญจนบุรี', 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=1200&q=80', NOW()),
(7, 'บึงแก่นนคร ไนท์รัน ขอนแก่น 2026 (Khon Kaen Lake Night Run)', 'สวนสาธารณะ 200 ปี ริมบึงแก่นนคร จ.ขอนแก่น', '2026-09-26', '18:30:00', '2026-09-26', '2026-09-20 23:59:59', 'Fun Run', '10K, 5K, 2.5K Family Walk', 1240, '✨ เหรียญไฟ LED กะพริบได้\n🎽 กำไลข้อมือสะท้อนแสงฟรี\n🏆 ถ้วยรางวัลแฟนซี', 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=1200&q=80', NOW()),
(8, 'เขาใหญ่ เทรล & มินิมาราธอน 2026 (Khao Yai Trail Challenge)', 'อุทยานแห่งชาติเขาใหญ่ จ.นครราชสีมา', '2026-10-18', '05:30:00', '2026-10-18', '2026-10-05 23:59:59', 'Trail', '25K Trail, 10K Mini, 5K Eco Run', 2850, '🌿 เหรียญไม้รักษ์โลกฉลุลายผืนป่าเขาใหญ่\n👕 เสื้อวิ่งเนื้อผ้า Aerocool ซับเหงื่อแห้งไว\n🏆 โล่รางวัลงานคราฟต์จากธรรมชาติ', 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=1200&q=80', NOW()),
(9, 'ภูทับเบิก สกายรัน มินิมาราธอน 2026 (Phu Thap Boek Sky Run)', 'จุดชมวิวภูทับเบิก จ.เพชรบูรณ์', '2026-10-25', '05:00:00', '2026-10-25', '2026-10-15 23:59:59', 'Mini Marathon', '12K King of Mountain, 6K Cloud Run', 1780, '☁️ เหรียญสัมผัสทะเลหมอก 3D ลายกะหล่ำปลี\n🧥 เสื้อแจ็กเก็ตกันลมเนื้อบาง\n🏆 ถ้วยรางวัล King of Mountain', 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80', NOW()),
(10, 'กว๊านพะเยา ฮาล์ฟมาราธอน 2026 (Phayao Lake Half Marathon)', 'ริมกว๊านพะเยา จ.พะเยา', '2026-10-31', '04:45:00', '2026-10-31', '2026-10-20 23:59:59', 'Half Marathon', '21.1K, 10K, 5K', 1450, '🐟 เหรียญรูปปลาบึกและวิวกว๊านพะเยา\n👕 เสื้อวิ่งสีครามย้อมธรรมชาติ\n🏆 ถ้วยเกียรติยศกลุ่มอายุ 1-5', 'https://images.unsplash.com/photo-1508873696983-2df5293cb32b?auto=format&fit=crop&w=1200&q=80', NOW()),
(11, 'พัทยา อินเตอร์เนชั่นแนล มาราธอน 2026 (Pattaya Marathon)', 'ถนนเลียบชายหาดพัทยา จ.ชลบุรี', '2026-11-08', '03:45:00', '2026-11-08', '2026-10-25 23:59:59', 'Full Marathon', '42.195K, 21.1K, 10K, 4.5K', 3200, '🌊 เสื้อ Singlet ชายหาดเนื้อพรีเมียม\n🏅 เหรียญรางวัล 3 มิติ ลายวิวอ่าวพัทยา\n🏆 เงินรางวัลรวมกว่า 800,000 บาท', 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?auto=format&fit=crop&w=1200&q=80', NOW()),
(12, 'สุโขทัย มินิ & มาราธอน มรดกโลก 2026 (Sukhothai Marathon)', 'อุทยานประวัติศาสตร์สุโขทัย จ.สุโขทัย', '2026-11-15', '03:30:00', '2026-11-15', '2026-10-31 23:59:59', 'Full Marathon', '42.195K, 21.1K, 10K, 3.5K', 1620, '🪔 เหรียญทรงโคมลอยเคลือบทองโบราณ\n👕 เสื้อ Finisher 42.195K ลายสังคโลก\n🏆 โล่เกียรติยศศิลาจารึกจำลอง', 'https://images.unsplash.com/photo-1541872703-74c5e44368f9?auto=format&fit=crop&w=1200&q=80', NOW()),
(13, 'ลากูน่า ภูเก็ต ไตรกีฬา & รัน 2026 (Laguna Phuket Half Marathon)', 'ลากูน่า ภูเก็ต จ.ภูเก็ต', '2026-11-22', '05:30:00', '2026-11-22', '2026-11-10 23:59:59', 'Half Marathon', '21.1K, 10.5K, 5K Community Run', 2100, '🌴 เหรียญทองเหลืองดีไซน์มาตรฐาน Laguna Series\n👕 เสื้อวิ่งแบรนด์กีฬาอินเตอร์\n🏆 ถ้วยรางวัล Overall ชาย/หญิง', 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80', NOW()),
(14, 'อะเมซิ่งไทยแลนด์ มาราธอน แบงค็อก 2026 (ATM Bangkok 2026)', 'ถนนราชดำเนิน และสะพานพระราม 8 กรุงเทพฯ', '2026-12-06', '02:30:00', '2026-12-06', '2026-11-20 23:59:59', 'Full Marathon', '42.195K, 21.1K, 10K, 5K', 4600, '👑 เสื้อ Finisher ลายแลนด์มาร์กกรุงเทพฯ\n🏅 เหรียญรางวัล World Athletics Elite Label\n🏆 เงินรางวัลรวมกว่า 2,000,000 บาท', 'https://images.unsplash.com/photo-1508672019048-805b876b67e2?auto=format&fit=crop&w=1200&q=80', NOW()),
(15, 'บางแสน21 ฮาล์ฟมาราธอน 2026 (Bangsaen21 Half Marathon)', 'ชายหาดบางแสน จ.ชลบุรี', '2026-12-20', '03:30:00', '2026-12-20', '2026-11-30 23:59:59', 'Half Marathon', '21.1K, 10K, 5K', 3890, '🥇 เสื้อ Finisher 21.1K ลายคลื่นทะเล\n🏅 เหรียญที่ระลึกดีไซน์หรูพรีเมียม\n🏆 ถ้วยรางวัล Top 100 ชาย/หญิง', 'https://images.unsplash.com/photo-1530549387789-4c1017266635?auto=format&fit=crop&w=1200&q=80', NOW()),
(16, 'เชียงใหม่ มาราธอน นานาชาติ 2026 (Chiang Mai Marathon 2026)', 'ประตูท่าแพ จ.เชียงใหม่', '2026-12-27', '03:00:00', '2026-12-27', '2026-12-05 23:59:59', 'Full Marathon', '42.195K, 21.1K, 10K, 3K', 3450, '🥇 เสื้อ Finisher ลายล้านนาพิเศษ\n🏅 เหรียญรางวัลเอกลักษณ์เมืองเหนือ\n🏆 ถ้วยรางวัลเกียรติยศ Overall และกลุ่มอายุ', 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?auto=format&fit=crop&w=1200&q=80', NOW());

-- ==========================================================
-- MOCK DATA: หมวดหมู่และราคาของแต่งาน (event_categories)
-- ==========================================================

INSERT INTO `event_categories` (`id`, `event_id`, `category_name`, `price`, `max_slots`, `booked_slots`) VALUES
(1, 1, 'Half Marathon (21.1 Km)', 850.00, 1000, 1000),
(2, 1, 'Mini Marathon (10 Km)', 650.00, 1200, 1200),
(3, 1, 'Fun Run (5 Km)', 450.00, 800, 800),
(4, 1, 'Family Walk (1.8 Km)', 350.00, 500, 500),
(5, 2, 'Half Marathon (21.1 Km)', 900.00, 800, 800),
(6, 2, 'Mini Marathon (10.5 Km)', 650.00, 1000, 1000),
(7, 2, 'Fun Run (5 Km)', 450.00, 600, 600),
(8, 3, 'Mini Beach Run (10 Km)', 600.00, 600, 530),
(9, 3, 'Sunset Fun Run (5 Km)', 450.00, 800, 710),
(10, 3, 'Family Sunset Walk (3 Km)', 350.00, 400, 350),
(11, 4, 'Heritage Mini Marathon (10 Km)', 600.00, 1000, 820),
(12, 4, 'Fun Run (5 Km)', 450.00, 800, 660),
(13, 5, 'Beach Mini Marathon (10.5 Km)', 650.00, 800, 450),
(14, 5, 'Fun Run (5 Km)', 450.00, 600, 320),
(15, 6, 'River Kwai Trail (30 Km)', 1500.00, 400, 350),
(16, 6, 'Mini Trail (15 Km)', 1000.00, 600, 510),
(17, 6, 'Fun Trail (7 Km)', 650.00, 500, 410),
(18, 7, 'Night Mini Marathon (10 Km)', 550.00, 800, 330),
(19, 7, 'Glow Fun Run (5 Km)', 400.00, 1000, 420),
(20, 7, 'Family Walk (2.5 Km)', 300.00, 500, 180),
(21, 8, 'Khao Yai Trail (25 Km)', 1400.00, 500, 460),
(22, 8, 'Mini Trail (10 Km)', 850.00, 800, 710),
(23, 8, 'Eco Fun Run (5 Km)', 550.00, 600, 520),
(24, 9, 'King of Mountain (12 Km)', 750.00, 600, 370),
(25, 9, 'Cloud Run (6 Km)', 500.00, 800, 470),
(26, 10, 'Half Marathon (21.1 Km)', 900.00, 1000, 520),
(27, 10, 'Mini Marathon (10 Km)', 650.00, 1200, 590),
(28, 10, 'Fun Run (5 Km)', 450.00, 600, 280),
(29, 11, 'Full Marathon (42.195 Km)', 1250.00, 1500, 980),
(30, 11, 'Half Marathon (21.1 Km)', 900.00, 2000, 1310),
(31, 11, 'Mini Marathon (10 Km)', 650.00, 1500, 950),
(32, 11, 'Family Run (4.5 Km)', 450.00, 800, 510),
(33, 12, 'Full Marathon (42.195 Km)', 1200.00, 800, 380),
(34, 12, 'Half Marathon (21.1 Km)', 850.00, 1000, 460),
(35, 12, 'Mini Marathon (10 Km)', 600.00, 1000, 420),
(36, 12, 'Fun Run (3.5 Km)', 400.00, 500, 210),
(37, 13, 'Half Marathon (21.1 Km)', 1100.00, 1200, 640),
(38, 13, 'Mini Marathon (10.5 Km)', 750.00, 1500, 770),
(39, 13, 'Community Run (5 Km)', 550.00, 800, 410),
(40, 14, 'Full Marathon (42.195 Km)', 1500.00, 3000, 2550),
(41, 14, 'Half Marathon (21.1 Km)', 1100.00, 4000, 3380),
(42, 14, 'Mini Marathon (10 Km)', 800.00, 3500, 2910),
(43, 14, 'Fun Run (5 Km)', 500.00, 2000, 1620),
(44, 15, 'Half Marathon (21.1 Km)', 950.00, 2500, 2310),
(45, 15, 'Mini Marathon (10 Km)', 750.00, 1500, 1340),
(46, 15, 'Micro Marathon (5 Km)', 550.00, 1000, 850),
(47, 16, 'Full Marathon (42.195 Km)', 1300.00, 1500, 1120),
(48, 16, 'Half Marathon (21.1 Km)', 950.00, 2000, 1450),
(49, 16, 'Mini Marathon (10 Km)', 650.00, 1500, 1020),
(50, 16, 'Smile Run (3 Km)', 450.00, 800, 520);

-- ==========================================================
-- MOCK DATA: ผลการแข่งขัน (race_results)
-- ==========================================================

INSERT INTO `race_results` (`id`, `event_id`, `bib_number`, `runner_name`, `gender`, `age_group`, `category_name`, `gun_time`, `net_time`, `overall_rank`, `gender_rank`, `division_rank`, `avg_pace`, `created_at`) VALUES
(1, 1, 'A21-1001', 'ณัฐวุฒิ วิ่งทะลุฟ้า', 'M', 'M 20-29', 'Half Marathon (21.1 Km)', '01:12:45', '01:12:43', 1, 1, 1, '03:26 min/km', NOW()),
(2, 1, 'A21-1002', 'กิตติพงษ์ เทพสายฟ้า', 'M', 'M 30-39', 'Half Marathon (21.1 Km)', '01:14:20', '01:14:18', 2, 2, 1, '03:31 min/km', NOW()),
(3, 1, 'A21-1003', 'สมชาย ยอดนักวิ่ง', 'M', 'M 30-39', 'Half Marathon (21.1 Km)', '01:16:05', '01:16:00', 3, 3, 2, '03:36 min/km', NOW()),
(4, 1, 'B21-2001', 'สุนิสา ใจแกร่ง', 'F', 'F 20-29', 'Half Marathon (21.1 Km)', '01:22:15', '01:22:12', 4, 1, 1, '03:53 min/km', NOW()),
(5, 1, 'B21-2002', 'พัชรี สปีดเกิร์ล', 'F', 'F 30-39', 'Half Marathon (21.1 Km)', '01:25:40', '01:25:35', 5, 2, 1, '04:03 min/km', NOW()),
(6, 1, 'A21-1088', 'ธนพล สายลุยป่า', 'M', 'M 40-49', 'Half Marathon (21.1 Km)', '01:32:10', '01:31:55', 12, 10, 3, '04:21 min/km', NOW()),
(7, 1, 'A21-1520', 'ธีรภัทร ชิลรันเนอร์', 'M', 'M 20-29', 'Half Marathon (21.1 Km)', '01:45:30', '01:44:50', 35, 28, 12, '04:58 min/km', NOW()),
(8, 1, 'B21-2105', 'กัญญารัตน์ รันนิ่งควีน', 'F', 'F 40-49', 'Half Marathon (21.1 Km)', '01:52:18', '01:51:40', 58, 15, 4, '05:17 min/km', NOW()),
(9, 1, 'M10-5001', 'วรัญญู มินิสปีด', 'M', 'M Overall', 'Mini Marathon (10 Km)', '00:34:10', '00:34:08', 1, 1, 1, '03:24 min/km', NOW()),
(10, 1, 'M10-5002', 'ชลธิชา มินิสตาร์', 'F', 'F Overall', 'Mini Marathon (10 Km)', '00:39:45', '00:39:42', 2, 1, 1, '03:58 min/km', NOW()),
(11, 2, 'MM-001', 'ศราวุธ แอ่วเหนือ', 'M', 'M 20-29', 'Half Marathon (21.1 Km)', '01:13:50', '01:13:48', 1, 1, 1, '03:30 min/km', NOW()),
(12, 2, 'MM-002', 'สุรชัย พลังขุนเขา', 'M', 'M 30-39', 'Half Marathon (21.1 Km)', '01:15:30', '01:15:25', 2, 2, 1, '03:34 min/km', NOW()),
(13, 2, 'MM-101', 'นารีรัตน์ ดอกบัวตอง', 'F', 'F 20-29', 'Half Marathon (21.1 Km)', '01:24:10', '01:24:05', 3, 1, 1, '03:59 min/km', NOW()),
(14, 2, 'MM-555', 'อภิชาต นักวิ่งเมืองรถม้า', 'M', 'M 40-49', 'Half Marathon (21.1 Km)', '01:38:20', '01:38:00', 18, 15, 4, '04:38 min/km', NOW());

-- ==========================================================
-- MOCK DATA: รายการลงทะเบียนตัวอย่าง (registrations)
-- ==========================================================

INSERT INTO `registrations` (`id`, `event_id`, `category_id`, `booking_code`, `full_name`, `email`, `phone`, `shirt_size`, `runner_photo`, `slip_image`, `payment_status`, `registered_at`) VALUES
(1, 15, 44, 'RUN-2026-BS21-001', 'สมชาย รักการวิ่ง', 'somchai.run@example.com', '0812345678', 'L', 'default_runner.jpg', 'mock_slip_01.jpg', 'confirmed', '2026-08-01 10:15:00'),
(2, 15, 45, 'RUN-2026-BS21-002', 'วิภาดา ใจสู้', 'wiphada.runner@example.com', '0898765432', 'M', 'default_runner.jpg', 'mock_slip_02.jpg', 'confirmed', '2026-08-02 14:30:20'),
(3, 16, 47, 'RUN-2026-CM42-001', 'กิตติศักดิ์ นักวิ่งมาราธอน', 'kittisak.marathon@example.com', '0865551234', 'XL', 'default_runner.jpg', 'mock_slip_03.jpg', 'pending', '2026-08-10 09:00:00'),
(4, 8, 21, 'RUN-2026-KY25-001', 'ธนพล สายเทรล', 'thanapol.trail@example.com', '0841112233', 'M', 'default_runner.jpg', 'mock_slip_04.jpg', 'confirmed', '2026-08-15 16:45:10');
