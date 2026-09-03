<?php
/**
 * ==========================================================
 * ไฟล์: includes/header.php
 * คำอธิบาย: โครงสร้างส่วนหัว HTML, CDN และสไตล์ส่วนกลาง
 * ==========================================================
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/functions.php';

$page_title = isset($page_title) ? $page_title . " | " . APP_NAME : APP_NAME . " - " . APP_TAGLINE;
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    
    <!-- Google Fonts: 'Prompt' และ 'Kanit' -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#ECFDF5',
                            100: '#D1FAE5',
                            200: '#A7F3D0',
                            500: '#059669', // Deep Emerald
                            600: '#047857', // Forest Green
                            700: '#065F46',
                            800: '#064E3B', // Dark Forest
                            900: '#022C22',
                        },
                        lime: {
                            400: '#A3E635',
                            500: '#84CC16',
                            600: '#65A30D'
                        }
                    },
                    fontFamily: {
                        sans: ['Prompt', 'Kanit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS Stylesheet -->
    <link rel="stylesheet" href="assets/css/custom.css">
    
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#F8FAFC] text-slate-800 min-h-screen flex flex-col antialiased selection:bg-brand-500 selection:text-white">

<?php 
// นำเข้า Navbar อัตโนมัติ
require_once __DIR__ . '/navbar.php'; 

// แสดงผลข้อความแจ้งเตือน Flash Messages (ถ้ามี)
render_flash_alert();
?>

<!-- พื้นที่แสดงเนื้อหาหลัก -->
<main class="flex-grow">
