# 🐾 PetCare Nutrition Studio

ระบบคำนวณสารอาหารและจัดตารางมื้ออาหารสัตว์เลี้ยง (สุนัขและแมว) ตามมาตรฐานสัตวแพทย์ (AAFCO / NRC)  
พัฒนาด้วย **Clean Object-Oriented Programming (PHP 8.2)** แบ่งเป็น **4 คลาสหลัก** พร้อมหน้าเว็บ UI ทันสมัย

---

## 📁 โครงสร้างโปรเจกต์ใหม่ (4 Core OOP Classes)

```
pet-nutrition/
├── assets/
│   ├── css/
│   │   ├── main.css           🎨 ธีมสี Warm Earthy Brown, UI Layout, Form, Modal
│   │   └── print.css          🖨️ สไตล์เอกสาร A4 Printable Worksheet (ถอดแบบ PNA)
│   └── js/
│       ├── models.js          🧠 คลาส OOP ฝั่ง Frontend (Pet, Dog, Cat, Food, etc.)
│       └── app.js             ⚡ UI Controller, Food Catalog, Event Handlers, Print
├── classes/
│   ├── Pet.php                🟢 ก้อนที่ 1: คลาสแม่ Pet และคลาสลูก Dog, Cat (Inheritance & Polymorphism)
│   ├── Food.php               🟡 ก้อนที่ 2: คลาส Food เก็บข้อมูลอาหารและคำนวณพลังงานต่อกรัม
│   ├── FeedingCalculator.php  🔵 ก้อนที่ 3: คลาส FeedingCalculator คำนวณ RER, DER, และกรัม/วัน
│   └── FeedingSchedule.php    🟣 ก้อนที่ 4: คลาส FeedingSchedule แบ่งมื้อและกระจายเวลากลางวัน
├── tests/
│   ├── test_classes.php       🧪 สคริปต์ Unit Test ทดสอบการทำงานของทั้ง 4 คลาส
│   └── test_api.php           🧪 สคริปต์ทดสอบ API endpoint calculate
├── api.php                    ⚡ REST API Handler (รองรับ action=calculate)
├── index.html                 📄 หน้าเว็บคลีนๆ (Separation of Concerns ~480 บรรทัด)
└── README.md                  📖 คู่มือการใช้งานและเอกสารสถาปัตยกรรม
```

---

## 🏗️ สถาปัตยกรรม 4 คลาสหลัก (OOP Architecture)

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
* **คลาสแม่ (`abstract class Pet`):** เก็บข้อมูลร่วม ได้แก่ `name`, `weight` (กก.), `ageStage` (วัยเจริญเติบโต/โตเต็มวัย/สูงวัย), `activityLevel` (น้อย/ปกติ/คึกคัก), และ `isNeutered` (ทำหมันแล้วหรือไม่)
* **คลาสลูก (`class Dog extends Pet`):** เพิ่มคุณลักษณะ `breedSize` (พันธุ์เล็ก/กลาง/ใหญ่) และคำนวณตัวคูณ DER ตามมาตรฐานสุนัข
* **คลาสลูก (`class Cat extends Pet`):** เพิ่มคุณลักษณะ `livingHabit` (เลี้ยงในบ้าน Indoor / เลี้ยงนอกบ้าน Outdoor) โดยแมวเลี้ยงในบ้านจะปรับลดพลังงานเพื่อป้องกันโรคอ้วน

### 2. ก้อนอาหาร (`classes/Food.php`)
* เก็บ `name`, `foodType` (`dry` เม็ด / `wet` เปียก)
* รับค่าพลังงานเป็น `caloriesPer100g` (kcal/100g) สอดคล้องกับฉลากจริงในท้องตลาด
* มี Method `getCaloriesPerGram()` แปลงเป็น kcal/g อัตโนมัติ ป้องกันความสับสนเรื่องหน่วย

### 3. ก้อนเครื่องคำนวณ (`classes/FeedingCalculator.php`)
* ทำหน้าที่คำนวณตัวเลขทางโภชนาการ (Pure Math / Domain Logic):
  - `calculateRER($weight)`: หาพลังงานขณะพักผ่อน $RER = 70 \times (weight)^{0.75}$
  - `calculateDER(Pet $pet)`: นำ RER คูณกับตัวคูณของสัตว์เลี้ยงแต่ละชนิด (`$pet->getDERMultiplier()`)
  - `calculateDailyPortion(Pet $pet, Food $food)`: คำนวณเป็นปริมาณกรัมอาหารต่อวัน ($DER \div caloriesPerGram$)

