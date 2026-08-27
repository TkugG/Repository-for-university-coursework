# 🍽️ Food & Recipe Management System (ระบบจัดการข้อมูลอาหารและสูตรอาหาร)

ระบบเว็บแอปพลิเคชันสำหรับจัดการข้อมูลรายการอาหาร หมวดหมู่อาหาร รูปภาพประกอบ และสูตรวัตถุดิบย่อย พัฒนาด้วยภาษา **PHP (PDO)** เชื่อมต่อฐานข้อมูล **MySQL / MariaDB** และตกแต่งส่วนต่อประสานผู้ใช้ (UI) ด้วย **Bootstrap 5** โดยออกแบบตามหลักความสัมพันธ์แบบ 1-to-Many (1 เมนูประกอบด้วยหลายวัตถุดิบ) และยึดหลัก HCI (Human-Computer Interaction) เพื่อความง่ายและสะดวกในการใช้งาน

---

## ✨ ฟีเจอร์หลัก (Key Features)

- 📋 **แสดงรายการอาหาร (Read):** แสดงเมนูอาหารทั้งหมด พร้อมรูปภาพ หมวดหมู่ (อาหารคาว/หวาน/เครื่องดื่ม) และ รายการวัตถุดิบ/สูตร ในรูปแบบตาราง
- ➕ **เพิ่มเมนูอาหารใหม่ (Create):**
  - กำหนดชื่ออาหารภาษาไทยและเลือกหมวดหมู่
  - อัปโหลดรูปภาพเมนูอาหารเข้าเซิร์ฟเวอร์
  - เพิ่มรายการวัตถุดิบ ปริมาณ และหน่วยวัด ได้แบบไดนามิก (Dynamic Row Addition) ผ่าน JavaScript
- ✏️ **แก้ไขข้อมูลเมนูอาหาร (Update):**
  - ดึงข้อมูลเดิมของอาหารและสูตรวัตถุดิบมาแก้ไขได้ทันที
  - อัปเดตรูปภาพหรือใช้รูปภาพเดิม
- 🗑️ **ลบข้อมูลเมนูอาหาร (Delete):**
  - มีระบบยืนยัน (Confirmation Alert) ก่อนลบข้อมูล
  - เมื่อลบเมนูอาหาร ข้อมูลสูตรวัตถุดิบที่เกี่ยวข้องจะถูกลบออกอัตโนมัติ

---

## 🛠️ เทคโนโลยีที่ใช้ (Tech Stack)

| ส่วนประกอบ | เทคโนโลยีที่ใช้ |
| :--- | :--- |
| **Language & Backend** | PHP (Native / PDO Connection) |
| **Database** | MySQL / MariaDB |
| **Frontend Framework** | Bootstrap 5.3 (via CDN) |
| **Client-Side Logic** | JavaScript (Vanilla JS for Dynamic UI Elements) |
| **Web Server Environment** | Apache (XAMPP / WAMP / Laragon) |

---

## 📁 โครงสร้างโปรเจกต์ (Project Structure)

```text
W5_Food_Repice/
├── uploads/          # โฟลเดอร์สำหรับเก็บไฟล์รูปภาพอาหารที่อัปโหลด
├── db.php            # ไฟล์ตั้งค่าและเชื่อมต่อฐานข้อมูล PDO (UTF-8)
├── index.php         # หน้าหลักแสดงรายการอาหารทั้งหมด และจัดการการลบ (Read / Delete)
├── manage.php        # หน้าฟอร์มสำหรับเพิ่มและแก้ไขข้อมูลอาหาร+วัตถุดิบ (Create / Update)
└── README.md         # เอกสารอธิบายรายละเอียดโปรเจกต์และการใช้งาน
```

---

## 🗄️ โครงสร้างฐานข้อมูล (Database Schema)

โปรเจกต์นี้ใช้ฐานข้อมูลชื่อ `foods_repice_db` ประกอบด้วย 2 ตารางหลักที่มีความสัมพันธ์กัน (1:N):

### 1. ตาราง `foods` (เก็บข้อมูลหลักของเมนูอาหาร)
| คอลัมน์ | ประเภทข้อมูล | รายละเอียด |
| :--- | :--- | :--- |
| `id` | `INT` (PK, Auto Increment) | รหัสอาหาร |
| `name_th` | `VARCHAR(255)` | ชื่ออาหาร (ภาษาไทย) |
| `category` | `VARCHAR(100)` | หมวดหมู่อาหาร (อาหารคาว, อาหารหวาน, เครื่องดื่ม) |
| `image` | `VARCHAR(255)` | ชื่อไฟล์รูปภาพอาหาร |

