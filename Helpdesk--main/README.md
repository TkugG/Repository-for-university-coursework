# 🚀 Smart IT Helpdesk & Notification System
### Custom OOP PHP 8.2+ (No Framework) • PSR-4 • PDO • MVC • Observer Pattern • Email Notification Engine

ระบบบริหารจัดการงานแจ้งซ่อมฝ่ายไอที (IT Helpdesk) และระบบแจ้งเตือนอัตโนมัติ ออกแบบและพัฒนาด้วยสถาปัตยกรรม **Pure Object-Oriented PHP 8.2+ (ไม่ใช้ Framework)** ตามมาตรฐาน **PSR-4**, **Repository Pattern**, **Service Layer (State Machine)**, **Event Dispatcher & Observer Pattern**, พร้อมระบบส่งอีเมลแจ้งเตือนผ่าน **Email Notification Engine (PHPMailer / HTML Templates)** ครอบคลุมการทำงาน 3 บทบาท (User, Technician, Admin)

---

## 🌟 จุดเด่นของสถาปัตยกรรม (Architecture Highlights)

1. **Custom MVC + Service + Repository Pattern:**
   - แยก **Business Logic** ออกจาก Controller อย่างชัดเจน
   - **Repository Layer** ทำหน้าที่เป็น Data Access Object (DAO) จัดการ Query ทั้งหมด
   - **Service Layer (`TicketStatusService`)** ควบคุมกฎการเปลี่ยนสถานะ (State Machine Validation) พร้อม Database Transaction
2. **Event-Driven Observer Pattern:**
   - เมื่อสถานะของ Ticket เปลี่ยนแปลง จะยิง Domain Events ผ่าน `EventDispatcher`
   - `TicketObserver` ทำหน้าที่ดักจับ Event และเรียก `EmailNotificationService` ส่งอีเมล HTML อัตโนมัติ
3. **Email Notification Engine:**
   - ส่งอีเมลแจ้งเตือนอัตโนมัติพร้อม Responsive HTML Email Templates สำหรับทุกขั้นตอน
   - รองรับโหมด `log` (บันทึกไฟล์ HTML ใน `storage/mail/`), `smtp` (PHPMailer SMTP), และ native `mail()`
4. **Role-Based Access Control (RBAC):**
   - ตรวจสอบสิทธิ์อย่างรัดกุมผ่าน `AuthMiddleware` และ `RoleMiddleware`
   - มีฟังก์ชัน **1-Click Quick Demo Switcher** สลับบทบาททดสอบระหว่าง Admin, Technician, และ User ได้ทันทีในคลิกเดียว
5. **Database Flexibility:**
   - ออกแบบสำหรับ **MySQL 8.0** (`schema.sql`)
   - รองรับ **SQLite Auto-Fallback** อัตโนมัติ (หากยังไม่ได้เปิด XAMPP MySQL ระบบจะสลับไปใช้ SQLite พร้อมสร้างตารางและ Mock Data ให้อัตโนมัติทันที ทำให้ Run ได้ 100% ทันทีไม่ต้องตั้งค่า)

---

## 📁 โครงสร้างโปรเจกต์ (PSR-4 Directory Structure)

