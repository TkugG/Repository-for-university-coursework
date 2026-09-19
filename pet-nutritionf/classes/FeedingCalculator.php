<?php
class FeedingCalculator {
    public function calculateRER(float $weight): float {
        return 70.0 * pow($weight, 0.75);
    }

    public function calculateDER(Pet $pet): float {
        $rer = $this->calculateRER($pet->getWeight());
        return $rer * $pet->getDERMultiplier();
    }

    public function calculateDailyPortion(Pet $pet, Food $food): float {
        $der = $this->calculateDER($pet);
        $calPerGram = $food->getCaloriesPerGram();
        return $calPerGram > 0 ? ($der / $calPerGram) : 0.0;
    }
}