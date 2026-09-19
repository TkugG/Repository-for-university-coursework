<?php
class Food {
    private string $name;
    private string $foodType;
    private float $caloriesPer100g;

    public function __construct(string $name, string $foodType, float $caloriesPer100g) {
        $this->name = $name;
        $this->foodType = $foodType;
        $this->caloriesPer100g = max(10.0, min(1000.0, $caloriesPer100g));
    }

    public function getName(): string { return $this->name; }
    public function getFoodType(): string { return $this->foodType; }
    public function getCaloriesPer100g(): float { return $this->caloriesPer100g; }

    public function getCaloriesPerGram(): float {
        return $this->caloriesPer100g / 100.0;
    }
}