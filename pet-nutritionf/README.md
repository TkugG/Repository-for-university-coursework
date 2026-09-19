# 🐾 PetCare Nutrition Studio

ระบบคำนวณสารอาหารและจัดตารางมื้ออาหารสัตว์เลี้ยง (สุนัขและแมว) ตามมาตรฐานสัตวแพทย์ (AAFCO / NRC)  
พัฒนาด้วยสถาปัตยกรรม **Clean Object-Oriented Programming (PHP 8.2)** แบ่งเป็น **4 คลาสหลัก** พร้อมระบบจัดการฐานข้อมูลสมาชิก (PDO / MySQL) และหน้าเว็บ UI ทันสมัย

---

## 📁 โครงสร้างโปรเจกต์ (Project Structure)

```
pet-nutrition/
├── assets/
│   ├── css/
│   │   └── main.css           🎨 สไตล์ตกแต่งหน้าเว็บ Modern Scandinavian UI
│   └── js/
│       └── app.js             ⚡ ระบบคำนวณสารอาหาร, จัดการ UI, และติดต่อ API ฝั่ง Client
├── classes/
│   ├── Pet.php                🟢 ก้อนที่ 1: คลาสแม่ Pet และคลาสลูก Dog, Cat (Inheritance & Polymorphism)
│   ├── Food.php               🟡 ก้อนที่ 2: คลาส Food เก็บข้อมูลอาหารและคำนวณพลังงานต่อกรัม
│   ├── FeedingCalculator.php  🔵 ก้อนที่ 3: คลาส FeedingCalculator คำนวณ RER, DER, และปริมาณอาหาร/วัน
│   └── FeedingSchedule.php    🟣 ก้อนที่ 4: คลาส FeedingSchedule แบ่งมื้อและคำนวณช่วงเวลามื้ออาหาร
├── config/
│   └── db.php                 🔌 เชื่อมต่อฐานข้อมูล MySQL ผ่าน PDO
├── api.php                    ⚡ Backend REST API (จัดการ Login, Register, Save, Get, Delete Pet)
├── index.php                  🎨 หน้าหลักของแอปพลิเคชัน (UI / Form / Dashboard / Modals)
└── README.md                  📖 คู่มือการใช้งานและเอกสารสถาปัตยกรรม
```

---

## 🏗️ สถาปัตยกรรม 4 คลาสหลัก (OOP Architecture)

โครงสร้างคลาสถูกออกแบบตามหลัก Object-Oriented Programming ชัดเจน รองรับการส่งต่อ Object ระหว่างกันเพื่อประมวลผล:

```
                           ┌──────────────────────────┐
                           │       Pet (Base)         │
                           │  - name, weight          │
                           │  - ageStage, isNeutered  │
                           └────────────┬─────────────┘
                                        │ extends
                   ┌────────────────────┴────────────────────┐
                   ▼                                         ▼
        ┌──────────────────────┐                  ┌──────────────────────┐
        │         Dog          │                  │         Cat          │
        │ - breedSize          │                  │ - livingHabit        │
        │ + getDERMultiplier() │                  │ + getDERMultiplier() │
        └──────────────────────┘                  └──────────────────────┘
                   │                                         │
                   └────────────────────┬────────────────────┘
                                        │ passed into
                                        ▼
┌────────────────────────┐         ┌─────────────────────────────────┐
│         Food           │         │        FeedingCalculator        │
│ - name, foodType       │───────> │ + calculateRER(weight)          │
│ - caloriesPer100g      │         │ + calculateDER(Pet)             │
│ + getCaloriesPerGram() │         │ + calculateDailyPortion(Pet,Food│
└────────────────────────┘         └────────────────┬────────────────┘
                                                    │ produces dailyGrams
                                                    ▼
                                    ┌─────────────────────────────────┐
                                    │         FeedingSchedule         │
                                    │ - mealsPerDay, firstMealTime    │
                                    │ + calculatePortionPerMeal()     │
                                    │ + getMealSlots()                │
                                    └─────────────────────────────────┘
```

### 1. ก้อนสัตว์เลี้ยง (`classes/Pet.php`)
* **`abstract class Pet`:** คลาสแม่เก็บข้อมูลพื้นฐาน เช่น `name`, `weight` (กก.), `ageStage` (ลูกสัตว์/โตเต็มวัย/สูงวัย), `activityLevel` (น้อย/ปกติ/คึกคัก) และ `isNeutered` (ทำหมันแล้วหรือไม่)
* **`class Dog extends Pet`:** สืบทอดจาก `Pet` เพิ่มคุณลักษณะ `breedSize` (พันธุ์เล็ก/กลาง/ใหญ่) และคำนวณตัวคูณ DER สำหรับสุนัข
* **`class Cat extends Pet`:** สืบทอดจาก `Pet` เพิ่มคุณลักษณะ `livingHabit` (เลี้ยงในบ้าน Indoor / นอกบ้าน Outdoor) คำนวณตัวคูณ DER สำหรับแมว

### 2. ก้อนอาหาร (`classes/Food.php`)
* เก็บข้อมูลประเภทอาหาร (`dry` อาหารเม็ด / `wet` อาหารเปียก)
* รับค่าพลังงาน `caloriesPer100g` (kcal/100g) ตามฉลากจริงในท้องตลาด
* มี Method `getCaloriesPerGram()` สำหรับแปลงค่าพลังงานเป็น kcal/g เพื่อนำไปใช้คำนวณปริมาณกรัมอาหาร

