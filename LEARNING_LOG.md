# บันทึกการเรียนรู้และการพัฒนา (Development & Learning Log)

บันทึกนี้จัดทำขึ้นเพื่อทบทวนแนวคิด **OOP / OOAD** และขั้นตอนการพัฒนาทีละส่วนอย่างละเอียด

---

## 📌 สรุปสิ่งที่เราทำสำเร็จไปแล้ว

### 1. วางแผนระบบตามหลัก OOAD & MVC
* **Concept:** แบ่งงานแบบ Decoupled MVC (Frontend และ Backend แยกกันคุยผ่าน JSON API)
  * **View (Frontend):** `index.html` + `assets/` (จัดการการแสดงผล, UI, การ์ดสี)
  * **Controller (API):** `api/` (รับคำสั่งจากหน้าเว็บ แล้วสั่ง Model)
  * **Model (OOP Logic):** `models/` (คลาสจัดการฐานข้อมูลและธุรกิจ เช่น `Note`, `Database`)
* **ประโยชน์:** ทำงานร่วมกับเพื่อนได้พร้อมกันโดยไม่ตีกัน (Parallel Development)

### 2. ออกแบบฐานข้อมูล (Database Schema)
* สร้าง 3 ตารางใน `database.sql`:
  1. `categories` (หมวดหมู่)
  2. `notes` (ข้อมูลโน๊ตหลัก รองรับทั้ง text และ todo)
  3. `todo_items` (รายการย่อยที่ติ๊กถูกได้ 1 โน๊ตมีได้หลายข้อ)
* **ทำไมต้องทำก่อน?** เพื่อเป็น "Data Contract" ให้ Frontend รู้ว่าต้องสร้างฟอร์มรับค่าอะไร และ Backend รู้ว่าต้องเขียน Class อะไร

### 3. ตั้งค่า Git สำหรับทำงานคู่ (Pair Programming)
* แยก Branch สะอาด `project-note-oop`
* กฎเหล็ก: *Pull ก่อน Push เสมอ* และแยกโฟลเดอร์กันแก้ ป้องกัน Git Conflict 100%

---

## 🎯 แผนขั้นตอนถัดไป (Roadmap)
- [x] **Step 1:** สร้างคลาสเชื่อมต่อฐานข้อมูล (`models/Database.php`) ด้วย PDO
  * **สิ่งที่ได้เรียนรู้:** 
    * ทำความเข้าใจ `class Database` และการห่อหุ้มคุณสมบัติ (Encapsulation)
    * ทำไมใช้ `private` กับข้อมูล Credentials? (เพื่อความปลอดภัย ไม่ให้คลาสภายนอกเข้ามาอ่านหรือแก้ค่าได้โดยตรง)
    * ทำไมต้องใช้ `PDO`? (เป็นมาตรฐาน OOP ของ PHP มี Prepared Statement ป้องกัน SQL Injection ได้ดีกว่า `mysqli`)
    * การใช้ `try-catch` จับ `PDOException` เพื่อดักจับข้อผิดพลาดกรณีเซิร์ฟเวอร์ฐานข้อมูลดับ

- [x] **Step 2:** ออกแบบคลาสหลัก `Note.php` (Inheritance & CRUD)
  * **สิ่งที่ได้เรียนรู้:**
    * **Dependency Injection (DI):** การส่ง `$db` เข้ามาทาง `__construct($db)` เพื่อให้คลาสใช้ connection ร่วมกัน ไม่ต้องเปลืองทรัพยากรสร้างใหม่
    * **Dynamic Query Building:** การเขียน SQL รองรับทั้งการค้นหา (`LIKE`), การกรองหมวดหมู่ (`category_id`), และจัดเรียงลำดับ (`ORDER BY created_at ASC/DESC`) ในฟังก์ชันเดียว
    * **SQL Prepared Statements:** การใช้ `bindValue()` เพื่อป้องกัน SQL Injection เมื่อรับข้อมูลจากผู้ใช้
    * **Data Aggregation:** ถ้าโน๊ตเป็นประเภท `todo` ให้ดึงรายการย่อยจาก `todo_items` มาแนบใน Array ทันที เพื่อให้ Frontend นำไปวนลูปแสดงผลได้ทันทีโดยไม่ต้องยิงหลายรอบ

- [x] **Step 3:** สร้าง API Endpoint แรก (`api/notes.php`) สำหรับดึงรายการโน๊ต (GET)
  * **สิ่งที่ได้เรียนรู้:**
    * **Header JSON:** การใส่ `header("Content-Type: application/json")` เพื่อส่งข้อมูลในรูปแบบมาตรฐานสากลที่ Frontend นำไปใช้ต่อได้ทันที
    * **HTTP Method Routing:** ใช้ `$_SERVER['REQUEST_METHOD']` เป็นตัวแยกแยะคำสั่ง (GET, POST, DELETE) ทำหน้าที่เป็น Controller ตามหลัก MVC
    * **CORS & Preflight:** ใส่ Header รองรับ `Access-Control-Allow-*` เพื่อไม่ให้หน้าเว็บบล็อกการเรียก API

- [x] **Step 4:** สร้างระบบบันทึกโน๊ตใหม่ (POST) พร้อมอัปโหลดรูปภาพ
  * **สิ่งที่ได้เรียนรู้:**
    * **File Upload Handling:** ตรวจสอบไฟล์ด้วย `$_FILES['image']`, ใช้ `uniqid()` เปลี่ยนชื่อไฟล์เพื่อไม่ให้ชื่อซ้ำกัน, และย้ายไฟล์ไปไว้ในโฟลเดอร์ `uploads/` ด้วย `move_uploaded_file()`
    * **HTTP Status Codes:** ตอบกลับ `201 Created` เมื่อสร้างสำเร็จ, `400 Bad Request` เมื่อข้อมูลไม่ครบ (เช่น ลืมใส่ title)

- [ ] **Step 5:** ระบบจัดการ To-Do List (ติ๊กถูก/ลบ/แจ้งเตือน)
