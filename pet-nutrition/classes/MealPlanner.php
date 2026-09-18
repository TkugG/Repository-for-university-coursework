<?php
/**
 * MealPlanner — CLASS B
 * 
 * รับผิดชอบ: วางแผนมื้ออาหาร + สร้าง daily meal schedule
 * ใช้งานร่วมกับ NutritionEngine (shared), PetProfile, FoodDatabase
 */

require_once __DIR__ . '/NutritionEngine.php';
require_once __DIR__ . '/PetProfile.php';
require_once __DIR__ . '/FoodDatabase.php';

class MealPlanner {
    private PDO $db;
    private PetProfile $petProfile;
    private FoodDatabase $foodDb;

    public function __construct() {
        $this->db         = Database::getConnection();
        $this->petProfile = new PetProfile();
        $this->foodDb     = new FoodDatabase();
    }

    // ─────────────────────────────────────────
    // CREATE MEAL PLAN
    // ─────────────────────────────────────────

    /**
     * สร้างแผนมื้ออาหาร 1 วัน
     */
    public function createMealPlan(int $petId, string $date, array $meals): int {
        // ดึง nutrition needs ของสัตว์
        $needs = $this->petProfile->getNutritionNeeds($petId);
        if (!$needs) throw new Exception("Pet ID {$petId} not found");

        $targetKcal = $needs['nutrition']['der_kcal'];

        // คำนวณ total nutrients จากทุก meal item
        $allNutrients = [];
        foreach ($meals as $meal) {
            foreach ($meal['items'] as $item) {
                $food = $this->foodDb->getById($item['food_id']);
                if ($food) {
                    $allNutrients[] = NutritionEngine::calculateFoodNutrients($food, $item['grams']);
                }
            }
        }

        $totalNutrients = NutritionEngine::sumNutrients($allNutrients);

        // บันทึก meal plan header
        $stmt = $this->db->prepare("
            INSERT INTO meal_plans (pet_id, plan_date, target_kcal, total_kcal, total_protein_g, total_fat_g, total_carbs_g, notes, created_at)
            VALUES (:pet_id, :plan_date, :target_kcal, :total_kcal, :total_protein_g, :total_fat_g, :total_carbs_g, :notes, NOW())
        ");
        $stmt->execute([
            ':pet_id'         => $petId,
            ':plan_date'      => $date,
            ':target_kcal'    => $targetKcal,
            ':total_kcal'     => $totalNutrients['calories_kcal'],
            ':total_protein_g'=> $totalNutrients['protein_g'],
            ':total_fat_g'    => $totalNutrients['fat_g'],
            ':total_carbs_g'  => $totalNutrients['carbs_g'],
            ':notes'          => '',
        ]);

        $planId = (int)$this->db->lastInsertId();

        // บันทึก meal items
        foreach ($meals as $mealIndex => $meal) {
            $mealOrder = $mealIndex + 1;
            foreach ($meal['items'] as $item) {
                $this->addMealItem($planId, $mealOrder, $meal['meal_name'], $item['food_id'], $item['grams']);
            }
        }

        return $planId;
    }

    /**
     * เพิ่ม item เข้า meal plan
     */
    public function addMealItem(int $planId, int $mealOrder, string $mealName, int $foodId, float $grams): int {
        $food = $this->foodDb->getById($foodId);
        if (!$food) throw new Exception("Food ID {$foodId} not found");

        $nutrients = NutritionEngine::calculateFoodNutrients($food, $grams);

        $stmt = $this->db->prepare("
            INSERT INTO meal_items (plan_id, meal_order, meal_name, food_id, grams, calories_kcal, protein_g, fat_g, carbs_g)
            VALUES (:plan_id, :meal_order, :meal_name, :food_id, :grams, :calories_kcal, :protein_g, :fat_g, :carbs_g)
        ");
        $stmt->execute([
            ':plan_id'       => $planId,
            ':meal_order'    => $mealOrder,
            ':meal_name'     => $mealName,
            ':food_id'       => $foodId,
            ':grams'         => $grams,
            ':calories_kcal' => $nutrients['calories_kcal'],
            ':protein_g'     => $nutrients['protein_g'],
            ':fat_g'         => $nutrients['fat_g'],
            ':carbs_g'       => $nutrients['carbs_g'],
        ]);

        return (int)$this->db->lastInsertId();
    }

    // ─────────────────────────────────────────
    // READ MEAL PLANS
    // ─────────────────────────────────────────

    public function getMealPlansByPet(int $petId, int $limit = 30): array {
        $stmt = $this->db->prepare("
            SELECT mp.*, p.name as pet_name, p.species
            FROM meal_plans mp
            JOIN pets p ON mp.pet_id = p.id
            WHERE mp.pet_id = :pet_id
            ORDER BY mp.plan_date DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':pet_id', $petId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMealPlanDetail(int $planId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM meal_plans WHERE id = :id");
        $stmt->execute([':id' => $planId]);
        $plan = $stmt->fetch();
        if (!$plan) return null;

        // ดึง items
        $stmt = $this->db->prepare("
            SELECT mi.*, f.name as food_name, f.brand, f.category, f.is_toxic
            FROM meal_items mi
            JOIN foods f ON mi.food_id = f.id
            WHERE mi.plan_id = :plan_id
            ORDER BY mi.meal_order ASC, mi.id ASC
        ");
        $stmt->execute([':plan_id' => $planId]);
        $items = $stmt->fetchAll();

        // จัดกลุ่ม items ตาม meal
        $plan['meals'] = [];
        foreach ($items as $item) {
            $key = $item['meal_order'] . '_' . $item['meal_name'];
            if (!isset($plan['meals'][$key])) {
                $plan['meals'][$key] = [
                    'meal_order' => $item['meal_order'],
                    'meal_name'  => $item['meal_name'],
                    'items'      => [],
                ];
            }
            $plan['meals'][$key]['items'][] = $item;
        }
        $plan['meals'] = array_values($plan['meals']);

        return $plan;
    }

    public function getAllMealPlans(): array {
        $stmt = $this->db->query("
            SELECT mp.*, p.name as pet_name, p.species
            FROM meal_plans mp
            JOIN pets p ON mp.pet_id = p.id
            ORDER BY mp.plan_date DESC
            LIMIT 100
        ");
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────

    public function deleteMealPlan(int $planId): bool {
        $this->db->prepare("DELETE FROM meal_items WHERE plan_id = :id")->execute([':id' => $planId]);
        $stmt = $this->db->prepare("DELETE FROM meal_plans WHERE id = :id");
        return $stmt->execute([':id' => $planId]);
    }

    // ─────────────────────────────────────────
    // SMART SUGGEST — ใช้ NutritionEngine
    // ─────────────────────────────────────────

    /**
     * แนะนำปริมาณอาหารต่อมื้อ จาก target kcal รายวัน
     */
    public function suggestMealAmounts(int $petId, array $foodIds, int $mealsPerDay = 2): ?array {
        $needs = $this->petProfile->getNutritionNeeds($petId);
        if (!$needs) return null;

        $targetKcalPerMeal = $needs['nutrition']['der_kcal'] / $mealsPerDay;
        $suggestions = [];

        foreach ($foodIds as $foodId) {
            $food = $this->foodDb->getById($foodId);
            if (!$food) continue;

            // คำนวณกรัมที่ได้ target kcal ต่อมื้อ
            $gramsNeeded = ($targetKcalPerMeal / $food['calories_per_100g']) * 100;

            $suggestions[] = [
                'food'              => $food,
                'target_kcal_meal'  => round($targetKcalPerMeal, 2),
                'suggested_grams'   => round($gramsNeeded, 1),
                'nutrients_per_meal'=> NutritionEngine::calculateFoodNutrients($food, $gramsNeeded),
            ];
        }

        return [
            'pet'             => $needs['pet'],
            'daily_kcal'      => $needs['nutrition']['der_kcal'],
            'meals_per_day'   => $mealsPerDay,
            'kcal_per_meal'   => round($targetKcalPerMeal, 2),
            'suggestions'     => $suggestions,
        ];
    }

    /**
     * สรุปรายงาน nutrition ของสัปดาห์
     */
    public function getWeeklySummary(int $petId): array {
        $stmt = $this->db->prepare("
            SELECT 
                plan_date,
                target_kcal,
                total_kcal,
                total_protein_g,
                total_fat_g,
                total_carbs_g,
                ROUND((total_kcal / target_kcal) * 100, 1) as coverage_pct
            FROM meal_plans
            WHERE pet_id = :pet_id
            AND plan_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ORDER BY plan_date ASC
        ");
        $stmt->execute([':pet_id' => $petId]);
        return $stmt->fetchAll();
    }
}