### 2. ตาราง `recipes` (เก็บสูตรวัตถุดิบย่อยของแต่ละเมนู)
| คอลัมน์ | ประเภทข้อมูล | รายละเอียด |
| :--- | :--- | :--- |
| `id` | `INT` (PK, Auto Increment) | รหัสวัตถุดิบ |
| `food_id` | `INT` (FK) | รหัสอาหาร อ้างอิงไปยัง `foods.id` |
| `recipe_name` | `VARCHAR(255)` | ชื่อวัตถุดิบ |
| `quantity` | `DECIMAL(10,2)` | ปริมาณวัตถุดิบ |
| `unit_name` | `VARCHAR(50)` | หน่วยวัด (เช่น กรัม, ช้อนโต๊ะ, ฟอง) |

### 📜 คำสั่ง SQL สำหรับสร้างฐานข้อมูลและตาราง

```sql
-- 1. สร้างฐานข้อมูล
CREATE DATABASE IF NOT EXISTS `foods_repice_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `foods_repice_db`;

-- 2. สร้างตาราง foods
CREATE TABLE IF NOT EXISTS `foods` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name_th` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. สร้างตาราง recipes
CREATE TABLE IF NOT EXISTS `recipes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `food_id` INT(11) NOT NULL,
  `recipe_name` VARCHAR(255) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `unit_name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_recipes_foods` (`food_id`),
  CONSTRAINT `fk_recipes_foods` FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🚀 วิธีติดตั้งและการใช้งาน (Installation & Setup)

1. **เตรียม Environment:**
   - ติดตั้ง [XAMPP](https://www.apachefriends.org/) หรือ Web Server ที่รองรับ PHP 8.0+ และ MySQL

2. **ดาวน์โหลด / วางไฟล์โปรเจกต์:**
   - คัดลอกโฟลเดอร์โปรเจกต์ `W5_Food_Repice` ไปไว้ที่โฟลเดอร์ `htdocs` ของ XAMPP (ตัวอย่าง: `C:\xampp\htdocs\W5_Food_Repice`)

3. **ตรวจสอบโฟลเดอร์รูปภาพ:**
   - ตรวจสอบว่ามีโฟลเดอร์ `uploads/` อยู่ในโปรเจกต์ หากยังไม่มีให้สร้างโฟลเดอร์ชื่อ `uploads` ไว้ที่ Root ของโปรเจกต์

4. **ตั้งค่าฐานข้อมูล:**
   - เปิดบราวเซอร์ไปที่ `http://localhost/phpmyadmin`
   - ไปที่แท็บ **SQL** แล้วคัดลอกคำสั่ง SQL ด้านบนไปวางเพื่อสร้าง Database และ Tables
   - หากมีการเปลี่ยน Password ของ MySQL ให้แก้ไขไฟล์ `db.php` ตามข้อมูลของท่าน:
     ```php
     $host = "localhost";
     $dbname = "foods_repice_db";
     $user = "root";
     $pass = ""; // กำหนดรหัสผ่าน MySQL ของท่าน
     ```

5. **เข้าใช้งานระบบ:**
   - เปิดบราวเซอร์แล้วระบุ URL: [http://localhost/W5_Food_Repice/index.php](http://localhost/W5_Food_Repice/index.php)

---

## 💡 แนวคิดด้านสถาปัตยกรรมและการออกแบบ (Architecture & Design Notes)

- **Prepared Statements (PDO):** เพิ่มความปลอดภัยในการเชื่อมต่อและสืบค้นฐานข้อมูล เพื่อป้องกันการโจมตีประเภท SQL Injection
- **Aggregation / 1-to-Many Data Model:** แยกตารางข้อมูลอาหารหลัก (`foods`) และสูตรวัตถุดิบ (`recipes`) เพื่อรองรับโครงสร้างข้อมูลแบบยืดหยุ่นที่ 1 เมนูสามารถมีกี่วัตถุดิบก็ได้
- **HCI (Human-Computer Interaction) Principles:** 
  - สไตล์ visual ด้วย Bootstrap 5 ให้โทนสีสบายตา จัดวางคอมโพเนนต์ชัดเจน
  - Responsive layout รองรับการแสดงผลทุกขนาดหน้าจอ
  - Interactive Form Management ใช้ JavaScript ควบคุมการเพิ่ม-ลดช่องกรอกวัตถุดิบโดยไม่ต้องรีโหลดหน้าเว็บ