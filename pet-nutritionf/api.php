<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// ตั้งค่าการเชื่อมต่อฐานข้อมูล PDO
$host = 'localhost';
$dbname = 'pet_nutrition_db';
$user = 'root';
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'เชื่อมต่อฐานข้อมูลล้มเหลว: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';

// 1. ตรวจสอบสถานะ Session
if ($action === 'check_session') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'logged_in' => true,
            'user_id' => $_SESSION['user_id'],
            'user_name' => $_SESSION['user_name'] ?? 'ผู้ใช้งาน'
        ]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
    exit;
}

// 2. เข้าสู่ระบบ (Login)
if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกอีเมลและรหัสผ่าน']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        echo json_encode(['success' => true, 'message' => 'เข้าสู่ระบบสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง']);
    }
    exit;
}

// 3. สมัครสมาชิก (Register)
if ($action === 'register') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($fullname) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'อีเมลนี้ถูกใช้งานแล้ว']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$fullname, $email, $hashedPassword])) {
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $fullname;
        echo json_encode(['success' => true, 'message' => 'สมัครสมาชิกสำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการสมัครสมาชิก']);
    }
    exit;
}

// 4. ออกจากระบบ (Logout)
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// 5. คำนวณสารอาหาร (Calculate) - ทำงานผ่าน 4 คลาสหลัก (Clean PHP OOP)
if ($action === 'calculate') {
    require_once __DIR__ . '/classes/Pet.php';
    require_once __DIR__ . '/classes/Food.php';
    require_once __DIR__ . '/classes/FeedingCalculator.php';
    require_once __DIR__ . '/classes/FeedingSchedule.php';

    $species = $_POST['species'] ?? 'dog';
    $name = trim($_POST['name'] ?? 'น้อง');
    $weight = floatval($_POST['weight'] ?? 1.0);
    $ageStage = $_POST['age_stage'] ?? 'adult'; 
    $activityLevel = $_POST['activity_level'] ?? 'normal';
    $isNeutered = ($_POST['is_neutered'] === 'true' || $_POST['is_neutered'] === '1' || $_POST['is_neutered'] === true);
    $breedSize = $_POST['breed_size'] ?? 'medium'; 
    $foodType = $_POST['food_type'] ?? 'dry';
    $caloriesPer100g = floatval($_POST['calories_per_100g'] ?? 85);
    $mealsCount = max(1, intval($_POST['meals_count'] ?? 2));
    $firstMealTime = $_POST['first_meal_time'] ?? '08:00';

    // 1. สร้าง Instance ตามหลัก Polymorphism & Inheritance (คลาสแม่ Pet -> คลาสลูก Dog, Cat)
    if ($species === 'dog') {
        $pet = new Dog($name, $weight, $ageStage, $activityLevel, $isNeutered, $breedSize);
    } else {
        $pet = new Cat($name, $weight, $ageStage, $activityLevel, $isNeutered);
    }

    // 2. สร้าง Instance ของอาหาร (Class Food)
    $food = new Food("อาหารสัตว์เลี้ยง", $foodType, $caloriesPer100g);

    // 3. ประมวลผลด้วยเครื่องคำนวณโภชนาการ (Class FeedingCalculator)
    $calculator = new FeedingCalculator();
    $rer = $calculator->calculateRER($pet->getWeight());
    $der = $calculator->calculateDER($pet);
    $dailyGrams = $calculator->calculateDailyPortion($pet, $food);

    // 4. จัดตารางเวลามื้ออาหาร (Class FeedingSchedule)
    $schedule = new FeedingSchedule($mealsCount, $firstMealTime);
    $slots = $schedule->getMealSlots($dailyGrams);
    $mealGrams = ($mealsCount > 0) ? $dailyGrams / $mealsCount : 0;

    echo json_encode([
        'success' => true,
        'data' => [
            'rer' => round($rer, 1),
            'factor' => round($pet->getDERMultiplier(), 2),
            'der' => round($der, 1),
            'dailyGrams' => round($dailyGrams, 1),
            'mealGrams' => round($mealGrams, 1),
            'water' => round($pet->getWeight() * 55, 1),
            'slots' => $slots
        ]
    ]);
    exit;
}

// 6. บันทึก/แก้ไข ข้อมูลสัตว์เลี้ยง (Save Pet)
if ($action === 'save_pet') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'logged_in' => false, 'message' => 'กรุณาล็อกอินก่อนบันทึกข้อมูล']);
        exit;
    }

    try {
        $pet_id = $_POST['pet_id'] ?? null;
        $name = $_POST['name'] ?? 'น้อง';
        $species = $_POST['species'] ?? 'dog';
        $weight = floatval($_POST['weight'] ?? 1.0);
        $age_stage = $_POST['age_stage'] ?? 'adult';
        $activity_level = $_POST['activity_level'] ?? 'normal';
        $is_neutered = ($_POST['is_neutered'] === 'true' || $_POST['is_neutered'] === '1' || $_POST['is_neutered'] === true) ? 1 : 0;
        $breed_size = $_POST['breed_size'] ?? 'medium';
        $food_type = $_POST['food_type'] ?? 'wet';
        $calories_per_100g = floatval($_POST['calories_per_100g'] ?? 85);
        $meals_per_day = intval($_POST['meals_per_day'] ?? 2);
        $first_meal_time = $_POST['first_meal_time'] ?? '08:00';
        $user_id = $_SESSION['user_id'];

        if ($pet_id) {
            $stmt = $pdo->prepare("UPDATE pets SET name=?, species=?, weight=?, age_stage=?, activity_level=?, is_neutered=?, breed_size=?, food_type=?, calories_per_100g=?, meals_per_day=?, first_meal_time=? WHERE id=? AND user_id=?");
            $stmt->execute([$name, $species, $weight, $age_stage, $activity_level, $is_neutered, $breed_size, $food_type, $calories_per_100g, $meals_per_day, $first_meal_time, $pet_id, $user_id]);
            echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสัตว์เลี้ยงเรียบร้อย!', 'pet_id' => $pet_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO pets (user_id, name, species, weight, age_stage, activity_level, is_neutered, breed_size, food_type, calories_per_100g, meals_per_day, first_meal_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $species, $weight, $age_stage, $activity_level, $is_neutered, $breed_size, $food_type, $calories_per_100g, $meals_per_day, $first_meal_time]);
            echo json_encode(['success' => true, 'message' => 'บันทึกสัตว์เลี้ยงใหม่เรียบร้อย!', 'pet_id' => $pdo->lastInsertId()]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด DB: ' . $e->getMessage()]);
    }
    exit;
}

// 7. ดึงรายการสัตว์เลี้ยงของผู้ใช้ (Get Pets)
if ($action === 'get_pets') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'pets' => []]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM pets WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $pets = $stmt->fetchAll();

    echo json_encode(['success' => true, 'pets' => $pets]);
    exit;
}

// 8. ลบข้อมูลสัตว์เลี้ยง (Delete Pet)
if ($action === 'delete_pet') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'กรุณาล็อกอินก่อนดำเนินการ']);
        exit;
    }

    $pet_id = $_POST['pet_id'] ?? null;
    if ($pet_id) {
        $stmt = $pdo->prepare("DELETE FROM pets WHERE id = ? AND user_id = ?");
        $stmt->execute([$pet_id, $_SESSION['user_id']]);
        echo json_encode(['success' => true, 'message' => 'ลบข้อมูลสัตว์เลี้ยงเรียบร้อย']);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบไอดีสัตว์เลี้ยงที่ต้องการลบ']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid Action']);