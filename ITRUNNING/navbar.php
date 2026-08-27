<?php
// เริ่มต้น Session เพื่อใช้งานข้อความแจ้งเตือน (ถ้ายังไม่เริ่ม)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ดึงชื่อไฟล์ปัจจุบันเพื่อใช้ทำแถบเมนูสีเข้ม (Active Menu)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT วิ่งเข้าป่ามัน - แพลตฟอร์มงานวิ่งและเทรลธรรมชาติ</title>
    
    <!-- ฟอนต์ Google Fonts 'Prompt' และ 'Kanit' สวยงาม ทันสมัย อ่านง่ายทั้งบนมือถือและคอมพิวเตอร์ -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Kanit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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
                            800: '#064E3B', // เขียวเข้มพรีเมียม
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
    
    <!-- CSS สไตล์ตกแต่งเพิ่มเติมและการสั่งพิมพ์ -->
    <style>
        body {
            font-family: 'Prompt', 'Kanit', sans-serif;
            background-color: #F8FAFC;
        }
        @media print {
            .no-print, nav, footer, #mobile-menu, button {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#F8FAFC] text-slate-800 min-h-screen flex flex-col antialiased selection:bg-brand-500 selection:text-white">

    <!-- แถบเมนูด้านบน (Navbar) Glassmorphism -->
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-sm no-print print:hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 sm:h-20 gap-3">
                
                <!-- 1. โลโก้เว็บไซต์ -->
                <a href="index.php" class="flex items-center gap-2.5 flex-shrink-0 group">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-gradient-to-tr from-brand-700 via-brand-500 to-lime-500 flex items-center justify-center shadow-md shadow-brand-500/20 text-white flex-shrink-0 group-hover:scale-105 transition-all duration-300">
                        <i data-lucide="trees" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                    </div>
                    <div class="whitespace-nowrap">
                        <div class="text-lg sm:text-xl font-black text-slate-900 leading-none">
                            IT <span class="text-brand-600">วิ่งเข้าป่ามัน</span>
                        </div>
                        <span class="text-[9px] sm:text-[10px] font-bold text-brand-700 tracking-wider uppercase block mt-0.5">Nature & Trail</span>
                    </div>
                </a>

                <!-- 2. เมนูนำทางสำหรับ Desktop -->
                <div class="hidden lg:flex items-center space-x-1.5 text-sm font-medium whitespace-nowrap">
                    <a href="index.php" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 <?= $current_page == 'index.php' || $current_page == 'event-detail.php' || $current_page == 'register.php' ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/60 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                        <i data-lucide="compass" class="w-4 h-4 text-brand-500 flex-shrink-0"></i>
                        <span>หน้าหลัก</span>
                    </a>
                    
                    <a href="calendar.php" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 <?= $current_page == 'calendar.php' ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/60 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                        <i data-lucide="calendar" class="w-4 h-4 text-brand-500 flex-shrink-0"></i>
                        <span>ปฏิทินงานวิ่ง</span>
                    </a>
                    
                    <a href="results.php" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 <?= $current_page == 'results.php' ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/60 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                        <i data-lucide="trophy" class="w-4 h-4 text-amber-500 flex-shrink-0"></i>
                        <span>ผลการแข่งขัน</span>
                    </a>

                    <a href="contact.php" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 <?= $current_page == 'contact.php' ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/60 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                        <i data-lucide="headset" class="w-4 h-4 text-sky-500 flex-shrink-0"></i>
                        <span>ติดต่อเรา</span>
                    </a>

                    <!-- ปุ่มเด่น CTA: สำหรับผู้จัดงาน -->
                    <a href="organizer.php" class="ml-1 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-lime-600 via-brand-600 to-brand-700 hover:from-lime-700 hover:to-brand-800 shadow-md shadow-brand-500/20 hover:shadow-lg transition-all flex items-center gap-1.5 transform hover:-translate-y-0.5">
                        <i data-lucide="megaphone" class="w-4 h-4 text-lime-300 flex-shrink-0"></i>
                        <span>สำหรับผู้จัดงาน</span>
                    </a>
                </div>

                <!-- 3. ส่วนค้นหา Booking Code ด้านขวา -->
                <div class="hidden md:flex items-center gap-2 flex-shrink-0">
                    <form action="confirmation.php" method="GET" class="relative">
                        <input type="text" name="booking_code" placeholder="ค้นหา BOOKING CODE..." required
                               class="w-44 lg:w-48 xl:w-56 pl-9 pr-3 py-2 text-xs bg-slate-100/90 focus:bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 uppercase transition-all shadow-inner">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    </form>
                </div>

                <!-- ปุ่มเปิดเมนูบนมือถือ -->
                <div class="flex lg:hidden">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="p-2 rounded-xl text-slate-600 hover:bg-slate-100 focus:outline-none">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>

            </div>
        </div>

        <!-- เมนูบนมือถือ (Dropdown Mobile) -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-5 space-y-2 text-sm font-medium shadow-xl no-print">
            <a href="index.php" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl <?= $current_page == 'index.php' ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-700 hover:bg-slate-50' ?>">
                <i data-lucide="compass" class="w-4 h-4 text-brand-500"></i> หน้าหลัก
            </a>
            <a href="calendar.php" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl <?= $current_page == 'calendar.php' ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-700 hover:bg-slate-50' ?>">
                <i data-lucide="calendar" class="w-4 h-4 text-brand-500"></i> ปฏิทินงานวิ่ง
            </a>
            <a href="results.php" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl <?= $current_page == 'results.php' ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-700 hover:bg-slate-50' ?>">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-500"></i> ผลการแข่งขัน
            </a>
            <a href="contact.php" class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl <?= $current_page == 'contact.php' ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-700 hover:bg-slate-50' ?>">
                <i data-lucide="headset" class="w-4 h-4 text-sky-500"></i> ติดต่อเรา
            </a>
            <a href="organizer.php" class="flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl font-bold text-white bg-gradient-to-r from-lime-600 to-brand-600 shadow-md">
                <i data-lucide="megaphone" class="w-4 h-4 text-lime-200"></i> สำหรับผู้จัดงาน
            </a>
            
            <div class="pt-2 border-t border-slate-100">
                <form action="confirmation.php" method="GET" class="relative">
                    <input type="text" name="booking_code" placeholder="ค้นหา BOOKING CODE..." required
                           class="w-full pl-9 pr-4 py-2.5 text-xs bg-slate-100 border border-slate-200 rounded-xl focus:outline-none uppercase">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                </form>
            </div>
        </div>
    </nav>

    <!-- กล่องแจ้งเตือน Flash Message -->
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="max-w-4xl mx-auto px-4 mt-4 no-print">
            <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-between text-xs sm:text-sm font-semibold shadow-sm">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
                    <span><?= $_SESSION['msg'] ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-base font-bold ml-2">✕</button>
            </div>
        </div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="max-w-4xl mx-auto px-4 mt-4 no-print">
            <div class="p-4 rounded-2xl bg-rose-50 text-rose-800 border border-rose-200 flex items-center justify-between text-xs sm:text-sm font-semibold shadow-sm">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 flex-shrink-0"></i>
                    <span><?= $_SESSION['error'] ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 text-base font-bold ml-2">✕</button>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <main class="flex-grow">
