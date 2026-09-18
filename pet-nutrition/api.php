<?php
/**
 * API Handler — Pet Nutrition System
 * รองรับการคำนวณโภชนาการสัตว์เลี้ยงด้วย 4 คลาสหลัก (Clean OOP)
 */

require_once __DIR__ . '/classes/Pet.php';
require_once __DIR__ . '/classes/Food.php';
require_once __DIR__ . '/classes/FeedingCalculator.php';
require_once __DIR__ . '/classes/FeedingSchedule.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action   = $_GET['action'] ?? '';
$rawInput = file_get_contents('php://input');
$body     = json_decode($rawInput, true) ?? [];

// รวม input จาก JSON body หรือ query parameters เข้าด้วยกัน
$input = array_merge($_GET, $_POST, $body);

function respond(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function fail(string $message, int $code = 400): void {
    respond(['success' => false, 'error' => $message], $code);
}

try {
    // ═════════════════════════════════════════════════════════
    // 🧮 CORE OOP ENDPOINT: คำนวณสารอาหารและตารางเวลา (4 คลาสหลัก)
    // ═════════════════════════════════════════════════════════
    if ($action === 'calculate') {
        $species       = strtolower($input['species'] ?? 'dog');
        $name          = trim($input['name'] ?? ($species === 'dog' ? 'น้องหมา' : 'น้องแมว'));
        $gender        = $input['gender'] ?? 'male';
        $weight        = (float)($input['weight'] ?? 0);
        $ageStage      = $input['age_stage'] ?? 'adult';
        $activityLevel = $input['activity_level'] ?? 'normal';
        $isNeutered    = filter_var($input['is_neutered'] ?? true, FILTER_VALIDATE_BOOLEAN);

        if ($weight <= 0) {
            fail('กรุณาระบุน้ำหนักที่มากกว่า 0 kg');
        }

        // 1. ก้อนสัตว์เลี้ยง (Class: Pet -> Dog, Cat)
        if ($species === 'cat') {
            $livingHabit = $input['living_habit'] ?? 'indoor';
            $pet = new Cat($name, $gender, $weight, $ageStage, $activityLevel, $isNeutered, $livingHabit);
        } else {
            $breedSize = $input['breed_size'] ?? 'medium';
            $pet = new Dog($name, $gender, $weight, $ageStage, $activityLevel, $isNeutered, $breedSize);
        }

        // 2. ก้อนอาหาร (Class: Food)
        $foodType        = $input['food_type'] ?? 'dry';
        $foodName        = trim($input['food_name'] ?? ($foodType === 'wet' ? 'อาหารเปียก' : 'อาหารเม็ด'));
        $defaultCalories = ($foodType === 'wet') ? 85.0 : 365.0;
        $caloriesPer100g = (float)($input['calories_per_100g'] ?? $defaultCalories);

        if ($caloriesPer100g <= 0) {
            fail('กรุณาระบุค่าพลังงานอาหารที่มากกว่า 0 kcal/100g');
        }

        $food = new Food($foodName, $foodType, $caloriesPer100g);

        // 3. ก้อนเครื่องคำนวณ (Class: FeedingCalculator)
        $nutrition = FeedingCalculator::getNutritionSummary($pet, $food);

        // 4. ก้อนจัดตารางเวลา (Class: FeedingSchedule)
        $mealsPerDay   = max(1, (int)($input['meals_per_day'] ?? 2));
        $firstMealTime = $input['first_meal_time'] ?? '08:00';
        $schedule      = new FeedingSchedule($mealsPerDay, $firstMealTime);

        $portionPerMeal = $schedule->calculatePortionPerMeal($nutrition['daily_portion_grams']);
        $mealSlots      = $schedule->getDetailedMealSlots($nutrition['daily_portion_grams']);

        // ส่งผลลัพธ์ครอบคลุมทั้ง 4 ก้อน
        respond([
            'success' => true,
            'data'    => [
                'pet' => [
                    'name'                   => $pet->name,
                    'species'                => $species,
                    'species_label'          => $pet->getSpeciesLabel(),
                    'gender'                 => $pet->gender,
                    'weight_kg'              => $pet->weight,
                    'age_stage'              => $pet->ageStage,
                    'activity_level'         => $pet->activityLevel,
                    'is_neutered'            => $pet->isNeutered,
                    'breed_size'             => ($pet instanceof Dog) ? $pet->breedSize : null,
                    'living_habit'           => ($pet instanceof Cat) ? $pet->livingHabit : null,
                    'multiplier_description' => $pet->getMultiplierDescription(),
                ],
                'food' => [
                    'name'              => $food->name,
                    'food_type'         => $food->foodType,
                    'food_type_label'   => $food->getFoodTypeLabel(),
                    'calories_per_100g' => $food->caloriesPer100g,
                    'calories_per_gram' => $food->getCaloriesPerGram(),
                ],
                'nutrition' => [
                    'rer_kcal'            => $nutrition['rer_kcal'],
                    'der_multiplier'      => $nutrition['der_multiplier'],
                    'der_kcal'            => $nutrition['der_kcal'],
                    'daily_portion_grams' => $nutrition['daily_portion_grams'],
                ],
                'schedule' => [
                    'meals_per_day'          => $schedule->mealsPerDay,
                    'first_meal_time'        => $schedule->firstMealTime,
                    'portion_per_meal_grams' => $portionPerMeal,
                    'reminder_times'         => $schedule->reminderTimes,
                    'meal_slots'             => $mealSlots,
                ]
            ]
        ]);
    }

    // ═════════════════════════════════════════════════════════
    // 📦 LEGACY DATABASE ENDPOINTS (ทำงานเมื่อมีการเรียกใช้)
    // ═════════════════════════════════════════════════════════
    if (file_exists(__DIR__ . '/classes/PetProfile.php')) {
        @require_once __DIR__ . '/classes/PetProfile.php';
        @require_once __DIR__ . '/classes/FoodDatabase.php';
        @require_once __DIR__ . '/classes/MealPlanner.php';

        if (in_array($action, ['pets', 'pet', 'pet_nutrition', 'foods', 'food', 'meal_plans', 'meal_plan', 'weekly_summary'])) {
            try {
                $petProfile  = new PetProfile();
                $foodDb      = new FoodDatabase();
                $mealPlanner = new MealPlanner();

                if ($action === 'pets' && $method === 'GET') {
                    respond(['success' => true, 'data' => $petProfile->getAll()]);
                }
                if ($action === 'foods' && $method === 'GET') {
                    respond(['success' => true, 'data' => $foodDb->getAll()]);
                }
            } catch (Throwable $dbEx) {
                fail('Database not connected: ' . $dbEx->getMessage(), 503);
            }
        }
    }

    if ($action === 'ping') {
        respond(['success' => true, 'message' => 'Pet Nutrition API is running!']);
    }

    fail('Unknown action: ' . htmlspecialchars($action), 404);

} catch (Throwable $e) {
    fail('Server error: ' . $e->getMessage(), 500);
}
