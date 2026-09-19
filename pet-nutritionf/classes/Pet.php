<?php
abstract class Pet {
    protected string $name;
    protected float $weight;
    protected string $ageStage;
    protected string $activityLevel;
    protected bool $isNeutered;

    public function __construct(string $name, float $weight, string $ageStage, string $activityLevel, bool $isNeutered) {
        $this->name = $name;
        $this->weight = max(0.1, min(200.0, $weight));
        $this->ageStage = $ageStage;
        $this->activityLevel = $activityLevel;
        $this->isNeutered = $isNeutered;
    }

    public function getName(): string { return $this->name; }
    public function getWeight(): float { return $this->weight; }
    public function getAgeStage(): string { return $this->ageStage; }
    public function getActivityLevel(): string { return $this->activityLevel; }
    public function isNeutered(): bool { return $this->isNeutered; }

    abstract public function getDERMultiplier(): float;
}

class Dog extends Pet {
    private string $breedSize;

    public function __construct(string $name, float $weight, string $ageStage, string $activityLevel, bool $isNeutered, string $breedSize = 'medium') {
        parent::__construct($name, $weight, $ageStage, $activityLevel, $isNeutered);
        $this->breedSize = $breedSize;
    }

    public function getBreedSize(): string { return $this->breedSize; }

    public function getDERMultiplier(): float {
        // 1. เช็คช่วงวัยพิเศษ
        if ($this->ageStage === 'pup') return 2.0;
        if ($this->ageStage === 'senior') return 1.1;

        // 2. ค่าเริ่มต้น Adult (1.6)
        $factor = 1.6;

        // 3. ปรับตามระดับกิจกรรม
        if ($this->activityLevel === 'low') {
            $factor -= 0.3; // กิจกรรมน้อย
        } elseif ($this->activityLevel === 'high') {
            $factor += 0.4; // คึกคักมาก
        }

        // 4. ปรับตามการทำหมัน
        if ($this->isNeutered) {
            $factor -= 0.2; // ทำหมันแล้วพลังงานลดลง
        }

        // 5. ปรับตามขนาดสายพันธุ์
        if ($this->breedSize === 'small') {
            $factor += 0.1; // พันธุ์เล็ก BMR สูงขึ้น
        } elseif ($this->breedSize === 'large') {
            $factor -= 0.1; // พันธุ์ใหญ่ BMR ต่อ กก. ต่ำลง
        }

        return max(1.0, $factor);
    }
}

class Cat extends Pet {
    public function getDERMultiplier(): float {
        if ($this->ageStage === 'pup') return 2.5;
        if ($this->ageStage === 'senior') return 1.0;

        $factor = 1.2;

        if ($this->activityLevel === 'low') {
            $factor -= 0.2;
        } elseif ($this->activityLevel === 'high') {
            $factor += 0.3;
        }

        if ($this->isNeutered) {
            $factor -= 0.1;
        }

        return max(0.8, $factor);
    }
}