```text
smart-it-helpdesk/
├── composer.json                  # Composer PSR-4 & PHPMailer
├── schema.sql                     # MySQL 8.0 Schema DDL & Seed Data
├── .env.example                   # Environment Template
├── .env                           # Active Configuration
├── test_app.php                   # Automated Test Suite Runner
├── public/                        # Document Root
│   ├── index.php                  # Application Entry Point & Router
│   ├── .htaccess                  # Apache URL Rewrite Rule
│   └── assets/
│       ├── css/style.css          # Custom Stylesheet
│       └── js/app.js              # Vanilla JS & Fetch API Helper
├── src/                           # PSR-4 Root (App\)
│   ├── Core/                      # Core Framework Engine
│   │   ├── Config.php             # .env Parser & Getter
│   │   ├── Database.php           # PDO Singleton (MySQL + SQLite Fallback)
│   │   ├── Request.php            # HTTP Request Abstraction
│   │   ├── Response.php           # HTTP Response & View Renderer
│   │   ├── Router.php             # Custom HTTP Router & Middleware Dispatcher
│   │   ├── Auth.php               # Session Authentication & CSRF
│   │   ├── EventDispatcher.php    # Event Bus (Observer Pattern)
│   │   └── Middleware/            # Middleware Pipeline
│   │       ├── AuthMiddleware.php
│   │       ├── RoleMiddleware.php
│   │       └── CsrfMiddleware.php
│   ├── Controllers/               # Controllers
│   │   ├── AuthController.php     # Login, Register, Logout, Quick Login
│   │   ├── TicketController.php   # User Dashboard, Create, Show, Rate, Reject
│   │   ├── TechnicianController.php # Tech Dashboard, Repair Proof, Status
│   │   ├── AdminController.php    # KPI Dashboard, Assign, Users, Categories, Mail Logs
│   │   └── ApiController.php      # Fetch API JSON Endpoints
│   ├── Services/                  # Business Logic Layer
│   │   ├── TicketStatusService.php# State Machine Transition & Transaction
│   │   ├── EmailNotificationService.php # HTML Email Engine & Transports
│   │   └── FileUploadService.php  # Secure File/Image Upload
│   ├── Repositories/              # Data Access Layer (Repository Pattern)
│   │   ├── RepositoryInterface.php
│   │   ├── UserRepository.php
│   │   ├── CategoryRepository.php
│   │   ├── TicketRepository.php
│   │   ├── CommentRepository.php
│   │   ├── StatusLogRepository.php
│   │   └── RatingRepository.php
│   ├── Enums/                     # PHP 8.2 Backed Enums
│   │   ├── UserRole.php           # User, Technician, Admin
│   │   ├── TicketStatus.php       # Open, Assigned, InProgress, Resolved, Closed
│   │   └── TicketPriority.php     # Low, Medium, High, Critical
│   └── Observers/                 # Domain Observers
│       └── TicketObserver.php     # Listens to Ticket Events -> Sends Emails
├── views/                         # Views & HTML Email Templates
│   ├── layouts/                   # Layout Templates (Navbar, Footer, Flash, Main)
│   ├── auth/                      # Login & Register
│   ├── user/                      # User Views (Dashboard, Create, Show/Timeline)
│   ├── technician/                # Technician Views (Dashboard, Job Detail)
│   ├── admin/                     # Admin Views (Dashboard, Tickets, Users, Categories, Mail Logs)
│   └── emails/                    # HTML Email Notification Templates
│       ├── ticket_created.php
│       ├── ticket_assigned.php
│       ├── status_updated.php
│       └── ticket_resolved.php
└── storage/
    ├── uploads/                   # Uploaded images / Repair proof photos
    ├── logs/                      # Application audit logs
    └── mail/                      # Outgoing HTML email copies for inspection
```

---

## 🗄️ ฐานข้อมูล (Database Schema)

โครงสร้างฐานข้อมูล 6 ตารางหลัก (ตัดฟิลด์ `line_user_id` ออกทั้งหมด):

1. **`users`**: `(id, name, email [UK], password_hash, role ['user','technician','admin'], created_at)`
2. **`categories`**: `(id, name, description)`
3. **`tickets`**: `(id, user_id, category_id, technician_id [nullable], title, description, status ['open','assigned','in_progress','resolved','closed'], priority ['low','medium','high','critical'], created_at, updated_at, resolved_at, closed_at)`
4. **`comments`**: `(id, ticket_id, user_id, body, image_path, created_at)`
5. **`status_logs`**: `(id, ticket_id, changed_by, from_status, to_status, note, created_at)`
6. **`ratings`**: `(id, ticket_id, score [1-5], feedback, created_at)`

---

## 🔑 บัญชีผู้ใช้ตัวอย่าง (Demo Credentials)

ทุกบัญชีใช้รหัสผ่านเดียวกันคือ: **`password123`**

| Role | Email | ชื่อผู้ใช้ | สิทธิ์การเข้าถึง |
|---|---|---|---|
| **Admin** | `admin@helpdesk.local` | Admin Manager | สถิติภาพรวม KPI, จัดการ Ticket, มอบหมายงาน, จัดการ User & Category, Email Logs |
| **Technician** | `tech1@helpdesk.local` | Somchai IT Support | รับงานซ่อม (Start Repair), อัปเดตงาน, แนบรูปถ่ายหลักฐานการซ่อม (Proof Photo) |
| **Technician 2** | `tech2@helpdesk.local` | Wichai Network Engineer | ช่างเทคนิคคนที่ 2 สำหรับทดสอบมอบหมายงาน |
| **User** | `user@helpdesk.local` | Somsri General User | เปิดใบแจ้งซ่อม, ติดตาม Stepper, ตรวจรับงาน, ให้คะแนนดาว 1-5, หรือส่งกลับไปซ่อมใหม่ (Reject) |

