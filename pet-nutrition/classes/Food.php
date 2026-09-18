<?php
/**
 * 🥩 ก้อนอาหาร (Class: Food)
 * 
 * เก็บข้อมูลอาหารที่เอามาใช้คำนวณ
 */

class Food {
    public function __construct(
        public string $name,
        public string $foodType,           // 'dry' (อาหารเม็ด), 'wet' (อาหารเปียก)
        public float $caloriesPer100g      // พลังงาน (kcal ต่อ 100 กรัม) — ตามฉลากจริงในท้องตลาด
    ) {
        if ($this->caloriesPer100g <= 0) {
            throw new InvalidArgumentException("พลังงานอาหารต้องมากกว่า 0 kcal/100g");
        }
    }

    /**
     * แปลงเป็นพลังงานต่อกรัม (kcal/g) สำหรับนำไปหารหาปริมาณอาหาร
     */
    public function getCaloriesPerGram(): float {
        return $this->caloriesPer100g / 100.0;
    }

    /**
     * ดึงชื่อประเภทอาหารภาษาไทย
     */
    public function getFoodTypeLabel(): string {
        return match($this->foodType) {
            'dry'   => 'อาหารเม็ด (Dry Food)',
            'wet'   => 'อาหารเปียก (Wet Food)',
            default => 'อาหารทั่วไป'
        };
    }
}
