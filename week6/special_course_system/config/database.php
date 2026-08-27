<?php
/**
 * Database Configuration
 *
 * อ่านค่าจาก Environment Variables ก่อน ถ้าไม่มีจึงใช้ค่า default
 * เพื่อรองรับทั้ง Production (ตั้งค่า ENV) และ Development (แก้ค่าตรงนี้)
 *
 * วิธีตั้งค่า ENV บน Linux/macOS:
 *   export DB_HOST=localhost DB_NAME=special_course_db DB_USER=root DB_PASS=secret
 *
 * วิธีตั้งค่าบน Apache (.htaccess):
 *   SetEnv DB_HOST localhost
 *   SetEnv DB_PASS secret
 */

define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME')    ?: 'special_course_db');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

/**
 * PDO Options
 * - ERRMODE_EXCEPTION  : โยน PDOException เมื่อ query ผิดพลาด
 * - FETCH_ASSOC        : คืนผลลัพธ์เป็น associative array
 * - EMULATE_PREPARES   : false = ใช้ true prepared statements (ป้องกัน SQL Injection)
 */
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
]);
