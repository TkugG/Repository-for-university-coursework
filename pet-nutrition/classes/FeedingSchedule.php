<?php
class FeedingSchedule {
    private int $mealsPerDay;
    private string $firstMealTime;

    public function __construct(int $mealsPerDay, string $firstMealTime) {
        $this->mealsPerDay = max(1, min(4, $mealsPerDay));
        $this->firstMealTime = $firstMealTime;
    }

    public function getMealSlots(float $dailyGrams): array {
        $slots = [];
        $portionPerMeal = $dailyGrams / $this->mealsPerDay;
        
        $parts = explode(':', $this->firstMealTime);
        $hours = intval($parts[0] ?? 8);
        $minutes = intval($parts[1] ?? 0);

        $intervalHours = floor(12 / max(1, $this->mealsPerDay - 1));
        $mealNames = ['มื้อเช้า', 'มื้อเที่ยง', 'มื้อเย็น', 'มื้อดึก'];

        for ($i = 0; $i < $this->mealsPerDay; $i++) {
            $currentH = ($hours + ($i * $intervalHours)) % 24;
            $timeFormatted = sprintf('%02d:%02d น.', $currentH, $minutes);
            $label = $mealNames[$i] ?? ("มื้อที่ " . ($i + 1));

            $slots[] = [
                'time' => $timeFormatted,
                'label' => $label,
                'grams' => round($portionPerMeal, 1)
            ];
        }

        return $slots;
    }
}