<?php
// ==========================================
// 1. นำเข้าแถบเมนู
// ==========================================
require_once "navbar.php";

// 2. ข้อมูลข่าวสารประชาสัมพันธ์ (Array Mock Data)
$news = [
    [
        'title' => 'คู่มือเตรียมตัววิ่งเทรลครั้งแรก: อุปกรณ์ที่ขาดไม่ได้และการฝึกซ้อมในป่า',
        'category' => '💡 เคล็ดลับนักวิ่ง',
        'date' => '18 ส.ค. 2026',
        'desc' => 'แนะนำอุปกรณ์บังคับสำหรับนักวิ่งสายเทรล เป้น้ำ รองเท้าเทรลดอกยางลึก ไม้โพล และวิธีปรับตัวกับความชันในป่าเขา',
        'img' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'title' => 'ประกาศจุดรับเสื้อและเบอร์วิ่ง (BIB) งานบางแสน21 ฮาล์ฟมาราธอน 2026',
        'category' => '📢 ประกาศสำคัญ',
        'date' => '15 ส.ค. 2026',
        'desc' => 'แจ้งกำหนดการรับอุปกรณ์ ณ โรงแรมเดอะไทด์ บางแสน กรุณานำบัตร E-Ticket และบัตรประชาชนมาแสดง',
        'img' => 'https://images.unsplash.com/photo-1530549387789-4c1017266635?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'title' => 'เปิดเส้นทางใหม่ "เขาใหญ่ ไนท์เทรล 2027" วิ่งชมดาวท่ามกลางผืนป่ามรดกโลก',
        'category' => '🏃 ข่าวสารงานวิ่ง',
        'date' => '10 ส.ค. 2026',
        'desc' => 'สัมผัสประสบการณ์วิ่งกลางคืนสุดเอ็กซ์คลูซีฟ จำกัดเพียง 800 สิทธิ์ พร้อมมาตรการดูแลความปลอดภัยระดับสูงสุด',
        'img' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=800&q=80'
    ],
    [
        'title' => '5 ท่าวอร์มอัพและยืดเหยียดป้องกันตะคริว สำหรับนักวิ่งทุกระยะทาง',
        'category' => '💡 เคล็ดลับนักวิ่ง',
        'date' => '05 ส.ค. 2026',
        'desc' => 'เทคนิคการ Dynamic Stretching ก่อนออกสตาร์ต ช่วยลดการบาดเจ็บของกล้ามเนื้อน่องและเอ็นร้อยหวาย',
        'img' => 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=800&q=80'
    ]
];
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    
    <!-- หัวข้อหน้า -->
    <div class="text-center space-y-2 mb-8">
        <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 flex items-center justify-center gap-2">
            <i data-lucide="newspaper" class="w-8 h-8 text-brand-600"></i> ข่าวสารและประชาสัมพันธ์ (News & PR)
        </h1>
        <p class="text-xs sm:text-sm text-slate-500">รวมข่าวสารงานวิ่ง ประกาศรับ BIB และเกร็ดความรู้สำหรับนักวิ่ง</p>
    </div>

    <!-- วนลูปแสดงการ์ดข่าวสาร -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($news as $item): ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow">
                <div>
                    <img src="<?= $item['img'] ?>" alt="" class="w-full h-40 object-cover">
                    <div class="p-4 space-y-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-brand-700 border border-brand-200">
                            <?= $item['category'] ?>
                        </span>
                        <h3 class="font-bold text-sm text-slate-900 line-clamp-2"><?= $item['title'] ?></h3>
                        <p class="text-xs text-slate-500 line-clamp-3"><?= $item['desc'] ?></p>
                    </div>
                </div>
                <div class="p-4 pt-0 text-[11px] text-slate-400 flex justify-between items-center border-t border-slate-50 mt-2">
                    <span>📅 <?= $item['date'] ?></span>
                    <button onclick="alert('อ่านรายละเอียดข่าว: <?= addslashes($item['title']) ?>')" class="text-brand-600 font-bold hover:underline">อ่านต่อ</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require_once "footer.php"; ?>
