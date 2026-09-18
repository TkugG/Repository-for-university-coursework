-- =========================================
-- Pet Nutrition System — Database Schema
-- =========================================

CREATE DATABASE IF NOT EXISTS pet_nutrition
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pet_nutrition;

-- ─────────────────────────────────────────
-- TABLE: pets (ใช้โดย PetProfile)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS pets (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    species         ENUM('dog', 'cat') NOT NULL,
    breed           VARCHAR(100) DEFAULT '',
    age_months      INT NOT NULL DEFAULT 12,
    weight_kg       DECIMAL(5,2) NOT NULL,
    ideal_weight_kg DECIMAL(5,2) NOT NULL,
    life_stage      VARCHAR(30) NOT NULL DEFAULT 'adult',
    activity_level  ENUM('low', 'moderate', 'high', 'working') DEFAULT 'moderate',
    notes           TEXT DEFAULT '',
    created_at      DATETIME NOT NULL,
    INDEX idx_species (species)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- TABLE: foods (ใช้โดย FoodDatabase)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS foods (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(150) NOT NULL,
    brand               VARCHAR(100) DEFAULT '',
    category            ENUM('dry', 'wet', 'treat', 'raw', 'homemade') NOT NULL DEFAULT 'dry',
    food_type           ENUM('commercial', 'raw', 'homemade') NOT NULL DEFAULT 'commercial',
    suitable_for        ENUM('dog', 'cat', 'both') NOT NULL DEFAULT 'both',
    calories_per_100g   DECIMAL(7,2) NOT NULL DEFAULT 0,
    protein_g           DECIMAL(6,2) NOT NULL DEFAULT 0,  -- ต่อ 100g
    fat_g               DECIMAL(6,2) NOT NULL DEFAULT 0,  -- ต่อ 100g
    carbs_g             DECIMAL(6,2) NOT NULL DEFAULT 0,  -- ต่อ 100g
    fiber_g             DECIMAL(6,2) NOT NULL DEFAULT 0,
    water_g             DECIMAL(6,2) NOT NULL DEFAULT 0,
    is_toxic            TINYINT(1) NOT NULL DEFAULT 0,
    toxic_note          TEXT DEFAULT '',
    created_at          DATETIME NOT NULL,
    INDEX idx_suitable_for (suitable_for),
    INDEX idx_category (category),
    FULLTEXT idx_name_brand (name, brand)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- TABLE: meal_plans (ใช้โดย MealPlanner)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS meal_plans (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    pet_id          INT NOT NULL,
    plan_date       DATE NOT NULL,
    target_kcal     DECIMAL(8,2) NOT NULL DEFAULT 0,
    total_kcal      DECIMAL(8,2) NOT NULL DEFAULT 0,
    total_protein_g DECIMAL(7,2) NOT NULL DEFAULT 0,
    total_fat_g     DECIMAL(7,2) NOT NULL DEFAULT 0,
    total_carbs_g   DECIMAL(7,2) NOT NULL DEFAULT 0,
    notes           TEXT DEFAULT '',
    created_at      DATETIME NOT NULL,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    INDEX idx_pet_date (pet_id, plan_date),
    UNIQUE KEY uq_pet_date (pet_id, plan_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- TABLE: meal_items (รายละเอียดแต่ละมื้อ)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS meal_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    plan_id         INT NOT NULL,
    meal_order      TINYINT NOT NULL DEFAULT 1,
    meal_name       VARCHAR(50) NOT NULL DEFAULT 'มื้อเช้า',
    food_id         INT NOT NULL,
    grams           DECIMAL(7,2) NOT NULL,
    calories_kcal   DECIMAL(7,2) NOT NULL DEFAULT 0,
    protein_g       DECIMAL(6,2) NOT NULL DEFAULT 0,
    fat_g           DECIMAL(6,2) NOT NULL DEFAULT 0,
    carbs_g         DECIMAL(6,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (plan_id) REFERENCES meal_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE RESTRICT,
    INDEX idx_plan_id (plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- SEED DATA: อาหารตัวอย่าง
-- ─────────────────────────────────────────
INSERT INTO foods (name, brand, category, food_type, suitable_for, calories_per_100g, protein_g, fat_g, carbs_g, fiber_g, water_g, is_toxic, toxic_note, created_at) VALUES
-- อาหารสุนัข
('Royal Canin Adult Dog', 'Royal Canin', 'dry', 'commercial', 'dog', 352, 26.0, 14.0, 38.0, 4.5, 8.5, 0, '', NOW()),
('Pedigree Adult Chicken', 'Pedigree', 'dry', 'commercial', 'dog', 340, 24.0, 12.0, 40.0, 3.0, 10.0, 0, '', NOW()),
('Cesar Chicken Wet Food', 'Cesar', 'wet', 'commercial', 'dog', 95, 8.0, 4.5, 6.0, 0.5, 78.0, 0, '', NOW()),

-- อาหารแมว
('Royal Canin Indoor Cat', 'Royal Canin', 'dry', 'commercial', 'cat', 336, 28.0, 11.0, 36.0, 7.5, 8.5, 0, '', NOW()),
('Whiskas Tuna Wet', 'Whiskas', 'wet', 'commercial', 'cat', 75, 9.5, 3.0, 4.0, 0.3, 82.0, 0, '', NOW()),
('Felix Ocean Fish', 'Felix', 'wet', 'commercial', 'cat', 80, 10.0, 3.5, 3.5, 0.2, 80.0, 0, '', NOW()),

-- อาหารที่ใช้ร่วมกันได้
('Chicken Breast (cooked)', '', 'homemade', 'homemade', 'both', 165, 31.0, 3.6, 0.0, 0.0, 65.0, 0, '', NOW()),
('Sweet Potato (boiled)', '', 'homemade', 'homemade', 'both', 86, 1.6, 0.1, 20.0, 3.0, 76.0, 0, '', NOW()),
('Plain Rice (cooked)', '', 'homemade', 'homemade', 'both', 130, 2.7, 0.3, 28.0, 0.4, 69.0, 0, '', NOW()),

-- อาหารที่เป็นพิษ
('Chocolate', '', 'treat', 'homemade', 'both', 546, 4.9, 31.0, 60.0, 7.0, 1.3, 1, 'มี Theobromine ที่เป็นพิษต่อสุนัขและแมวอย่างมาก ห้ามให้เด็ดขาด!', NOW()),
('Grapes / Raisins', '', 'treat', 'homemade', 'dog', 67, 0.7, 0.4, 17.0, 0.9, 81.0, 1, 'ทำให้ไตวายในสุนัข ห้ามให้!', NOW()),
('Onion / Garlic', '', 'homemade', 'homemade', 'both', 40, 1.1, 0.1, 9.0, 1.7, 89.0, 1, 'ทำลายเม็ดเลือดแดง (Heinz body anemia) ในสุนัขและแมว', NOW());

-- ─────────────────────────────────────────
-- SEED DATA: สัตว์เลี้ยงตัวอย่าง
-- ─────────────────────────────────────────
INSERT INTO pets (name, species, breed, age_months, weight_kg, ideal_weight_kg, life_stage, activity_level, notes, created_at) VALUES
('บัดดี้', 'dog', 'Golden Retriever', 36, 28.5, 30.0, 'adult', 'moderate', 'ชอบวิ่งเล่นตอนเย็น', NOW()),
('มิ้ว', 'cat', 'Scottish Fold', 24, 4.2, 4.0, 'adult', 'low', 'ทำหมันแล้ว ชอบนอนในบ้าน', NOW());
