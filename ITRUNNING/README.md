# 🏃 IT วิ่งเข้าป่ามัน - แพลตฟอร์มงานวิ่งและเทรลธรรมชาติ (RunLan Platform)

ระบบเว็บรับสมัคร ปฏิทินงานวิ่ง และตรวจสอบผลการแข่งขัน (Web Application) พัฒนาด้วย **PHP 8 (Native PDO)**, **HTML5 / Vanilla JS**, **TailwindCSS**, **FullCalendar JS** และ **MySQL** ได้รับการจัดระเบียบโครงสร้างตามมาตรฐาน Web Development สากล (Clean Architecture) ปลอดภัย และดูแลรักษาง่าย

---

## 📁 โครงสร้างโปรเจกต์ (Project Structure)

```text
ITRUNNING/
├── config/                     # ⚙️ โฟลเดอร์ตั้งค่าระบบและการเชื่อมต่อฐานข้อมูล
│   ├── app.php                 # ค่าคงที่ระบบ, Timezone, Session และ Path Definitions
│   └── db.php                  # การเชื่อมต่อฐานข้อมูล Native PDO (UTF8MB4, ERRMODE_EXCEPTION)
├── includes/                   # 🧩 โฟลเดอร์คอมโพเนนต์และฟังก์ชันส่วนกลาง
│   ├── header.php              # โครงสร้าง <head>, Google Fonts (Prompt & Kanit), Tailwind Config, CSS CDN
│   ├── navbar.php              # เมนูนำทาง (Desktop & Mobile Drawer) และช่องค้นหา E-Ticket ด่วน
│   ├── footer.php              # ส่วนท้ายเว็บไซต์, โหลด JS และเปิดใช้งาน Lucide Icons
│   └── functions.php           # Helper Functions (e() ป้องกัน XSS, วันที่ภาษาไทย, Format เงิน, Flash Alerts)
├── api/                        # ⚡ โฟลเดอร์ Backend REST JSON API
│   └── events.php              # API ส่งข้อมูล JSON งานวิ่งสำหรับ FullCalendar JS
├── assets/                     # 🎨 ทรัพยากรและสไตล์ฝั่ง Client
│   ├── css/
│   │   └── custom.css          # สไตล์ Glassmorphism, Animations, Print Rules สำหรับ E-Ticket
│   └── js/
│       └── app.js              # สคริปต์จัดการ Live Preview รูปภาพ, Mobile Menu, Flash Alerts
├── database/                   # 🗄️ ไฟล์ฐานข้อมูลและ Schema
│   └── schema.sql              # สคริปต์สร้างฐานข้อมูล MySQL พร้อม Mock Data 16 งานวิ่ง + ผลการแข่งขัน
├── uploads/                    # 📁 โฟลเดอร์จัดเก็บไฟล์อัปโหลดจากผู้ใช้
│   ├── .htaccess               # 🔒 ป้องกันการ Execute สคริปต์ในโฟลเดอร์อัปโหลด
│   ├── photos/                 # รูปถ่ายหน้าตรงนักวิ่งสำหรับพิมพ์ BIB
│   └── slips/                  # สลิปหลักฐานการโอนเงิน
├── index.php                   # [Page] หน้าหลัก ค้นหาและฟิลเตอร์รายการงานวิ่ง
├── event-detail.php            # [Page] รายละเอียดงานวิ่ง หมวดหมู่ระยะทาง และของรางวัล
├── register.php                # [Page] ฟอร์มสมัครวิ่ง คำนวณราคา และอัปโหลดหลักฐาน
├── confirmation.php            # [Page] หน้าแสดงบัตร E-Ticket พร้อม QR Code และรองรับการพิมพ์
├── calendar.php                # [Page] ปฏิทินงานวิ่ง Interactive (FullCalendar JS)
├── results.php                 # [Page] ตรวจสอบผลการแข่งขันอย่างเป็นทางการ (Race Results & Leaderboard)
├── organizer.php               # [Page] โซลูชันและฟอร์มติดต่อสำหรับผู้จัดงาน (Organizer & PR)
├── contact.php                 # [Page] ติดต่อสอบถาม แจ้งปัญหา และ FAQ
├── news.php                    # [Page] ข่าวสารประชาสัมพันธ์และเกร็ดความรู้สำหรับนักวิ่ง
├── api_events.php              # [Shim] ตัวเชื่อมต่อเดิมสำหรับ Backward Compatibility
└── README.md                   # เอกสารคู่มือโปรเจกต์ฉบับสมบูรณ์
```

---

## 🗄️ โครงสร้างฐานข้อมูล (Database Schema)

ตารางหลัก 5 ตารางใน `database/schema.sql`:

1. **`events`**: ข้อมูลกิจกรรมงานวิ่ง (ชื่องาน, สถานที่, วันเวลาแข่งขัน, วันปิดรับสมัคร, ยอดวิว, แบนเนอร์)
2. **`event_categories`**: หมวดหมู่และระยะทางของแต่ละงาน (ชื่อรุ่น, ราคา, โควตารับสมัคร, ยอดจอง)
3. **`registrations`**: ข้อมูลการลงทะเบียนสมัครของนักวิ่ง (Booking Code, ชื่อ, อีเมล, เบอร์โทร, ไซส์เสื้อ, รูปนักวิ่ง, สลิป)
4. **`race_results`**: ผลการแข่งขัน (BIB, ชื่อน้องวิ่ง, Gun Time, Net Time, อันดับ Overall, Pace)
5. **`contact_messages`**: ข้อความติดต่อและสอบถามจากหน้าเว็บ

---

## 🚀 ขั้นตอนการติดตั้งและรันโปรเจกต์ (Installation & Running)

### 1. นำเข้าฐานข้อมูล MySQL
นำเข้าไฟล์ `database/schema.sql` ใน phpMyAdmin หรือผ่าน Command Line:
```bash
mysql -u root -p < database/schema.sql
```

### 2. ตั้งค่าการเชื่อมต่อฐานข้อมูล
ตรวจสอบหรือแก้ไขค่าในไฟล์ [`config/db.php`](file:///config/db.php) ให้ตรงกับเครื่องหรือโฮสติ้งของคุณ

### 3. รันเซิร์ฟเวอร์
- **บน XAMPP / Apache**: เปิดโปรเจกต์ผ่าน `http://localhost/ITRUNNING/`
- **บน PHP Built-in Server**:
  ```bash
  php -S localhost:8000
  ```
  จากนั้นเปิดเบราว์เซอร์ไปที่: `http://localhost:8000`

---

## 🔒 ความปลอดภัยและมาตรฐานการพัฒนา (Best Practices)

- 🛡️ **SQL Injection Prevention**: ใช้ Native PDO Prepared Statements 100% ในทุกส่วนของระบบ
- 🛡️ **XSS Attack Defense**: เข้ารหัส HTML Output ปลอดภัยด้วยฟังก์ชัน `e()` ใน `includes/functions.php`
- 🛡️ **File Upload Security**: ตรวจสอบนามสกุลไฟล์ที่อนุญาต สุ่มสร้างชื่อไฟล์ และบล็อกการรันสคริปต์ด้วย `.htaccess`
- 🛡️ **Responsive & Clean UI**: รองรับทุกหน้าจอด้วย Tailwind CSS, Google Fonts และ Glassmorphism Design
