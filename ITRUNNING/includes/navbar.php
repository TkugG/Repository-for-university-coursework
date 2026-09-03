<?php
/**
 * ==========================================================
 * ไฟล์: includes/navbar.php
 * คำอธิบาย: แถบเมนูนำทางหลักของเว็บไซต์ (Navbar Component)
 * ==========================================================
 */

$current_page = basename($_SERVER['PHP_SELF']);
?>
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
                <a href="index.php" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 <?= in_array($current_page, ['index.php', 'event-detail.php', 'register.php']) ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/60 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
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

                <a href="news.php" class="px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5 <?= $current_page == 'news.php' ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/60 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                    <i data-lucide="newspaper" class="w-4 h-4 text-brand-500 flex-shrink-0"></i>
                    <span>ข่าวสาร</span>
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

            <!-- 3. ส่วนค้นหา Booking Code ด้านขวา (Desktop) -->
            <div class="hidden md:flex items-center gap-2 flex-shrink-0">
                <form action="confirmation.php" method="GET" class="relative">
                    <input type="text" name="booking_code" placeholder="ค้นหา BOOKING CODE..." required
                           class="w-48 lg:w-56 pl-9 pr-4 py-2 bg-slate-100 hover:bg-slate-200/70 focus:bg-white text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all uppercase placeholder:normal-case shadow-inner">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                </form>
            </div>

            <!-- 4. ปุ่มเปิดเมนูบนมือถือ (Mobile Hamburger Button) -->
            <div class="flex items-center lg:hidden gap-2">
                <button id="mobile-menu-btn" type="button" aria-label="เปิดเมนู"
                        class="p-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-brand-50 hover:text-brand-600 transition-colors focus:outline-none">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>

        </div>
    </div>

    <!-- เมนูสำหรับจอมือถือ (Mobile Drawer) -->
    <div id="mobile-menu" class="hidden lg:hidden border-t border-slate-100 bg-white/95 backdrop-blur-lg px-4 pt-3 pb-6 space-y-2 shadow-xl">
        <form action="confirmation.php" method="GET" class="relative mb-3">
            <input type="text" name="booking_code" placeholder="ค้นหา BOOKING CODE..." required
                   class="w-full pl-9 pr-4 py-2.5 bg-slate-100 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 uppercase placeholder:normal-case">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
        </form>

        <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium <?= in_array($current_page, ['index.php', 'event-detail.php', 'register.php']) ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
            <i data-lucide="compass" class="w-4 h-4 text-brand-500"></i>
            <span>หน้าหลัก</span>
        </a>

        <a href="calendar.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium <?= $current_page == 'calendar.php' ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
            <i data-lucide="calendar" class="w-4 h-4 text-brand-500"></i>
            <span>ปฏิทินงานวิ่ง</span>
        </a>

        <a href="results.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium <?= $current_page == 'results.php' ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
            <i data-lucide="trophy" class="w-4 h-4 text-amber-500"></i>
            <span>ผลการแข่งขัน</span>
        </a>

        <a href="news.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium <?= $current_page == 'news.php' ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
            <i data-lucide="newspaper" class="w-4 h-4 text-brand-500"></i>
            <span>ข่าวสาร</span>
        </a>

        <a href="contact.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium <?= $current_page == 'contact.php' ? 'bg-brand-50 text-brand-700 font-bold' : 'text-slate-700 hover:bg-slate-100' ?>">
            <i data-lucide="headset" class="w-4 h-4 text-sky-500"></i>
            <span>ติดต่อเรา</span>
        </a>

        <a href="organizer.php" class="flex items-center justify-center gap-2 mt-3 px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-md">
            <i data-lucide="megaphone" class="w-4 h-4 text-lime-300"></i>
            <span>สำหรับผู้จัดงานและประชาสัมพันธ์</span>
        </a>
    </div>
</nav>
