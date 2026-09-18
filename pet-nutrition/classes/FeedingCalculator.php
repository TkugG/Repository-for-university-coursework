<?php
/**
 * 🧮 ก้อนเครื่องคำนวณ (Class: FeedingCalculator)
 * 
 * ทำหน้าที่คำนวณตัวเลขล้วนๆ ไม่ต้องเก็บข้อมูลส่วนตัวของสัตว์
 * ใช้งานร่วมกับ Pet และ Food
 */

require_once __DIR__ . '/Pet.php';
require_once __DIR__ . '/Food.php';

class FeedingCalculator {

    /**
     * หาพลังงานพื้นฐานตอนพักผ่อน (Resting Energy Requirement)
     * สูตร: RER = 70 × (weight)^0.75
     */
    public static function calculateRER(float $weight): float {
        if ($weight <= 0) {
            throw new InvalidArgumentException("น้ำหนักต้องมากกว่า 0 kg");
        }
        return round(70 * pow($weight, 0.75), 2);
    }

    /**
     * หาพลังงานที่ต้องการจริงต่อวัน (Daily Energy Requirement)
     * นำ RER มาคูณกับตัวคูณเฉพาะของสัตว์แต่ละตัว (ผ่าน Polymorphism)
     */
    public static function calculateDER(Pet $pet): float {
        $rer = self::calculateRER($pet->weight);
        $multiplier = $pet->getDERMultiplier();
        return round($rer * $multiplier, 2);
    }

    /**
     * แปลงพลังงานต่อวันออกมาเป็น "ปริมาณอาหารกี่กรัม/วัน"
     * สูตร: DailyPortion (g) = DER (kcal/day) ÷ caloriesPerGram (kcal/g)
     */
    public static function calculateDailyPortion(Pet $pet, Food $food): float {
        $der = self::calculateDER($pet);
        $calPerGram = $food->getCaloriesPerGram();

        if ($calPerGram <= 0) {
            throw new InvalidArgumentException("ค่าแคลอรีอาหารต้องมากกว่า 0");
        }

        return round($der / $calPerGram, 1);
    }

    /**
     * คำนวณสรุปข้อมูลโภชนาการทั้งหมดในครั้งเดียว
     */
    public static function getNutritionSummary(Pet $pet, Food $food): array {
        $rer = self::calculateRER($pet->weight);
        $derMultiplier = $pet->getDERMultiplier();
        $der = round($rer * $derMultiplier, 2);
        $dailyGrams = self::calculateDailyPortion($pet, $food);

        return [
            'rer_kcal'               => $rer,
            'der_multiplier'         => $derMultiplier,
            'multiplier_description' => $pet->getMultiplierDescription(),
            'der_kcal'               => $der,
            'daily_portion_grams'    => $dailyGrams,
        ];
    }
}
