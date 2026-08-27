<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    echo "<h2 style='color: green;'> Connect Database Successfully!</h2>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'> Connection Failed: " . $e->getMessage() . "</h2>";
}