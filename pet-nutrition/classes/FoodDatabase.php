<?php
/**
 * FoodDatabase — CLASS C
 * 
 * รับผิดชอบ: ฐานข้อมูลอาหาร + nutrition label ของอาหารแต่ละชนิด
 * ใช้งานร่วมกับ NutritionEngine (shared)
 */

require_once __DIR__ . '/NutritionEngine.php';

class FoodDatabase {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // ─────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO foods (
                name, brand, category, food_type,
                suitable_for, calories_per_100g,
                protein_g, fat_g, carbs_g, fiber_g, water_g,
                is_toxic, toxic_note, created_at
            ) VALUES (
                :name, :brand, :category, :food_type,
                :suitable_for, :calories_per_100g,
                :protein_g, :fat_g, :carbs_g, :fiber_g, :water_g,
                :is_toxic, :toxic_note, NOW()
            )
        ");

        $stmt->execute([
            ':name'               => $data['name'],
            ':brand'              => $data['brand'] ?? '',
            ':category'           => $data['category'],   // 'dry', 'wet', 'treat', 'homemade'
            ':food_type'          => $data['food_type'],  // 'commercial', 'raw', 'homemade'
            ':suitable_for'       => $data['suitable_for'], // 'dog', 'cat', 'both'
            ':calories_per_100g'  => (float)$data['calories_per_100g'],
            ':protein_g'          => (float)$data['protein_g'],
            ':fat_g'              => (float)$data['fat_g'],
            ':carbs_g'            => (float)$data['carbs_g'],
            ':fiber_g'            => (float)($data['fiber_g'] ?? 0),
            ':water_g'            => (float)($data['water_g'] ?? 0),
            ':is_toxic'           => (int)($data['is_toxic'] ?? 0),
            ':toxic_note'         => $data['toxic_note'] ?? '',
        ]);

        return (int)$this->db->lastInsertId();
    }

    // ─────────────────────────────────────────
    // READ
    // ─────────────────────────────────────────

    public function getAll(string $species = '', string $category = ''): array {
        $where = ['1=1'];
        $params = [];

        if ($species) {
            $where[] = "(suitable_for = :species OR suitable_for = 'both')";
            $params[':species'] = $species;
        }
        if ($category) {
            $where[] = "category = :category";
            $params[':category'] = $category;
        }

        $sql = "SELECT * FROM foods WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM foods WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function search(string $keyword, string $species = ''): array {
        $params = [':keyword' => "%{$keyword}%"];
        $speciesClause = '';
        if ($species) {
            $speciesClause = "AND (suitable_for = :species OR suitable_for = 'both')";
            $params[':species'] = $species;
        }

        $stmt = $this->db->prepare("
            SELECT * FROM foods
            WHERE (name LIKE :keyword OR brand LIKE :keyword)
            {$speciesClause}
            ORDER BY name ASC
            LIMIT 50
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE foods SET
                name              = :name,
                brand             = :brand,
                category          = :category,
                food_type         = :food_type,
                suitable_for      = :suitable_for,
                calories_per_100g = :calories_per_100g,
                protein_g         = :protein_g,
                fat_g             = :fat_g,
                carbs_g           = :carbs_g,
                fiber_g           = :fiber_g,
                water_g           = :water_g,
                is_toxic          = :is_toxic,
                toxic_note        = :toxic_note
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id'                 => $id,
            ':name'               => $data['name'],
            ':brand'              => $data['brand'] ?? '',
            ':category'           => $data['category'],
            ':food_type'          => $data['food_type'],
            ':suitable_for'       => $data['suitable_for'],
            ':calories_per_100g'  => (float)$data['calories_per_100g'],
            ':protein_g'          => (float)$data['protein_g'],
            ':fat_g'              => (float)$data['fat_g'],
            ':carbs_g'            => (float)$data['carbs_g'],
            ':fiber_g'            => (float)($data['fiber_g'] ?? 0),
            ':water_g'            => (float)($data['water_g'] ?? 0),
            ':is_toxic'           => (int)($data['is_toxic'] ?? 0),
            ':toxic_note'         => $data['toxic_note'] ?? '',
        ]);
    }

    // ─────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM foods WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ─────────────────────────────────────────
    // NUTRITION ANALYSIS — ใช้ NutritionEngine
    // ─────────────────────────────────────────

    /**
     * คำนวณ nutrient ของอาหารตามกรัมที่ระบุ
     */
    public function calcNutrientsForAmount(int $foodId, float $grams): ?array {
        $food = $this->getById($foodId);
        if (!$food) return null;

        // เรียกใช้ NutritionEngine (shared class)
        $nutrients = NutritionEngine::calculateFoodNutrients($food, $grams);

        return [
            'food'      => $food,
            'grams'     => $grams,
            'nutrients' => $nutrients,
        ];
    }

    /**
     * ตรวจสอบอาหารที่เป็นพิษกับสัตว์ชนิดนั้น
     */
    public function getToxicFoods(string $species = ''): array {
        $params = [];
        $speciesClause = '';
        if ($species) {
            $speciesClause = "AND (suitable_for = :species OR suitable_for = 'both')";
            $params[':species'] = $species;
        }

        $stmt = $this->db->prepare("
            SELECT name, toxic_note, suitable_for FROM foods
            WHERE is_toxic = 1 {$speciesClause}
            ORDER BY name ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Categories ที่รองรับ
     */
    public static function getCategories(): array {
        return [
            'dry'      => 'อาหารเม็ด (Dry Food)',
            'wet'      => 'อาหารเปียก (Wet Food)',
            'treat'    => 'ขนมรางวัล (Treat)',
            'raw'      => 'อาหารดิบ (Raw)',
            'homemade' => 'อาหารทำเอง',
        ];
    }
}
