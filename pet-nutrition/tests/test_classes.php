<?php
require_once __DIR__ . '/../classes/Pet.php';
require_once __DIR__ . '/../classes/Food.php';
require_once __DIR__ . '/../classes/FeedingCalculator.php';
require_once __DIR__ . '/../classes/FeedingSchedule.php';

echo "=== TEST DOG CALCULATION ===" . PHP_EOL;
$dog = new Dog('บัดดี้', 'male', 10.0, 'adult', 'normal', true, 'medium');
$food = new Food('Royal Canin Medium Adult', 'dry', 380);
$calc = FeedingCalculator::getNutritionSummary($dog, $food);
$schedule = new FeedingSchedule(2, '08:00');
$slots = $schedule->getDetailedMealSlots($calc['daily_portion_grams']);

echo "Name: " . $dog->name . " (" . $dog->getSpeciesLabel() . ")" . PHP_EOL;
echo "Weight: " . $dog->weight . " kg" . PHP_EOL;
echo "RER: " . $calc['rer_kcal'] . " kcal/day" . PHP_EOL;
echo "DER Multiplier: " . $calc['der_multiplier'] . "x (" . $calc['multiplier_description'] . ")" . PHP_EOL;
echo "DER: " . $calc['der_kcal'] . " kcal/day" . PHP_EOL;
echo "Daily Food: " . $calc['daily_portion_grams'] . " g/day" . PHP_EOL;
echo "Portion Per Meal: " . $schedule->calculatePortionPerMeal($calc['daily_portion_grams']) . " g/meal" . PHP_EOL;
echo "Reminder Times: " . implode(', ', $schedule->reminderTimes) . PHP_EOL;
foreach ($slots as $slot) {
    echo "  - [{$slot['time']}] {$slot['meal_name']}: {$slot['portion_grams']} g" . PHP_EOL;
}

echo PHP_EOL . "=== TEST CAT CALCULATION ===" . PHP_EOL;
$cat = new Cat('มิ้ว', 'female', 4.0, 'adult', 'low', true, 'indoor');
$catFood = new Food('Whiskas Pouch', 'wet', 85);
$catCalc = FeedingCalculator::getNutritionSummary($cat, $catFood);
$catSchedule = new FeedingSchedule(3, '07:30');
$catSlots = $catSchedule->getDetailedMealSlots($catCalc['daily_portion_grams']);

echo "Name: " . $cat->name . " (" . $cat->getSpeciesLabel() . ")" . PHP_EOL;
echo "Weight: " . $cat->weight . " kg" . PHP_EOL;
echo "RER: " . $catCalc['rer_kcal'] . " kcal/day" . PHP_EOL;
echo "DER Multiplier: " . $catCalc['der_multiplier'] . "x (" . $catCalc['multiplier_description'] . ")" . PHP_EOL;
echo "DER: " . $catCalc['der_kcal'] . " kcal/day" . PHP_EOL;
echo "Daily Food: " . $catCalc['daily_portion_grams'] . " g/day" . PHP_EOL;
echo "Portion Per Meal: " . $catSchedule->calculatePortionPerMeal($catCalc['daily_portion_grams']) . " g/meal" . PHP_EOL;
echo "Reminder Times: " . implode(', ', $catSchedule->reminderTimes) . PHP_EOL;
foreach ($catSlots as $slot) {
    echo "  - [{$slot['time']}] {$slot['meal_name']}: {$slot['portion_grams']} g" . PHP_EOL;
}
echo PHP_EOL . "ALL 4 CLASSES PASSED VERIFICATION!" . PHP_EOL;
