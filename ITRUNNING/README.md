# 🏃 RunLan - ระบบเว็บวิ่ง (RunLan Web Application)

ระบบเว็บรับสมัครและปฏิทินงานวิ่ง (RunLan) พัฒนาด้วย **PHP 8 (Native PDO)**, **HTML5**, **TailwindCSS**, **FullCalendar JS** และ **MySQL** ออกแบบด้วย UI/UX ระดับพรีเมียม สไตล์ Glassmorphism สวยงาม ทันสมัย ใช้งานได้สมบูรณ์แบบบนทุกอุปกรณ์ (Mobile, Tablet, Desktop)

---

## 📁 โครงสร้างโปรเจกต์ (Project Structure)

```text
RUNLAN/
├── config/
│   ├── db.php                  # การเชื่อมต่อฐานข้อมูล Native PDO (UTF8MB4, ERRMODE_EXCEPTION, FETCH_ASSOC)
│   └── functions.php           # Helper Functions (XSS Sanitizer, วันที่ภาษาไทย, สุ่มรหัส Booking Code, Flash Alerts)
├── includes/
│   ├── header.php              # ส่วนหัว HTML, CDN TailwindCSS, ฟอนต์ Kanit, Lucide Icons
│   ├── navbar.php              # แถบเมนูนำทางหลัก และช่องค้นหา E-Ticket ด่วน
│   └── footer.php              # ส่วนท้ายเว็บ ลิงก์ และสคริปต์เสริม
├── assets/
│   ├── css/
│   │   └── custom.css          # Glassmorphism, Micro-animations, Print Styles สำหรับ E-Ticket
│   └── js/
│       └── app.js              # สคริปต์ฝั่ง Client (Live Preview สลิป, Mobile Nav Drawer)
├── uploads/
│   └── slips/                  # จัดเก็บสลิปโอนเงิน (พร้อม .htaccess ป้องกันการรัน Script)
├── schema.sql                  # สคริปต์สร้างฐานข้อมูล MySQL พร้อม Mock Data งานวิ่งไทยยอดนิยม 4 งาน
├── index.php                   # หน้ารายการงานวิ่งที่เปิดรับสมัคร ดีไซน์เป็นการ์ด Responsive + ค้นหา/ฟิลเตอร์
├── calendar.php                # หน้าปฏิทินงานวิ่ง Interactive ด้วย FullCalendar JS
├── api_events.php              # Backend REST API ส่งข้อมูล JSON ให้ FullCalendar
├── event-detail.php            # รายละเอียดงานวิ่ง, รางวัล, แผนที่จำลอง, สถิติยอดวิว (Auto Increment)
├── register.php                # ฟอร์มสมัครวิ่ง, เลือกประเภท, ไซส์เสื้อ, พร้อมระบบคำนวณราคาและแนบสลิป
├── process_register.php        # ประมวลผลสมัคร (DB Transaction, Check Quota, File Validation, Prevent SQL Injection)
├── confirmation.php            # หน้ายืนยันการจอง บัตรจำลอง E-Ticket พร้อม QR Code และรองรับการพิมพ์
└── README.md                   # เอกสารคู่มือการติดตั้งและการใช้งาน
```

---

## 🗄️ โครงสร้างฐานข้อมูล (Database Schema)

ตารางหลัก 3 ตารางใน `schema.sql`:

1. **`events`**: ข้อมูลกิจกรรมงานวิ่ง
   - `id`, `title`, `location`, `event_date`, `registration_end_date`, `categories`, `view_count`, `rewards_detail`, `banner_image`, `created_at`
2. **`event_categories`**: หมวดหมู่และระยะทางของแต่ละงาน
   - `id`, `event_id`, `category_name`, `price`, `max_slots`, `booked_slots`
3. **`registrations`**: ข้อมูลการลงทะเบียนสมัครของนักวิ่ง
   - `id`, `event_id`, `category_id`, `booking_code` (UNIQUE), `full_name`, `email`, `phone`, `shirt_size`, `slip_image`, `payment_status` (ENUM: 'pending', 'confirmed'), `registered_at`
   - **`UNIQUE KEY (event_id, email)`**: ป้องกันการสมัครซ้ำด้วยอีเมลเดิมในงานเดียวกัน

---

## 🚀 ขั้นตอนการติดตั้งและรันโปรเจกต์ (Installation & Running)

### 1. นำเข้าฐานข้อมูล MySQL
เปิดโปรแกรมจัดการฐานข้อมูล เช่น phpMyAdmin, MySQL Workbench หรือ Command Line แล้วรันคำสั่งจากไฟล์ `schema.sql`:
```bash
mysql -u root -p < schema.sql
```
*(สคริปต์จะสร้างฐานข้อมูลชื่อ `runlan_db` พร้อมตารางและข้อมูล Mock Data ให้ทันที)*

### 2. ตั้งค่าการเชื่อมต่อฐานข้อมูล
ตรวจสอบหรือแก้ไขข้อมูลเชื่อมต่อ MySQL ในไฟล์ [`config/db.php`](file:///config/db.php):
```php
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'runlan_db';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
```

### 3. รันเซิร์ฟเวอร์ PHP Built-in Server
เปิด Terminal ในโฟลเดอร์โปรเจกต์แล้วรันคำสั่ง:
```bash
php -S localhost:8000
```
จากนั้นเปิดเบราว์เซอร์ไปที่: **`http://localhost:8000`**

---

## 🔒 มาตรการความปลอดภัยและ Best Practices

- ✅ **SQL Injection Prevention**: ใช้ Native PDO Prepared Statements 100%
- ✅ **Database Transactions**: ใช้ `beginTransaction()`, `commit()`, `rollBack()` ร่วมกับ `SELECT ... FOR UPDATE` ป้องกันข้อผิดพลาดในการตัดโควตาซ้ำซ้อน (Overbooking Race Condition)
- ✅ **File Upload Security**:
  - ตรวจสอบ MIME Type จริงผ่าน `finfo_file` (เฉพาะ `image/jpeg`, `image/png`, `image/webp`)
  - ตรวจสอบนามสกุลและจำกัดขนาดไฟล์ไม่เกิน 2MB
  - สุ่มชื่อไฟล์ใหม่ด้วย `bin2hex(random_bytes(16))` ป้องกัน Path Traversal
  - มี `.htaccess` ในโฟลเดอร์ `uploads/slips/` เพื่อบล็อกการรันสคริปต์
- ✅ **XSS Protection**: เข้ารหัส HTML Entities ทุกจุดที่แสดงผลด้วยฟังก์ชัน `e()`
- ✅ **Clean Code**: โค้ดมีการแยก Layer ชัดเจนและมีคอมเมนต์ภาษาไทยอธิบายทุกฟังก์ชัน
