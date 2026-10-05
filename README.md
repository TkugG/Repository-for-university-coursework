# Project Note & To-Do (เว็บแอปจดโน๊ตสไตล์โมบาย)

โปรเจกต์เว็บแอปพลิเคชันจดบันทึกและจัดการรายการสิ่งที่ต้องทำ (To-Do List) พัฒนาด้วย **PHP OOP (Object-Oriented Programming)** เชื่อมต่อฐานข้อมูล **MySQL** และแสดงผลผ่านหน้าเว็บแบบ Modern Web Application

---

## 👥 การแบ่งหน้าที่ในทีม (Roles & Responsibilities)

| บทบาท | ผู้รับผิดชอบ | หน้าที่หลัก |
| :--- | :--- | :--- |
| **Frontend** | เพื่อนร่วมทีม | ดูแลหน้าจอ UI (`index.html`, `assets/css/`, `assets/js/`), จัดการ Layout (Navbar, Sidebar, การ์ด 5 สี, Modal), เชื่อมต่อ API ด้วย JavaScript `fetch` |
| **Backend** | ฉัน (คุณ) | ดูแลฐานข้อมูล (`database.sql`, phpMyAdmin), เขียนคลาส PHP OOP (`models/`), จัดการ API รับ-ส่งข้อมูล JSON (`api/`) |

---

## 📁 โครงสร้างโปรเจกต์ (Folder Structure)

```text
ProjectOOP/
│
├── assets/                 <-- [Frontend] โฟลเดอร์ของเพื่อน
│   ├── css/                <-- ไฟล์แต่งหน้าตา (style.css)
│   └── js/                 <-- ไฟล์สคริปต์ (app.js, notifications.js)
│
├── api/                    <-- [Backend] จุดรับ-ส่งข้อมูล JSON (Controller)
│   └── notes.php           <-- จัดการ ดึง/เพิ่ม/ลบ โน๊ต
│
├── models/                 <-- [Backend] คลาส PHP OOP (Database, Note, Category)
│   ├── Database.php        
│   └── Note.php            
│
├── uploads/                <-- โฟลเดอร์เก็บรูปภาพที่ผู้ใช้อัปโหลด
├── database.sql            <-- ไฟล์โครงสร้างฐานข้อมูล (นำไป Import เข้า phpMyAdmin)
├── index.html              <-- [Frontend] หน้าเว็บหลัก
└── README.md               <-- คู่มือโปรเจกต์นี้
```

---

## 🚀 วิธีติดตั้งและเปิดโปรเจกต์ (สำหรับเพื่อนร่วมทีม)

1. **เปิดโปรแกรม XAMPP**:
   - กดปุ่ม **Start** ที่โมดูล **Apache** และ **MySQL**
2. **นำเข้าฐานข้อมูล (Import Database)**:
   - เปิดบราวเซอร์ไปที่: `http://localhost/phpmyadmin`
   - คลิกเมนู **Import** ด้านบน
   - กดปุ่ม **Choose File** แล้วเลือกไฟล์ `database.sql` ในโฟลเดอร์โปรเจกต์นี้
   - เลื่อนลงมากดปุ่ม **Import (หรือ Go)** ด้านล่างสุด
   - *(ระบบจะสร้างฐานข้อมูลชื่อ `project_note_db` พร้อมตารางและข้อมูลตัวอย่างให้อัตโนมัติ)*
3. **เปิดหน้าเว็บ**:
   - เปิดบราวเซอร์ไปที่: `http://localhost/ProjectOOP`

---

## 📋 ข้อตกลงข้อมูล (Data Contract สำหรับ Frontend และ Backend)

### 1. หมวดหมู่ (Categories)
- `1`: ทั่วไป
- `2`: การเรียน
- `3`: งาน
- `4`: ส่วนตัว

### 2. รหัสสีการ์ด 5 สี (Color Palette)
- สีเหลือง (Yellow): `#fef08a`
- สีเขียว (Green): `#bbf7d0`
- สีฟ้า (Blue): `#bfdbfe`
- สีชมพู (Pink): `#fbcfe8`
- สีม่วง (Purple): `#e9d5ff`

### 3. ฟิลด์ข้อมูลในโน๊ต (Note Fields)
- `title` (ข้อความ): หัวข้อโน๊ต
- `category_id` (ตัวเลข): รหัสหมวดหมู่
- `type` (text หรือ todo): ชนิดของโน๊ต
- `content` (ข้อความ): เนื้อหาโน๊ต (เฉพาะ text note)
- `image` (ไฟล์): ภาพแนบ (เฉพาะ text note)
- `color` (รหัสสี): เช่น `#fef08a`
- `reminder_at` (วันเวลา): เช่น `2026-10-06 18:00:00` (สำหรับ todo ที่ต้องการเตือน)

---

## 🛡️ กฎเหล็กในการใช้ Git ร่วมกัน

1. **ไม่แก้ไฟล์เดียวกัน:** Frontend แก้ไขเฉพาะ `index.html` และโฟลเดอร์ `assets/` เท่านั้น / Backend แก้ไขเฉพาะ `api/` และ `models/`
2. **ดึงงานก่อนส่งเสมอ (Pull ก่อน Push):**
   ```bash
   git add .
   git commit -m "อธิบายสิ่งที่ทำ"
   git pull
   git push
   ```