### 3. ก้อนเครื่องคำนวณ (`classes/FeedingCalculator.php`)
* ประมวลผลตรรกะทางโภชนาการ (Domain Logic):
  - `calculateRER($weight)`: หาพลังงานขณะพักผ่อน $RER = 70 \times (\text{weight})^{0.75}$
  - `calculateDER(Pet $pet)`: คำนวณพลังงานที่ต้องการต่อวันโดยนำ RER คูณกับค่า Multiplier จาก `$pet->getDERMultiplier()`
  - `calculateDailyPortion(Pet $pet, Food $food)`: คำนวณปริมาณอาหารรวมต่อวันเป็นกรัม

### 4. ก้อนจัดตารางเวลา (`classes/FeedingSchedule.php`)
* `calculatePortionPerMeal($dailyGrams)`: คำนวณแบ่งปริมาณอาหารตามจำนวนมื้อต่อวัน
* `getMealSlots($dailyGrams)`: คำนวณและกระจายช่วงเวลาให้อาหารในแต่ละมื้อโดยเริ่มจากเวลามื้อแรก (`firstMealTime`)

---

## 🧬 สูตรคำนวณมาตรฐานสัตวแพทย์ (Veterinary Standards)

| ค่าที่คำนวณ | สูตร / Formula | คำอธิบาย |
|---|---|---|
| **RER** | $70 \times (\text{weight\_kg})^{0.75}$ | Resting Energy Requirement (พลังงานพื้นฐานขณะพักผ่อน) |
| **DER** | $RER \times \text{DER Multiplier}$ | Daily Energy Requirement (พลังงานที่ต้องการจริงต่อวัน) |
| **Daily Portion** | $\frac{DER}{\text{Calories Per Gram}}$ | ปริมาณอาหารรวมต่อวัน (กรัม/วัน) |
| **Portion per Meal** | $\frac{\text{Daily Portion}}{\text{Meals Per Day}}$ | ปริมาณอาหารต่อมื้อ (กรัม/มื้อ) |
| **Water Requirement** | $\text{weight\_kg} \times 55$ | ปริมาณน้ำดื่มที่ควรได้รับต่อวัน (มล./วัน) |

---

## 💻 ฟีเจอร์หลักของระบบ (Features)

1. **ระบบคำนวณโภชนาการแบบ Real-time:**
   - คำนวณตามชนิดสัตว์เลี้ยง (สุนัข/แมว), น้ำหนัก, ช่วงวัย, ระดับกิจกรรม, การทำหมัน และขนาดสายพันธุ์
   - คำนวณตามประเภทอาหาร (เม็ด/เปียก) พร้อมปุ่ม Quick Presets ค่าพลังงานมาตรฐานตามท้องตลาด (360, 385, 85, 95 kcal/100g)
   - จัดตารางเวลามื้ออาหารแบบไดนามิกตามจำนวนมื้อที่เลือก (1 - 4 มื้อ)

2. **ระบบสมาชิก & จัดการข้อมูลสัตว์เลี้ยง (Database Integration):**
   - ระบบสมัครสมาชิก (Register) และเข้าสู่ระบบ (Login) ผ่าน PHP Session
   - บันทึกโปรไฟล์สัตว์เลี้ยงลงฐานข้อมูล MySQL
   - โหลดข้อมูลสัตว์เลี้ยงที่บันทึกไว้กลับมาแก้ไข/คำนวณใหม่ได้ทันที
   - ปุ่มล้างฟอร์มสำหรับเพิ่มสัตว์เลี้ยงตัวใหม่ (`➖ ล้างฟอร์ม`) และลบข้อมูลสัตว์เลี้ยง (`🗑️ ลบ`)

---

## 🚀 วิธีเปิดใช้งานระบบ (Getting Started)

### การตั้งค่าฐานข้อมูล (Database Setup)
1. สร้างฐานข้อมูล MySQL ชื่อ `pet_nutrition_db`
2. สร้างตารางสำหรับผู้ใช้งานและสัตว์เลี้ยง:
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  species ENUM('dog', 'cat') NOT NULL,
  weight DECIMAL(5,2) NOT NULL,
  age_stage VARCHAR(20) NOT NULL,
  activity_level VARCHAR(20) DEFAULT 'normal',
  is_neutered TINYINT(1) DEFAULT 0,
  breed_size VARCHAR(20) DEFAULT 'medium',
  food_type VARCHAR(20) NOT NULL,
  calories_per_100g DECIMAL(6,2) NOT NULL,
  meals_per_day INT NOT NULL,
  first_meal_time VARCHAR(10) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

---

การรันโปรเจกต์ย้ายโฟลเดอร์โปรเจกต์ไปไว้ใน htdocs 
(สำหรับ XAMPP):C:\xampp\htdocs\pet-nutrition\
เปิดเบราว์เซอร์แล้วเข้าใช้งานที่:http://localhost/pet-nutrition/index.php

📡 Backend API Endpoints (api.php)
Endpoint                        Method      คำอธิบาย
api.php?action=check_session    GET         ตรวจสอบสถานะการเข้าสู่ระบบของผู้ใช้  
api.php?action=login            POST        เข้าสู่ระบบ (email, password)
api.php?action=register         POST        สมัครสมาชิกใหม่ (fullname, email, password)
api.php?action=logout           GET         ออกจากระบบ (Destroy Session)
api.php?action=get_pets         GET         ดึงรายการสัตว์เลี้ยงทั้งหมดของผู้ใช้ที่ล็อกอินอยู่
api.php?action=save_pet         POST        บันทึกหรืออัปเดตข้อมูลสัตว์เลี้ยง
api.php?action=delete_pet       POST        ลบข้อมูลสัตว์เลี้ยง (pet_id)
api.php?action=calculate        POST        คำนวณสารอาหารผ่าน 4 Core OOP Classes

```
