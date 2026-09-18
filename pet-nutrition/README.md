# 🐾 Pet Nutrition System

ระบบคำนวณสารอาหารและมื้ออาหารสัตว์เลี้ยง — PHP OOP + MySQL

---

## 📁 โครงสร้างโปรเจกต์

```
pet-nutrition/
├── config/
│   └── db.php              ← Database connection (PDO Singleton)
├── classes/
│   ├── NutritionEngine.php  🟢 SHARED — คำนวณ RER/DER/BCS
│   ├── PetProfile.php       🔵 CLASS A — CRUD สัตว์เลี้ยง
│   ├── MealPlanner.php      🟡 CLASS B — วางแผนมื้ออาหาร
│   └── FoodDatabase.php     🔴 CLASS C — ฐานข้อมูลอาหาร
├── database/
│   └── schema.sql           ← SQL สำหรับสร้าง database
├── api.php                  ← REST API endpoint
└── index.html               ← Single Page Application
```

---

## ⚡ วิธี Setup (5 นาที)

### 1. วาง folder ใน XAMPP/WAMP

```
C:\xampp\htdocs\pet-nutrition\
```

### 2. Import Database

1. เปิด **phpMyAdmin** → `http://localhost/phpmyadmin`
2. คลิก **"New"** → สร้าง database ชื่อ `pet_nutrition`
3. คลิก **Import** → เลือกไฟล์ `database/schema.sql`
4. กด **Go**

> ระบบจะสร้าง tables + seed data อาหาร + สัตว์เลี้ยงตัวอย่างให้อัตโนมัติ

### 3. แก้ไข config (ถ้าจำเป็น)

```php
// config/db.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // ← ใส่ password ถ้ามี
define('DB_NAME', 'pet_nutrition');
```

### 4. เปิดระบบ

```
http://localhost/pet-nutrition/index.html
```

---

## 🏗️ Architecture — 3 Class + 1 Shared

```
                    ┌─────────────────────┐
                    │   NutritionEngine   │  🟢 SHARED CLASS
                    │  (คำนวณ RER/DER/BCS) │
                    └──────────┬──────────┘
                               │ ใช้งานโดยทุก class
              ┌────────────────┼────────────────┐
              ▼                ▼                ▼
    ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
    │  PetProfile  │  │  MealPlanner │  │FoodDatabase  │
    │  🔵 CLASS A  │  │  🟡 CLASS B  │  │  🔴 CLASS C  │
    └──────────────┘  └──────────────┘  └──────────────┘
```

### NutritionEngine (Shared)
- `calculateRER(weight)` → Resting Energy Requirement
- `calculateDER(weight, species, lifeStage, activity)` → Daily Energy Requirement  
- `analyzeBCS(current, ideal)` → Body Condition Score (1-5)
- `calculateFoodNutrients(food, grams)` → คำนวณ nutrient ต่อปริมาณ
- `sumNutrients(list)` → รวม nutrient จากหลาย food items

### PetProfile (Class A)
- CRUD สัตว์เลี้ยง (สุนัข + แมว)
- `getNutritionNeeds(petId)` → เรียก NutritionEngine
- รองรับ life stage ตาม AAFCO/NRC guidelines

### MealPlanner (Class B)
- สร้าง/ดู/ลบ meal plan รายวัน
- `suggestMealAmounts(petId, foodIds, mealsPerDay)` → แนะนำปริมาณอาหาร
- `getWeeklySummary(petId)` → รายงานสัปดาห์

### FoodDatabase (Class C)
- CRUD อาหาร + search full-text
- `calcNutrientsForAmount(foodId, grams)` → เรียก NutritionEngine
- `getToxicFoods(species)` → อาหารที่เป็นพิษ

---

## 🧬 สูตรคำนวณ (Veterinary Standard)

| สูตร | Formula |
|------|---------|
| **RER** (พลังงานพักผ่อน) | `70 × (weight_kg ^ 0.75)` |
| **DER** (พลังงานต้องการต่อวัน) | `RER × life_stage_factor × activity_multiplier` |

### Life Stage Factors

| Species | Life Stage | Factor |
|---------|-----------|--------|
| สุนัข | ลูกสุนัข < 4 เดือน | 3.0x |
| สุนัข | ผู้ใหญ่ (ทำหมัน) | 1.6x |
| สุนัข | สูงวัย | 1.4x |
| แมว | ลูกแมว | 2.5x |
| แมว | ผู้ใหญ่ (ทำหมัน) | 1.2x |
| แมว | ตั้งท้อง | 2.0x |

---

## 🛡️ Security Notes

- ใช้ **PDO Prepared Statements** ทุก query — ป้องกัน SQL Injection
- Input validation ทั้ง frontend และ backend
- ไม่มีระบบ login (ตามที่กำหนด) → ควร deploy บน local network หรือเพิ่ม `.htpasswd` ก่อน production

---

## 🐾 Seed Data ที่มีให้

**สัตว์เลี้ยงตัวอย่าง:**
- บัดดี้ (Golden Retriever, 28.5 kg)
- มิ้ว (Scottish Fold, 4.2 kg)

**อาหาร 12 รายการ:** Royal Canin, Pedigree, Whiskas, Felix, อาหารทำเอง และอาหารที่เป็นพิษ (Chocolate, Grapes, Onion)