> 💡 **เคล็ดลับ:** มีปุ่ม **1-Click Quick Demo Login** ทั้งบนหน้า Login และบน Navbar มุมขวาบน สามารถกดสลับ Role ไป-มา เพื่อทดสอบทั้ง 3 หน้าจอได้ทันทีโดยไม่ต้องจำรหัสผ่าน

---

## 🚦 กฎการเปลี่ยนสถานะ (Ticket State Machine Matrix)

```
[Open] ──(Admin Assign)──> [Assigned] ──(Tech Accept)──> [In Progress]
                                                               │
                                                       (Tech Resolve + Photo)
                                                               │
                                                               ▼
                                                          [Resolved]
                                                          │        │
                                   (User Confirm & Rate)  │        │ (User Reject with Reason)
                                                          ▼        ▼
                                                       [Closed]  [In Progress]
```

1. **`Open` ➔ `Assigned`**: มอบหมายช่างเทคนิค (กระทำโดย Admin)
2. **`Assigned` ➔ `In Progress`**: ช่างกดรับงาน (กระทำโดย Assigned Technician หรือ Admin)
3. **`In Progress` ➔ `Resolved`**: ช่างซ่อมเสร็จและแนบรูปถ่ายผลการซ่อม (กระทำโดย Technician)
4. **`Resolved` ➔ `Closed`**: ผู้แจ้งตรวจสอบอุปกรณ์ กดยืนยันปิดงาน และให้คะแนน 1-5 ดาว (กระทำโดย Ticket Owner)
5. **`Resolved` ➔ `In Progress`**: หากปัญหายังไม่หาย ผู้แจ้งสามารถกดปุ่ม "ปฏิเสธการแก้ไข (Reject)" พร้อมระบุเหตุผลเพื่อส่งให้ช่างดำเนินการใหม่

---

## 📧 ระบบแจ้งเตือนทางอีเมล (Email Notification Engine)

ระบบส่ง HTML Email ใน 4 จังหวะสำคัญ:
1. **Ticket Created:** ส่งอีเมลแจ้งเตือนถึง Admin / IT Team ว่ามีงานใหม่เข้ามา
2. **Ticket Assigned:** ส่งอีเมลแจ้งเตือนถึงช่างเทคนิคที่ได้รับมอบหมายงาน
3. **Status Updated:** ส่งอีเมลแจ้งผู้ใช้งานเมื่อสถานะงานเปลี่ยน (เช่น เริ่มต้นเข้าซ่อม)
4. **Ticket Resolved:** ส่งอีเมลแจ้งผู้ใช้งานพร้อมปุ่ม Link ให้เข้ามากดปิดงานและให้คะแนนความพึงพอใจ

### วิธีตรวจสอบอีเมลใน Local Development:
- ทุกครั้งที่ส่งอีเมล สำเนาไฟล์ `.html` จะถูกบันทึกในโฟลเดอร์ `storage/mail/`

---

## ⚙️ วิธีการติดตั้งและการเปิดใช้งาน (Installation & Run)

### 1. การเปิดใช้งานด้วย PHP Built-in Server
เปิด Terminal ในโฟลเดอร์โปรเจกต์ แล้วรันคำสั่ง:

```bash
php -S localhost:8000 -t public
```

จากนั้นเปิด Browser ไปที่: **[http://localhost:8000](http://localhost:8000)**

### 2. การทดสอบด้วย Automated Test Suite
โปรเจกต์มีชุดทดสอบอัตโนมัติครบ 12 จุดทดสอบ (ตรวจสอบ Database, Repositories, State Machine, Transactions, และ Email Generation):

```bash
php test_app.php
```

### 3. การนำขึ้น Apache / XAMPP (Production)
- นำโฟลเดอร์โปรเจกต์ไปวางใน `htdocs` หรือตั้งค่า VirtualHost ให้ DocumentRoot ชี้ไปที่โฟลเดอร์ `/public`
- รันไฟล์ `schema.sql` ใน MySQL 8.0 ผ่าน phpMyAdmin หรือ MySQL CLI:
  ```bash
  mysql -u root -p it_helpdesk < schema.sql
  ```
- กำหนดค่าใน `.env` ให้ตรงกับฐานข้อมูล MySQL ของคุณ:
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=it_helpdesk
  DB_USERNAME=root
  DB_PASSWORD=
  ```