### 4. ก้อนจัดตารางเวลา (`classes/FeedingSchedule.php`)
* `calculatePortionPerMeal($dailyGrams)`: เอากรัมต่อวันมาหารจำนวนมื้อ (เช่น วันละ 160g แบ่ง 2 มื้อ = มื้อละ 80g)
* `generateSchedule()` / `getMealSlots($dailyGrams)`: กระจายเวลามื้ออาหารในกรอบ Daytime Window (10-12 ชั่วโมง) เช่น เช้า 08:00 น. และ เย็น 18:00 น. ป้องกันการให้อาหารมื้อดึก

---

## 🧬 สูตรคำนวณมาตรฐานสัตวแพทย์ (Veterinary Standards)

| สูตร | Formula | คำอธิบาย |
|---|---|---|
| **RER** | $70 \times (\text{weight\_kg})^{0.75}$ | Resting Energy Requirement (พลังงานพื้นฐานขณะพักผ่อน) |
| **DER** | $RER \times \text{DER Multiplier}$ | Daily Energy Requirement (พลังงานที่ต้องการต่อวันตามกิจกรรมและสรีระ) |
| **Daily Portion** | $\frac{DER}{\text{Calories Per Gram}}$ | ปริมาณอาหารรวมต่อวัน (กรัม/วัน) |
| **Portion per Meal** | $\frac{\text{Daily Portion}}{\text{Meals Per Day}}$ | ปริมาณอาหารต่อมื้อ (กรัม/มื้อ) |

---

## ⚡ วิธีเปิดใช้งานระบบ

### ทางเลือกที่ 1: รันผ่าน XAMPP
1. วางโฟลเดอร์โปรเจกต์ไว้ที่:
   ```
   C:\xampp\htdocs\pet-nutrition\
   ```
2. เปิดเบราว์เซอร์แล้วเข้าใช้งาน:
   ```
   http://localhost/pet-nutrition/index.html
   ```

### ทางเลือกที่ 2: รันผ่าน PHP Built-in Server (ไม่ต้องติดตั้ง Apache)
1. เปิด Terminal ในโฟลเดอร์โปรเจกต์แล้วรันคำสั่ง:
   ```bash
   php -S localhost:8000
   ```
2. เปิดเบราว์เซอร์ไปที่ `http://localhost:8000/index.html`

---

## 🧪 การทดสอบระบบ (Automated Tests)

สามารถทดสอบการทำงานของทั้ง 4 คลาส และ API ผ่าน Command Line ได้ทันที:

```bash
# ทดสอบ Unit Test ของ 4 คลาสหลัก (สุนัข, แมว, อาหาร, ตารางเวลา)
php tests/test_classes.php

# ทดสอบ API Endpoint calculate
php tests/test_api.php
```

---

## 📡 API Reference

### `GET/POST api.php?action=calculate`
รับข้อมูลสัตว์เลี้ยงและอาหาร เพื่อประมวลผลผ่าน 4 คลาสหลัก และส่งผลลัพธ์กลับเป็น JSON

**Parameters:**
- `species`: `'dog'` หรือ `'cat'`
- `name`: ชื่อสัตว์เลี้ยง
- `weight`: น้ำหนักตัว (กก.)
- `age_stage`: `'puppy_kitten'`, `'adult'`, หรือ `'senior'`
- `activity_level`: `'low'`, `'normal'`, หรือ `'high'`
- `is_neutered`: `true` หรือ `false`
- `breed_size`: (เฉพาะสุนัข) `'small'`, `'medium'`, `'large'`
- `living_habit`: (เฉพาะแมว) `'indoor'`, `'outdoor'`
- `food_type`: `'dry'` หรือ `'wet'`
- `calories_per_100g`: พลังงานอาหาร (kcal/100g)
- `meals_per_day`: จำนวนมื้อ (1, 2, 3, 4)
- `first_meal_time`: เวลาเริ่มมื้อแรก (เช่น `'08:00'`)

**Response ตัวอย่าง:**
```json
{
  "success": true,
  "data": {
    "pet": {
      "name": "บัดดี้",
      "species": "dog",
      "weight_kg": 10.0,
      "multiplier_description": "สุนัขโตเต็มวัย (ทำหมันแล้ว) • กิจกรรมปกติ • พันธุ์กลาง"
    },
    "nutrition": {
      "rer_kcal": 393.64,
      "der_multiplier": 1.6,
      "der_kcal": 629.82,
      "daily_portion_grams": 172.6
    },
    "schedule": {
      "meals_per_day": 2,
      "portion_per_meal_grams": 86.3,
      "meal_slots": [
        { "meal_name": "มื้อเช้า", "time": "08:00", "portion_grams": 86.3 },
        { "meal_name": "มื้อเย็น", "time": "18:00", "portion_grams": 86.3 }
      ]
    }
  }
}
```
