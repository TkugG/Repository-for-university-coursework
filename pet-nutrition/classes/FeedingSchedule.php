<?php
/**
 * ⏰ ก้อนจัดตารางเวลา (Class: FeedingSchedule)
 * 
 * เอาผลคำนวณจาก FeedingCalculator มาซอยเป็นมื้อ และกระจายเวลา
 */

class FeedingSchedule {
    public array $reminderTimes = [];

    public function __construct(
        public int $mealsPerDay = 2,
        public string $firstMealTime = '08:00'
    ) {
        if ($this->mealsPerDay < 1) {
            throw new InvalidArgumentException("จำนวนมื้อต้องอย่างน้อย 1 มื้อ");
        }
        $this->reminderTimes = $this->generateSchedule();
    }

    /**
     * คำนวณปริมาณอาหารต่อหนึ่งมื้อ (กรัม/มื้อ)
     * เอากรัมต่อวันมาหารจำนวนมื้อ
     */
    public function calculatePortionPerMeal(float $dailyGrams): float {
        return round($dailyGrams / $this->mealsPerDay, 1);
    }

    /**
     * กระจายเวลาแต่ละมื้อ โดยยึดกรอบเวลากลางวัน (Daytime Active Hours 10-12 ชม.)
     * หลีกเลี่ยงไม่ให้มีมื้อดึกตอนกลางคืนที่เจ้าของและสัตว์เลี้ยงหลับ
     */
    public function generateSchedule(): array {
        $firstTs = strtotime($this->firstMealTime);
        if ($firstTs === false) {
            $firstTs = strtotime('08:00');
        }

        if ($this->mealsPerDay === 1) {
            return [date('H:i', $firstTs)];
        }

        // กรอบเวลากลางวัน 10 - 12 ชั่วโมง (เช้าจรดเย็น/ค่ำ)
        $totalSpanMinutes = ($this->mealsPerDay >= 4) ? 720 : 600; // 10-12 ชั่วโมง
        $intervalMinutes = (int)round($totalSpanMinutes / ($this->mealsPerDay - 1));

        $times = [];
        for ($i = 0; $i < $this->mealsPerDay; $i++) {
            $mealTs = $firstTs + ($i * $intervalMinutes * 60);
            $times[] = date('H:i', $mealTs);
        }

        $this->reminderTimes = $times;
        return $times;
    }

    /**
     * สร้างตารางแจกแจงมื้ออาหารอย่างละเอียด (Time Slots พร้อมชื่อมื้อและปริมาณ)
     */
    public function getDetailedMealSlots(float $dailyGrams): array {
        $times = $this->generateSchedule();
        $portion = $this->calculatePortionPerMeal($dailyGrams);

        $mealNamesByCount = [
            1 => ['มื้อหลักประจำวัน'],
            2 => ['มื้อเช้า', 'มื้อเย็น'],
            3 => ['มื้อเช้า', 'มื้อกลางวัน', 'มื้อเย็น'],
            4 => ['มื้อเช้า', 'มื้อกลางวัน', 'มื้อบ่าย/เย็น', 'มื้อค่ำ'],
            5 => ['มื้อเช้า', 'มื้อสาย', 'มื้อกลางวัน', 'มื้อบ่าย', 'มื้อเย็น'],
        ];

        $names = $mealNamesByCount[$this->mealsPerDay] ?? array_map(fn($i) => "มื้อที่ " . ($i + 1), range(0, $this->mealsPerDay - 1));

        $slots = [];
        foreach ($times as $index => $time) {
            $slots[] = [
                'meal_number'   => $index + 1,
                'meal_name'     => $names[$index] ?? ("มื้อที่ " . ($index + 1)),
                'time'          => $time,
                'portion_grams' => $portion,
            ];
        }

        return $slots;
    }
}
