<?php
/**
 * 🐾 ก้อนสัตว์เลี้ยง (Class: Pet, Dog, Cat)
 * 
 * คลาสแม่ (Base Class): Pet
 * คลาสลูก (Inheritance): Dog, Cat
 */

abstract class Pet {
    public function __construct(
        public string $name,
        public string $gender,          // 'male' | 'female'
        public float $weight,           // kg (สำคัญสุด ใช้คิดแคลอรี)
        public string $ageStage,        // 'puppy_kitten', 'adult', 'senior'
        public string $activityLevel,   // 'low', 'normal', 'high'
        public bool $isNeutered         // ทำหมันแล้วหรือไม่
    ) {
        if ($this->weight <= 0) {
            throw new InvalidArgumentException("น้ำหนักต้องมากกว่า 0 kg");
        }
    }

    /**
     * คำนวณตัวคูณพลังงาน DER ตามลักษณะของสัตว์เลี้ยงแต่ละชนิด (Polymorphism)
     */
    abstract public function getDERMultiplier(): float;

    /**
     * ดึงชื่อประเภทสัตว์เลี้ยงภาษาไทย
     */
    abstract public function getSpeciesLabel(): string;

    /**
     * ดึงคำอธิบายเกณฑ์ของตัวคูณที่ใช้
     */
    abstract public function getMultiplierDescription(): string;
}

/**
 * 🐶 Class: Dog (สืบทอดจาก Pet)
 */
class Dog extends Pet {
    public function __construct(
        string $name,
        string $gender,
        float $weight,
        string $ageStage,
        string $activityLevel,
        bool $isNeutered,
        public string $breedSize = 'medium' // 'small', 'medium', 'large'
    ) {
        parent::__construct($name, $gender, $weight, $ageStage, $activityLevel, $isNeutered);
    }

    public function getSpeciesLabel(): string {
        return 'สุนัข';
    }

    public function getDERMultiplier(): float {
        // อ้างอิงตามเกณฑ์มาตรฐาน AAFCO / NRC สำหรับสุนัข
        if ($this->ageStage === 'puppy_kitten') {
            return 2.5; // ลูกสุนัขช่วงกำลังเจริญเติบโต
        }

        if ($this->ageStage === 'senior') {
            $base = 1.4; // สุนัขสูงวัย
        } else {
            // โตเต็มวัย (Adult)
            $base = $this->isNeutered ? 1.6 : 1.8;
        }

        // ปรับตามระดับกิจกรรม
        if ($this->activityLevel === 'low') {
            $base -= 0.2; // กิจกรรมน้อย / ขี้เกียจ
        } elseif ($this->activityLevel === 'high') {
            $base += 0.3; // วิ่งเล่นเยอะ / คึกคักมาก
        }

        // ปรับตามขนาดสายพันธุ์เล็กน้อย (พันธุ์เล็กเผาผลาญเร็วกว่า)
        if ($this->breedSize === 'small') {
            $base += 0.05;
        }

        return round(max(1.0, $base), 2);
    }

    public function getMultiplierDescription(): string {
        $stageText = match($this->ageStage) {
            'puppy_kitten' => 'ลูกสุนัข (วัยเจริญเติบโต)',
            'senior'       => 'สุนัขสูงวัย',
            default        => 'สุนัขโตเต็มวัย' . ($this->isNeutered ? ' (ทำหมันแล้ว)' : ' (ยังไม่ทำหมัน)')
        };

        $actText = match($this->activityLevel) {
            'low'  => 'กิจกรรมน้อย',
            'high' => 'คึกคักมาก',
            default => 'กิจกรรมปกติ'
        };

        $sizeText = match($this->breedSize) {
            'small' => 'พันธุ์เล็ก',
            'large' => 'พันธุ์ใหญ่',
            default => 'พันธุ์กลาง'
        };

        return "{$stageText} • {$actText} • {$sizeText}";
    }
}

/**
 * 🐈 Class: Cat (สืบทอดจาก Pet)
 */
class Cat extends Pet {
    public function __construct(
        string $name,
        string $gender,
        float $weight,
        string $ageStage,
        string $activityLevel,
        bool $isNeutered,
        public string $livingHabit = 'indoor' // 'indoor' (เลี้ยงในบ้าน), 'outdoor' (เลี้ยงนอกบ้าน)
    ) {
        parent::__construct($name, $gender, $weight, $ageStage, $activityLevel, $isNeutered);
    }

    public function getSpeciesLabel(): string {
        return 'แมว';
    }

    public function getDERMultiplier(): float {
        // อ้างอิงตามเกณฑ์มาตรฐาน AAFCO / NRC สำหรับแมว
        if ($this->ageStage === 'puppy_kitten') {
            return 2.5; // ลูกแมว
        }

        if ($this->ageStage === 'senior') {
            $base = 1.1; // แมวสูงวัย
        } else {
            // โตเต็มวัย (Adult)
            $base = $this->isNeutered ? 1.2 : 1.4;
        }

        // การเลี้ยงดูในบ้าน (Indoor) vs นอกบ้าน (Outdoor) มีผลมากต่อพลังงาน
        if ($this->livingHabit === 'indoor') {
            $base -= 0.1;
        } elseif ($this->livingHabit === 'outdoor') {
            $base += 0.2;
        }

        // ปรับตามกิจกรรม
        if ($this->activityLevel === 'low') {
            $base -= 0.1;
        } elseif ($this->activityLevel === 'high') {
            $base += 0.2;
        }

        return round(max(1.0, $base), 2);
    }

    public function getMultiplierDescription(): string {
        $stageText = match($this->ageStage) {
            'puppy_kitten' => 'ลูกแมว (วัยเจริญเติบโต)',
            'senior'       => 'แมวสูงวัย',
            default        => 'แมวโตเต็มวัย' . ($this->isNeutered ? ' (ทำหมันแล้ว)' : ' (ยังไม่ทำหมัน)')
        };

        $habitText = $this->livingHabit === 'indoor' ? 'เลี้ยงในบ้าน (Indoor)' : 'เลี้ยงนอกบ้าน (Outdoor)';

        return "{$stageText} • {$habitText}";
    }
}
