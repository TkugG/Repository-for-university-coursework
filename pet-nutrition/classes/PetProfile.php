<?php
/**
 * PetProfile — CLASS A
 * 
 * รับผิดชอบ: CRUD ข้อมูลสัตว์เลี้ยง + คำนวณ nutrition ของแต่ละตัว
 * ใช้งานร่วมกับ NutritionEngine (shared)
 */

require_once __DIR__ . '/NutritionEngine.php';

class PetProfile {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // ─────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO pets (name, species, breed, age_months, weight_kg, ideal_weight_kg, life_stage, activity_level, notes, created_at)
            VALUES (:name, :species, :breed, :age_months, :weight_kg, :ideal_weight_kg, :life_stage, :activity_level, :notes, NOW())
        ");

        $stmt->execute([
            ':name'             => $data['name'],
            ':species'          => $data['species'],       // 'dog' | 'cat'
            ':breed'            => $data['breed'] ?? '',
            ':age_months'       => (int)$data['age_months'],
            ':weight_kg'        => (float)$data['weight_kg'],
            ':ideal_weight_kg'  => (float)($data['ideal_weight_kg'] ?? $data['weight_kg']),
            ':life_stage'       => $data['life_stage'],
            ':activity_level'   => $data['activity_level'] ?? 'moderate',
            ':notes'            => $data['notes'] ?? '',
        ]);

        return (int)$this->db->lastInsertId();
    }

    // ─────────────────────────────────────────
    // READ
    // ─────────────────────────────────────────

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM pets ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM pets WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ─────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE pets SET
                name             = :name,
                species          = :species,
                breed            = :breed,
                age_months       = :age_months,
                weight_kg        = :weight_kg,
                ideal_weight_kg  = :ideal_weight_kg,
                life_stage       = :life_stage,
                activity_level   = :activity_level,
                notes            = :notes
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id'               => $id,
            ':name'             => $data['name'],
            ':species'          => $data['species'],
            ':breed'            => $data['breed'] ?? '',
            ':age_months'       => (int)$data['age_months'],
            ':weight_kg'        => (float)$data['weight_kg'],
            ':ideal_weight_kg'  => (float)($data['ideal_weight_kg'] ?? $data['weight_kg']),
            ':life_stage'       => $data['life_stage'],
            ':activity_level'   => $data['activity_level'] ?? 'moderate',
            ':notes'            => $data['notes'] ?? '',
        ]);
    }

    // ─────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM pets WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ─────────────────────────────────────────
    // NUTRITION ANALYSIS — ใช้ NutritionEngine
    // ─────────────────────────────────────────

    public function getNutritionNeeds(int $petId): ?array {
        $pet = $this->getById($petId);
        if (!$pet) return null;

        // เรียกใช้ NutritionEngine (shared class)
        $der = NutritionEngine::calculateDER(
            (float)$pet['weight_kg'],
            $pet['species'],
            $pet['life_stage'],
            $pet['activity_level']
        );

        $bcs = NutritionEngine::analyzeBCS(
            (float)$pet['weight_kg'],
            (float)$pet['ideal_weight_kg']
        );

        return [
            'pet'       => $pet,
            'nutrition' => $der,
            'bcs'       => $bcs,
        ];
    }

    /**
     * Life stages ที่รองรับ ใช้แสดง dropdown ใน UI
     */
    public static function getLifeStages(string $species): array {
        $stages = [
            'dog' => [
                'puppy'        => 'ลูกสุนัข (< 4 เดือน)',
                'puppy_adult'  => 'ลูกสุนัข (4-12 เดือน)',
                'adult'        => 'สุนัขผู้ใหญ่ (ทำหมัน)',
                'adult_intact' => 'สุนัขผู้ใหญ่ (ไม่ทำหมัน)',
                'senior'       => 'สุนัขสูงวัย (> 7 ปี)',
                'pregnant'     => 'ตั้งท้อง',
                'lactating'    => 'ให้นม',
            ],
            'cat' => [
                'kitten'       => 'ลูกแมว (< 1 ปี)',
                'adult'        => 'แมวผู้ใหญ่ (ทำหมัน)',
                'adult_intact' => 'แมวผู้ใหญ่ (ไม่ทำหมัน)',
                'senior'       => 'แมวสูงวัย (> 10 ปี)',
                'pregnant'     => 'ตั้งท้อง',
                'lactating'    => 'ให้นม',
            ],
        ];
        return $stages[$species] ?? [];
    }
}
