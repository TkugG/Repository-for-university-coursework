<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">ศูนย์จัดการใบสั่งซ่อม</h1>
            <p class="text-base text-slate-600 font-medium">คัดกรอง อนุมัติรับเรื่อง จ่ายงานช่าง และติดตามกำหนดเวลาตามข้อตกลง SLA</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1.5 shadow-xs">
                <?= svg_icon('ticket', 'w-4 h-4 text-indigo-600') ?>
                พบใบงานทั้งหมด <?= count($tickets) ?> รายการ
            </span>
        </div>
    </div>

    <!-- Quick Filter Capsules (Operational Workflow) -->
    <?php
    $currentStatus = $filters['status'] ?? '';
    $currentTech = $filters['technician_id'] ?? '';
    $currentSla = $filters['sla_filter'] ?? '';
    $isAll = empty($currentStatus) && empty($currentTech) && empty($currentSla) && empty($filters['keyword'] ?? '');
    $hasAdvancedFilters = !empty($filters['priority']) || (!empty($filters['technician_id']) && $filters['technician_id'] !== 'unassigned') || !empty($filters['keyword']) || (!empty($filters['status']) && !in_array($filters['status'], ['pending_approval', 'in_progress', 'waiting_parts', 'resolved', 'closed']));
    ?>
    <div class="space-y-4">
        <!-- Capsule Track (แคปซูลแสดงสถานะงานหลัก) -->
        <div class="admin-capsule-grid" id="admin-capsule-track">
            <!-- 1. ทั้งหมด -->
            <a href="<?= url('/admin/tickets') ?>" 
               class="capsule-btn border <?= $isAll ? 'active bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-500/25 ring-2 ring-indigo-500/20' : 'border-slate-300 bg-white text-slate-800 hover:bg-slate-50 hover:border-slate-400 shadow-2xs' ?>">
                <div class="flex items-center gap-2 truncate">
                    <?= svg_icon('filter', 'w-4 h-4 shrink-0 ' . ($isAll ? 'text-white' : 'text-slate-600')) ?>
                    <span class="truncate">ทั้งหมด</span>
                </div>
                <span class="capsule-badge shrink-0 <?= $isAll ? 'bg-white text-indigo-700 shadow-2xs' : 'bg-slate-200 text-slate-800' ?>"><?= $counts['total'] ?? count($tickets) ?></span>
            </a>

            <!-- 2. รออนุมัติ -->
            <a href="<?= url('/admin/tickets?status=pending_approval') ?>" 
               class="capsule-btn border <?= $currentStatus === 'pending_approval' ? 'active bg-amber-600 text-white border-amber-600 shadow-md shadow-amber-500/25 ring-2 ring-amber-500/20' : 'border-amber-300 bg-amber-50/80 text-amber-950 hover:bg-amber-100 shadow-2xs' ?>">
                <div class="flex items-center gap-2 truncate">
                    <?= svg_icon('clock', 'w-4 h-4 shrink-0 ' . ($currentStatus === 'pending_approval' ? 'text-white' : 'text-amber-600')) ?>
                    <span class="truncate">รออนุมัติ</span>
                </div>
                <span class="capsule-badge shrink-0 <?= $currentStatus === 'pending_approval' ? 'bg-white text-amber-700 shadow-2xs' : 'bg-amber-200 text-amber-950' ?>"><?= $counts['pending_approval'] ?? 0 ?></span>
            </a>

            <!-- 3. รอจ่ายงาน -->
            <a href="<?= url('/admin/tickets?technician_id=unassigned') ?>" 
               class="capsule-btn border <?= $currentTech === 'unassigned' ? 'active bg-purple-600 text-white border-purple-600 shadow-md shadow-purple-500/25 ring-2 ring-purple-500/20' : 'border-purple-300 bg-purple-50/80 text-purple-950 hover:bg-purple-100 shadow-2xs' ?>">
                <div class="flex items-center gap-2 truncate">
                    <?= svg_icon('user', 'w-4 h-4 shrink-0 ' . ($currentTech === 'unassigned' ? 'text-white' : 'text-purple-600')) ?>
                    <span class="truncate">รอจ่ายงาน</span>
                </div>
                <span class="capsule-badge shrink-0 <?= $currentTech === 'unassigned' ? 'bg-white text-purple-700 shadow-2xs' : 'bg-purple-200 text-purple-950' ?>"><?= $unassignedCount ?? 0 ?></span>
            </a>

            <!-- 4. กำลังดำเนินการ -->
            <a href="<?= url('/admin/tickets?status=in_progress') ?>" 
               class="capsule-btn border <?= $currentStatus === 'in_progress' ? 'active bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/25 ring-2 ring-blue-500/20' : 'border-blue-300 bg-blue-50/80 text-blue-950 hover:bg-blue-100 shadow-2xs' ?>">
                <div class="flex items-center gap-2 truncate">
                    <?= svg_icon('wrench', 'w-4 h-4 shrink-0 ' . ($currentStatus === 'in_progress' ? 'text-white' : 'text-blue-600')) ?>
                    <span class="truncate">กำลังดำเนินการ</span>
                </div>
                <span class="capsule-badge shrink-0 <?= $currentStatus === 'in_progress' ? 'bg-white text-blue-700 shadow-2xs' : 'bg-blue-200 text-blue-950' ?>"><?= $counts['in_progress'] ?? 0 ?></span>
            </a>

            <!-- 5. รออะไหล่ -->
            <a href="<?= url('/admin/tickets?status=waiting_parts') ?>" 
               class="capsule-btn border <?= $currentStatus === 'waiting_parts' ? 'active bg-orange-600 text-white border-orange-600 shadow-md shadow-orange-500/25 ring-2 ring-orange-500/20' : 'border-orange-300 bg-orange-50/80 text-orange-950 hover:bg-orange-100 shadow-2xs' ?>">
                <div class="flex items-center gap-2 truncate">
                    <?= svg_icon('package', 'w-4 h-4 shrink-0 ' . ($currentStatus === 'waiting_parts' ? 'text-white' : 'text-orange-600')) ?>
                    <span class="truncate">รออะไหล่</span>
                </div>
                <span class="capsule-badge shrink-0 <?= $currentStatus === 'waiting_parts' ? 'bg-white text-orange-700 shadow-2xs' : 'bg-orange-200 text-orange-950' ?>"><?= $counts['waiting_parts'] ?? 0 ?></span>
            </a>

            <!-- 6. ซ่อมเสร็จสิ้น -->
            <a href="<?= url('/admin/tickets?status=resolved') ?>" 
               class="capsule-btn border <?= $currentStatus === 'resolved' ? 'active bg-teal-600 text-white border-teal-600 shadow-md shadow-teal-500/25 ring-2 ring-teal-500/20' : 'border-teal-300 bg-teal-50/80 text-teal-950 hover:bg-teal-100 shadow-2xs' ?>">
                <div class="flex items-center gap-2 truncate">
                    <?= svg_icon('check-circle', 'w-4 h-4 shrink-0 ' . ($currentStatus === 'resolved' ? 'text-white' : 'text-teal-600')) ?>
                    <span class="truncate">ซ่อมเสร็จสิ้น</span>
                </div>
                <span class="capsule-badge shrink-0 <?= $currentStatus === 'resolved' ? 'bg-white text-teal-700 shadow-2xs' : 'bg-teal-200 text-teal-950' ?>"><?= $counts['resolved'] ?? 0 ?></span>
            </a>

            <!-- 7. ปิดงานแล้ว -->
            <a href="<?= url('/admin/tickets?status=closed') ?>" 
               class="capsule-btn border <?= $currentStatus === 'closed' ? 'active bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-500/25 ring-2 ring-emerald-500/20' : 'border-emerald-300 bg-emerald-50/80 text-emerald-950 hover:bg-emerald-100 shadow-2xs' ?>">
                <div class="flex items-center gap-2 truncate">
                    <?= svg_icon('check', 'w-4 h-4 shrink-0 ' . ($currentStatus === 'closed' ? 'text-white' : 'text-emerald-600')) ?>
                    <span class="truncate">ปิดงานแล้ว</span>
                </div>
                <span class="capsule-badge shrink-0 <?= $currentStatus === 'closed' ? 'bg-white text-emerald-700 shadow-2xs' : 'bg-emerald-200 text-emerald-950' ?>"><?= $counts['closed'] ?? 0 ?></span>
            </a>

            <!-- 8. เกินเวลา SLA -->
            <a href="<?= url('/admin/tickets?sla_filter=overdue') ?>" 
               class="capsule-btn border <?= $currentSla === 'overdue' ? 'active bg-rose-600 text-white border-rose-600 shadow-md shadow-rose-500/25 ring-2 ring-rose-500/20' : 'border-rose-400 bg-rose-50/90 text-rose-950 hover:bg-rose-100 shadow-2xs ring-1 ring-rose-400/30' ?>">
                <div class="flex items-center gap-2 truncate">
                    <?= svg_icon('alert-triangle', 'w-4 h-4 shrink-0 ' . ($currentSla === 'overdue' ? 'text-white' : 'text-rose-600')) ?>
                    <span class="truncate">เกินเวลา SLA</span>
                </div>
                <span class="capsule-badge shrink-0 <?= $currentSla === 'overdue' ? 'bg-white text-rose-700 shadow-2xs' : 'bg-rose-200 text-rose-950' ?>"><?= $counts['overdue'] ?? 0 ?></span>
            </a>
        </div>

        <!-- Instant Search Bar & Controls Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs">
            <!-- Real-time Instant Search Input -->
            <div class="relative flex-1 max-w-xl">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <?= svg_icon('search', 'w-4 h-4') ?>
                </span>
                <input type="text" id="admin-table-search" placeholder="พิมพ์ค้นหาด่วนในหน้านี้ (รหัส #, เรื่อง, สถานที่, ผู้แจ้ง, ช่าง)..." 
                       class="w-full pl-10 pr-9 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 rounded-full text-base text-slate-800 placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition shadow-2xs">
                <button type="button" id="admin-clear-search-btn" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 hidden text-lg font-bold">
                    &times;
                </button>
            </div>

            <!-- Visible Counter & Advanced Filter Toggle -->
            <div class="flex items-center gap-2.5 flex-wrap justify-between md:justify-end">
                <span class="text-sm font-bold px-3.5 py-2 rounded-full bg-slate-100 text-slate-700 whitespace-nowrap">
                    แสดง <span id="admin-visible-count" class="text-indigo-600 font-extrabold"><?= count($tickets) ?></span> จาก <?= count($tickets) ?> รายการ
                </span>

                <button type="button" id="btn-toggle-advanced-filter" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm transition shadow-2xs">
                    <?= svg_icon('sliders', 'w-4 h-4 text-indigo-600') ?>
                    <span>ตัวกรองละเอียด</span>
                    <?php if ($hasAdvancedFilters): ?>
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    <?php endif; ?>
                    <span id="advanced-chevron" class="transition-transform duration-200 <?= $hasAdvancedFilters ? 'rotate-180' : '' ?>">
                        <?= svg_icon('chevron-down', 'w-3.5 h-3.5 text-slate-400') ?>
                    </span>
                </button>

                <?php if (!$isAll): ?>
                    <a href="<?= url('/admin/tickets') ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-sm border border-rose-200 transition">
                        <?= svg_icon('x', 'w-3.5 h-3.5') ?>
                        <span>ล้างเงื่อนไขทั้งหมด</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Collapsible Advanced Filter Drawer (ตัวกรองละเอียดตามพารามิเตอร์) -->
        <div id="advanced-filter-panel" class="<?= $hasAdvancedFilters ? '' : 'hidden' ?> bg-white rounded-2xl border border-slate-200 p-5 shadow-xs transition-all duration-200">
            <div class="flex items-center justify-between mb-3.5 pb-2.5 border-b border-slate-100">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                    <?= svg_icon('sliders', 'w-4 h-4 text-indigo-600') ?>
                    <span>ค้นหาและกรองแบบเจาะจง (Server-Side Filter)</span>
                </h3>
                <button type="button" id="btn-close-advanced-filter" class="text-xs text-slate-500 hover:text-slate-800 font-semibold px-2 py-1 rounded-lg hover:bg-slate-100 transition">
                    ย่อเก็บ &times;
                </button>
            </div>
            <form action="<?= url('/admin/tickets') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 text-sm">
                <!-- Keyword -->
                <div>
                    <label class="block font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">ค้นหาคำสำคัญ</label>
                    <input type="text" name="keyword" value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>" placeholder="ค้นชื่อ, อาคาร, ปัญหา..." 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">สถานะใบงาน</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- ทุกสถานะ --</option>
                        <option value="pending_approval" <?= ($filters['status'] ?? '') === 'pending_approval' ? 'selected' : '' ?>>รออนุมัติ</option>
                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>อนุมัติแล้ว</option>
                        <option value="assigned" <?= ($filters['status'] ?? '') === 'assigned' ? 'selected' : '' ?>>จ่ายงานแล้ว</option>
                        <option value="en_route" <?= ($filters['status'] ?? '') === 'en_route' ? 'selected' : '' ?>>กำลังเดินทาง</option>
                        <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>กำลังดำเนินการซ่อม</option>
                        <option value="waiting_parts" <?= ($filters['status'] ?? '') === 'waiting_parts' ? 'selected' : '' ?>>รออะไหล่</option>
                        <option value="resolved" <?= ($filters['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>ซ่อมเสร็จสิ้น (รอปิดงาน)</option>
                        <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>ปิดงานสมบูรณ์</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>ยกเลิก</option>
                    </select>
                </div>

                <!-- SLA Filter -->
                <div>
                    <label class="block font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">สถานะ SLA</label>
                    <select name="sla_filter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- ทุกรายการ --</option>
                        <option value="overdue" <?= ($filters['sla_filter'] ?? '') === 'overdue' ? 'selected' : '' ?>>เกินกำหนดเวลา (Overdue)</option>
                        <option value="near_due" <?= ($filters['sla_filter'] ?? '') === 'near_due' ? 'selected' : '' ?>>ใกล้ครบกำหนดเวลา (ภายใน 4 ชม.)</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label class="block font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">ความเร่งด่วน</label>
                    <select name="priority" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- ทุกระดับ --</option>
                        <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>ด่วนที่สุด</option>
                        <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>สูง</option>
                        <option value="normal" <?= ($filters['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>ปกติ</option>
                        <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>ต่ำ</option>
                    </select>
                </div>

                <!-- Technician Filter -->
                <div>
                    <label class="block font-bold text-slate-700 text-xs uppercase tracking-wider mb-1.5">ช่างเทคนิค</label>
                    <select name="technician_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- ทุกคน --</option>
                        <option value="unassigned" <?= ($filters['technician_id'] ?? '') === 'unassigned' ? 'selected' : '' ?>>[ยังไม่ได้จ่ายงาน]</option>
                        <?php foreach ($technicians as $tech): ?>
                            <option value="<?= $tech['id'] ?>" <?= ($filters['technician_id'] ?? '') == $tech['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tech['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base transition flex items-center justify-center gap-1.5 shadow-sm">
                        <?= svg_icon('search', 'w-4 h-4') ?>
                        <span>ค้นหา</span>
                    </button>
                    <a href="<?= url('/admin/tickets') ?>" class="py-2.5 px-4 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-base transition text-center flex items-center justify-center">
                        ล้าง
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Master Ticket Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left admin-ticket-table min-w-[1150px]">
                <thead class="bg-slate-50/90 border-b border-slate-200 text-xs md:text-sm font-extrabold uppercase tracking-wider text-slate-700">
                    <tr>
                        <th class="px-5 py-4 whitespace-nowrap min-w-[75px]">รหัส</th>
                        <th class="px-5 py-4 min-w-[280px]">หัวข้อปัญหา &amp; สถานที่</th>
                        <th class="px-5 py-4 whitespace-nowrap min-w-[160px]">ผู้แจ้ง</th>
                        <th class="px-5 py-4 whitespace-nowrap min-w-[130px] text-center">ระดับความเร่งด่วน</th>
                        <th class="px-5 py-4 whitespace-nowrap min-w-[160px]">กำหนด SLA</th>
                        <th class="px-5 py-4 whitespace-nowrap min-w-[180px]">สถานะ</th>
                        <th class="px-5 py-4 whitespace-nowrap min-w-[210px]">ช่างผู้รับผิดชอบ</th>
                        <th class="px-5 py-4 whitespace-nowrap min-w-[150px] text-right">การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-base">
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="8" class="p-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-3">
                                    <?= svg_icon('inbox', 'w-6 h-6') ?>
                                </div>
                                <p class="text-base font-semibold text-slate-700">ไม่พบรายการตามเงื่อนไขที่เลือก</p>
                                <p class="text-sm text-slate-500 mt-1">ลองเปลี่ยนตัวกรองค้นหา หรือกดปุ่ม "ล้าง" เพื่อดูรายการทั้งหมด</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <!-- Real-time Filter Empty State Feedback -->
                        <tr id="admin-empty-filter-row" class="hidden">
                            <td colspan="8" class="p-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-3">
                                    <?= svg_icon('search', 'w-6 h-6') ?>
                                </div>
                                <p class="text-base font-bold text-slate-800">ไม่พบใบงานที่ตรงกับคำค้นหาในหน้านี้</p>
                                <p class="text-sm text-slate-500 mt-1 mb-4">ลองเปลี่ยนคำค้นหา หรือกดปุ่มด้านล่างเพื่อล้างการค้นหา</p>
                                <button type="button" onclick="clearAdminSearch()" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-600 text-white font-bold text-sm shadow-sm hover:bg-indigo-700 transition">
                                    <?= svg_icon('refresh', 'w-4 h-4') ?> ล้างการค้นหาด่วน
                                </button>
                            </td>
                        </tr>
                        <?php foreach ($tickets as $t): 
                            $statusEnum = \App\Enums\TicketStatus::tryFrom($t['status']);
                            $priorityEnum = \App\Enums\TicketPriority::tryFrom($t['priority']);
                            $isOverdue = !empty($t['is_overdue']);
                        ?>
                        <tr class="admin-ticket-row hover:bg-slate-50/80 transition group">
                            <td class="px-5 py-4 font-mono font-extrabold text-indigo-700 text-sm whitespace-nowrap">
                                #<?= $t['id'] ?>
                            </td>
                            <td class="px-5 py-4 min-w-[280px] max-w-md">
                                <a href="<?= url('/tickets/' . $t['id']) ?>" class="font-bold text-slate-900 hover:text-indigo-600 transition block text-[15px] leading-snug line-clamp-1">
                                    <?= htmlspecialchars($t['title']) ?>
                                </a>
                                <div class="text-slate-600 font-medium text-xs md:text-sm mt-1.5 flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1 text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md text-xs font-semibold whitespace-nowrap">
                                        <?= svg_icon('map-pin', 'w-3 h-3 text-slate-500 inline shrink-0') ?>
                                        <?= htmlspecialchars($t['location']) ?>
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-slate-600 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-md text-xs font-medium whitespace-nowrap">
                                        <?= svg_icon('tag', 'w-3 h-3 text-slate-400 inline shrink-0') ?>
                                        <?= htmlspecialchars($t['category_name']) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap min-w-[160px]">
                                <div class="inline-flex items-center gap-2.5 whitespace-nowrap">
                                    <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold text-sm flex items-center justify-center border border-slate-200 shrink-0">
                                        <?= mb_substr($t['user_name'] ?? 'ผ', 0, 1, 'UTF-8') ?>
                                    </span>
                                    <div class="text-left">
                                        <span class="font-bold text-slate-900 text-sm block whitespace-nowrap"><?= htmlspecialchars($t['user_name']) ?></span>
                                        <span class="text-xs text-slate-500 font-medium block whitespace-nowrap"><?= htmlspecialchars($t['user_email'] ?? 'ผู้แจ้งเรื่อง') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full font-bold text-xs md:text-sm whitespace-nowrap <?= $priorityEnum ? $priorityEnum->badgeClass() : '' ?>">
                                    <?php if ($priorityEnum && $priorityEnum->value === 'urgent'): ?>
                                        <?= svg_icon('alert-triangle', 'w-3.5 h-3.5 shrink-0 text-rose-600') ?>
                                    <?php endif; ?>
                                    <span><?= $priorityEnum ? $priorityEnum->label() : $t['priority'] ?></span>
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap min-w-[160px]">
                                <?php if ($t['sla_due_at']): ?>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold font-mono <?= $isOverdue ? 'text-rose-700 font-bold' : 'text-slate-800' ?>">
                                            <?= date('d/m/Y H:i', strtotime($t['sla_due_at'])) ?>
                                        </span>
                                        <?php if ($isOverdue): ?>
                                            <span class="inline-flex items-center gap-1 text-xs bg-rose-100 text-rose-800 border border-rose-200 px-2 py-0.5 rounded-full font-bold w-fit mt-0.5 shadow-2xs whitespace-nowrap">
                                                <?= svg_icon('alert-triangle', 'w-3 h-3 text-rose-600 inline shrink-0') ?>
                                                <span>เกินเวลา SLA</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-500 font-medium">ตามกำหนดเวลา</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-400 font-medium">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap min-w-[180px]">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs md:text-sm font-bold whitespace-nowrap <?= $statusEnum ? $statusEnum->badgeClass() : 'bg-slate-100 text-slate-800' ?>">
                                    <?= svg_icon($statusEnum ? $statusEnum->iconName() : 'tag', 'w-3.5 h-3.5 shrink-0') ?>
                                    <span><?= $statusEnum ? $statusEnum->label() : $t['status'] ?></span>
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap min-w-[210px]">
                                <?php if (!empty($t['technician_name'])): ?>
                                    <div class="inline-flex items-center gap-2.5 whitespace-nowrap">
                                        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center justify-center border border-indigo-200 shrink-0">
                                            <?= mb_substr($t['technician_name'], 0, 1, 'UTF-8') ?>
                                        </span>
                                        <div class="text-left">
                                            <span class="font-bold text-slate-900 text-sm block whitespace-nowrap"><?= htmlspecialchars($t['technician_name']) ?></span>
                                            <span class="text-xs text-slate-500 font-medium block whitespace-nowrap">ช่างผู้รับผิดชอบ</span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs md:text-sm font-bold bg-amber-50 text-amber-900 border border-amber-300 whitespace-nowrap shadow-2xs">
                                        <?= svg_icon('alert-triangle', 'w-3.5 h-3.5 text-amber-600 shrink-0') ?>
                                        <span>ยังไม่ได้จ่ายงาน</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap space-x-1.5 min-w-[150px]">
                                <?php if ($t['status'] === 'pending_approval'): ?>
                                    <form action="<?= url('/admin/tickets/' . $t['id'] . '/approve') ?>" method="POST" class="inline">
                                        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs md:text-sm transition inline-flex items-center gap-1 shadow-xs">
                                            <?= svg_icon('check-circle', 'w-3.5 h-3.5') ?>
                                            <span>อนุมัติ</span>
                                        </button>
                                    </form>
                                    <button onclick="openRejectModal(<?= $t['id'] ?>)" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-xs md:text-sm transition inline-flex items-center gap-1 border border-rose-200">
                                        <?= svg_icon('x-circle', 'w-3.5 h-3.5') ?>
                                        <span>ปฏิเสธ</span>
                                    </button>
                                <?php endif; ?>

                                <?php if (in_array($t['status'], ['pending_approval', 'approved', 'assigned'], true)): ?>
                                    <button type="button" 
                                            onclick="openAssignModal(<?= $t['id'] ?>, '<?= htmlspecialchars(addslashes($t['title'])) ?>', '<?= $t['technician_id'] ?? '' ?>', '<?= $t['priority'] ?>')" 
                                            class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs md:text-sm transition inline-flex items-center gap-1 border border-indigo-200">
                                        <?= svg_icon('wrench', 'w-3.5 h-3.5') ?>
                                        <span>จ่ายงาน</span>
                                    </button>
                                <?php endif; ?>

                                <a href="<?= url('/tickets/' . $t['id']) ?>" class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs md:text-sm transition inline-flex items-center gap-1">
                                    <span>ดูรายละเอียด</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Assign Technician & Set Priority -->
<div id="assign-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-base">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <?= svg_icon('wrench', 'w-5 h-5 text-indigo-600') ?>
                <span>จ่ายงาน &amp; กำหนดระดับความเร่งด่วน</span>
            </h3>
            <button onclick="document.getElementById('assign-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg text-xl leading-none">&times;</button>
        </div>

        <form id="assign-form" action="" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">

            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">ใบงานที่เลือก:</label>
                <p id="modal-ticket-title" class="p-3 bg-slate-50 rounded-xl text-slate-800 font-semibold text-base border border-slate-200"></p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">เลือกช่างเทคนิคตามความเชี่ยวชาญ <span class="text-rose-500">*</span></label>
                <select name="technician_id" id="modal-tech-select" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- เลือกช่างเทคนิค --</option>
                    <?php foreach ($technicians as $tech): ?>
                        <option value="<?= $tech['id'] ?>"><?= htmlspecialchars($tech['name']) ?><?= !empty($tech['email']) ? ' (' . htmlspecialchars($tech['email']) . ')' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">ระดับความเร่งด่วน (กำหนด SLA อัตโนมัติ)</label>
                <select name="priority" id="modal-priority-select" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="urgent">ด่วนที่สุด - SLA 4 ชม.</option>
                    <option value="high">สูง - SLA 8 ชม.</option>
                    <option value="normal">ปกติ - SLA 24 ชม.</option>
                    <option value="low">ต่ำ - SLA 48 ชม.</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">โน้ตแจ้งช่าง / กำชับการทำงาน</label>
                <textarea name="note" rows="3" placeholder="ระบุคำแนะนำเพิ่มเติมให้ช่าง เช่น เตรียมบันไดสูงไปด้วย, ให้ติดต่อคุณสมศรีที่หน้างาน..." 
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('assign-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">ยกเลิก</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md transition flex items-center gap-1.5">
                    <?= svg_icon('check-circle', 'w-4 h-4') ?>
                    <span>ยืนยันจ่ายงาน &amp; ส่งเมล</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reject Ticket -->
<div id="reject-admin-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-base">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-rose-700 flex items-center gap-2">
                <?= svg_icon('x-circle', 'w-5 h-5 text-rose-600') ?>
                <span>ปฏิเสธคำขอใบแจ้งซ่อม</span>
            </h3>
            <button onclick="document.getElementById('reject-admin-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg text-xl leading-none">&times;</button>
        </div>
        <form id="reject-admin-form" action="" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">ระบุเหตุผลในการปฏิเสธ <span class="text-rose-500">*</span></label>
                <textarea name="reason" rows="3" required placeholder="เช่น อยู่นอกเหนือขอบเขตการรับผิดชอบของอาคาร, ข้อมูลไม่เพียงพอ..." 
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-rose-500"></textarea>
            </div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('reject-admin-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">ยกเลิก</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold shadow-md transition flex items-center gap-1.5">
                    <?= svg_icon('x-circle', 'w-4 h-4') ?>
                    <span>ยืนยันปฏิเสธ</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAssignModal(ticketId, title, currentTechId, priority) {
        document.getElementById('assign-form').action = `<?= url('/admin/tickets') ?>/${ticketId}/assign`;
        document.getElementById('modal-ticket-title').textContent = `#${ticketId} - ${title}`;
        document.getElementById('modal-tech-select').value = currentTechId || '';
        document.getElementById('modal-priority-select').value = priority || 'normal';
        document.getElementById('assign-modal').classList.remove('hidden');
    }

    function openRejectModal(ticketId) {
        document.getElementById('reject-admin-form').action = `<?= url('/admin/tickets') ?>/${ticketId}/reject`;
        document.getElementById('reject-admin-modal').classList.remove('hidden');
    }

    // Admin Instant Search & Advanced Filter Interactions
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('admin-table-search');
        const clearBtn = document.getElementById('admin-clear-search-btn');
        const visibleCountEl = document.getElementById('admin-visible-count');
        const emptyRow = document.getElementById('admin-empty-filter-row');
        const rows = document.querySelectorAll('.admin-ticket-row');
        const toggleBtn = document.getElementById('btn-toggle-advanced-filter');
        const closeBtn = document.getElementById('btn-close-advanced-filter');
        const advancedPanel = document.getElementById('advanced-filter-panel');
        const chevron = document.getElementById('advanced-chevron');

        // Toggle Advanced Filter Panel
        if (toggleBtn && advancedPanel) {
            toggleBtn.addEventListener('click', function() {
                const isHidden = advancedPanel.classList.toggle('hidden');
                if (chevron) {
                    chevron.classList.toggle('rotate-180', !isHidden);
                }
            });
        }

        if (closeBtn && advancedPanel) {
            closeBtn.addEventListener('click', function() {
                advancedPanel.classList.add('hidden');
                if (chevron) {
                    chevron.classList.remove('rotate-180');
                }
            });
        }

        // Live Table Search Filtering
        function applyAdminTableSearch() {
            if (!rows.length) return;
            const query = (searchInput ? searchInput.value : '').trim().toLowerCase();

            if (clearBtn) {
                clearBtn.classList.toggle('hidden', query === '');
            }

            let matchCount = 0;
            rows.forEach(row => {
                if (query === '') {
                    row.classList.remove('hidden');
                    matchCount++;
                } else {
                    const text = row.textContent.toLowerCase();
                    const matches = text.includes(query);
                    row.classList.toggle('hidden', !matches);
                    if (matches) matchCount++;
                }
            });

            if (visibleCountEl) {
                visibleCountEl.textContent = matchCount;
            }

            if (emptyRow) {
                emptyRow.classList.toggle('hidden', matchCount > 0);
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyAdminTableSearch);
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                }
                applyAdminTableSearch();
            });
        }

        window.clearAdminSearch = function() {
            if (searchInput) searchInput.value = '';
            applyAdminTableSearch();
        };
    });
</script>
