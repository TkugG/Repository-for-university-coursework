<?php
/**
 * ==========================================================
 * ไฟล์: calendar.php
 * คำอธิบาย: หน้าปฏิทินกิจกรรมงานวิ่งแบบ Interactive ด้วย FullCalendar JS
 * ==========================================================
 */

$page_title = "ปฏิทินงานวิ่ง (Running Calendar)";
require_once __DIR__ . '/includes/header.php';
?>

<!-- โหลด FullCalendar JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<div class="max-w-7xl mx-auto px-4 py-8 sm:py-12">
    
    <!-- หัวข้อหน้า -->
    <div class="text-center space-y-2 mb-8">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-50 text-brand-700 text-xs sm:text-sm font-bold border border-brand-200">
            <i data-lucide="calendar" class="w-4 h-4 text-brand-600"></i> Event Calendar
        </div>
        <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 flex items-center justify-center gap-2">
            ปฏิทินกิจกรรมงานวิ่ง 2026
        </h1>
        <p class="text-xs sm:text-sm text-slate-500">คลิกที่รายการงานวิ่งในปฏิทินเพื่อดูรายละเอียดและสมัคร</p>
    </div>

    <!-- แถบสัญลักษณ์สี -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-wrap gap-4 text-xs font-semibold text-slate-600 justify-center">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-600"></span> Full Marathon (42K)</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-orange-600"></span> Half Marathon (21K)</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-sky-600"></span> Mini Marathon (10K)</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-purple-600"></span> Trail Challenge</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-600"></span> Fun Run</span>
    </div>

    <!-- กล่องแสดงปฏิทิน FullCalendar -->
    <div class="bg-white p-4 sm:p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div id="calendar" class="min-h-[600px]"></div>
    </div>

</div>

<!-- สคริปต์สั่งให้ FullCalendar ดึงข้อมูลจาก api/events.php -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (calendarEl && typeof FullCalendar !== 'undefined') {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'th',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            buttonText: {
                today: 'วันนี้',
                month: 'เดือน',
                week: 'สัปดาห์',
                list: 'รายการ'
            },
            events: 'api/events.php' // ดึง JSON จาก REST API
        });
        calendar.render();
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
