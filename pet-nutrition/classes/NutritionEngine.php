<?php
/**
 * NutritionEngine — SHARED CLASS (ใช้งานร่วมกันทุก Class)
 * 
 * รับผิดชอบ: คำนวณความต้องการสารอาหาร calories/nutrients
 * ใช้สูตร RER (Resting Energy Requirement) มาตรฐาน veterinary
 */

require_once __DIR__ . '/../config/db.php';

class NutritionEngine {

    /**
     * คำนวณ RER (Resting Energy Requirement)
     * สูตร: 70 × (weight_kg ^ 0.75)
     */
    public static function calculateRER(float $weightKg): float {
        return 70 * pow($weightKg, 0.75);
    }

    /**
     * คำนวณ DER (Daily Energy Requirement) จาก life stage factor
     * Factor แตกต่างกันตาม species + activity level
     */
    public static function calculateDER(float $weightKg, string $species, string $lifeStage, string $activityLevel): array {
        $rer = self::calculateRER($weightKg);

        // Life stage factors (AAFCO / NRC guidelines)
        $factors = self::getLifeStageFactor($species, $lifeStage, $activityLevel);

        $der = $rer * $factors['factor'];

        return [
            'rer_kcal'       => round($rer, 2),
            'der_kcal'       => round($der, 2),
            'factor_used'    => $factors['factor'],
            'factor_label'   => $factors['label'],
            'protein_g'      => round($der * $factors['protein_ratio'] / 4, 2),   // 4 kcal/g
            'fat_g'          => round($der * $factors['fat_ratio'] / 9, 2),        // 9 kcal/g
            'carbs_g'        => round($der * $factors['carbs_ratio'] / 4, 2),
            'water_ml'       => round($weightKg * $factors['water_ml_per_kg'], 2),
        ];
    }

    /**
     * ดึง Life Stage Factor ตาม species, life stage, activity
     */
    private static function getLifeStageFactor(string $species, string $lifeStage, string $activityLevel): array {
        $baseFactors = [
            'dog' => [
                'puppy'        => ['factor' => 3.0, 'label' => 'ลูกสุนัข (<4 เดือน)'],
                'puppy_adult'  => ['factor' => 2.5, 'label' => 'ลูกสุนัข (4-12 เดือน)'],
                'adult'        => ['factor' => 1.6, 'label' => 'สุนัขผู้ใหญ่ (ทำหมัน)'],
                'adult_intact' => ['factor' => 1.8, 'label' => 'สุนัขผู้ใหญ่ (ไม่ทำหมัน)'],
                'senior'       => ['factor' => 1.4, 'label' => 'สุนัขสูงวัย'],
                'pregnant'     => ['factor' => 3.0, 'label' => 'สุนัขตั้งท้อง'],
                'lactating'    => ['factor' => 4.0, 'label' => 'สุนัขให้นม'],
            ],
            'cat' => [
                'kitten'       => ['factor' => 2.5, 'label' => 'ลูกแมว'],
                'adult'        => ['factor' => 1.2, 'label' => 'แมวผู้ใหญ่ (ทำหมัน)'],
                'adult_intact' => ['factor' => 1.4, 'label' => 'แมวผู้ใหญ่ (ไม่ทำหมัน)'],
                'senior'       => ['factor' => 1.1, 'label' => 'แมวสูงวัย'],
                'pregnant'     => ['factor' => 2.0, 'label' => 'แมวตั้งท้อง'],
                'lactating'    => ['factor' => 3.0, 'label' => 'แมวให้นม'],
            ],
        ];

        // Activity multipliers เฉพาะสุนัข
        $activityMultiplier = [
            'low'      => 0.8,
            'moderate' => 1.0,
            'high'     => 1.2,
            'working'  => 1.5,
        ];

        $factor = $baseFactors[$species][$lifeStage] ?? $baseFactors[$species]['adult'];
        $actMult = $activityMultiplier[$activityLevel] ?? 1.0;

        // ใช้ activity multiplier กับ adult dogs เท่านั้น
        if ($species === 'dog' && in_array($lifeStage, ['adult', 'adult_intact', 'senior'])) {
            $factor['factor'] *= $actMult;
        }

        // Macronutrient ratios ตาม species
        if ($species === 'cat') {
            // แมวต้องการโปรตีนสูง (obligate carnivore)
            $factor['protein_ratio'] = 0.40;
            $factor['fat_ratio']     = 0.35;
            $factor['carbs_ratio']   = 0.10;
            $factor['water_ml_per_kg'] = 60;
        } else {
            $factor['protein_ratio'] = 0.25;
            $factor['fat_ratio']     = 0.30;
            $factor['carbs_ratio']   = 0.35;
            $factor['water_ml_per_kg'] = 50;
        }

        return $factor;
    }

    /**
     * วิเคราะห์ความเหมาะสมของน้ำหนัก (BCS - Body Condition Score)
     */
    public static function analyzeBCS(float $currentWeight, float $idealWeight): array {
        $ratio = $currentWeight / $idealWeight;

        if ($ratio < 0.85) {
            return ['bcs' => 1, 'label' => 'ผอมมาก', 'color' => 'danger', 'advice' => 'ควรเพิ่มปริมาณอาหาร 20% และพบสัตวแพทย์'];
        } elseif ($ratio < 0.95) {
            return ['bcs' => 2, 'label' => 'ผอม', 'color' => 'warning', 'advice' => 'ควรเพิ่มปริมาณอาหาร 10%'];
        } elseif ($ratio <= 1.05) {
            return ['bcs' => 3, 'label' => 'น้ำหนักดี ✓', 'color' => 'success', 'advice' => 'น้ำหนักอยู่ในเกณฑ์ดี ดูแลต่อไปเลย!'];
        } elseif ($ratio <= 1.15) {
            return ['bcs' => 4, 'label' => 'น้ำหนักเกิน', 'color' => 'warning', 'advice' => 'ลดปริมาณอาหาร 10% และเพิ่มการออกกำลังกาย'];
        } else {
            return ['bcs' => 5, 'label' => 'อ้วนมาก', 'color' => 'danger', 'advice' => 'ลดปริมาณอาหาร 20% พบสัตยแพทย์เพื่อวางแผนลดน้ำหนัก'];
        }
    }

    /**
     * คำนวณ nutrient ของ food item ตามปริมาณที่กิน
     */
    public static function calculateFoodNutrients(array $food, float $amountGrams): array {
        $ratio = $amountGrams / 100;
        return [
            'calories_kcal' => round($food['calories_per_100g'] * $ratio, 2),
            'protein_g'     => round($food['protein_g'] * $ratio, 2),
            'fat_g'         => round($food['fat_g'] * $ratio, 2),
            'carbs_g'       => round($food['carbs_g'] * $ratio, 2),
            'fiber_g'       => round(($food['fiber_g'] ?? 0) * $ratio, 2),
            'water_g'       => round(($food['water_g'] ?? 0) * $ratio, 2),
        ];
    }

    /**
     * สรุป nutrient รวม จากหลาย food items
     */
    public static function sumNutrients(array $nutrientsList): array {
        $total = ['calories_kcal' => 0, 'protein_g' => 0, 'fat_g' => 0, 'carbs_g' => 0, 'fiber_g' => 0, 'water_g' => 0];
        foreach ($nutrientsList as $n) {
            foreach ($total as $key => &$val) {
                $val += ($n[$key] ?? 0);
            }
        }
        return array_map(fn($v) => round($v, 2), $total);
    }
}
