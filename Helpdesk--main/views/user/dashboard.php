<div class="space-y-8">
    <!-- Header banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-gradient-to-r from-indigo-700 via-indigo-800 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl shadow-indigo-950/20">
        <div>
            <span class="inline-block px-3.5 py-1.5 bg-white/10 rounded-full text-sm font-bold text-indigo-100 mb-2">ศูนย์บริการและแจ้งซ่อมสำหรับผู้ใช้งาน</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">สวัสดีครับ, คุณ<?= htmlspecialchars($user['name']) ?></h1>
            <p class="text-indigo-100 text-base mt-1">ระบบแจ้งซ่อมและงานบริการอาคาร ติดตามขั้นตอนงานซ่อมแซมได้แบบเรียลไทม์</p>
        </div>
        <div>
            <a href="<?= url('/tickets/create') ?>" class="inline-flex items-center gap-2.5 px-6 py-3.5 rounded-xl bg-white text-indigo-700 hover:bg-indigo-50 font-extrabold text-base shadow-md transition transform active:scale-95">
                <?= svg_icon('plus-circle', 'w-6 h-6 text-indigo-600') ?>
                <span>เปิดใบแจ้งซ่อมใหม่</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <!-- Total -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-slate-600">ใบแจ้งซ่อมทั้งหมด</p>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-1"><?= $counts['total'] ?></h3>
                <p class="text-sm text-slate-600 mt-1 font-medium">ประวัติคำขอทั้งหมดของคุณ</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-xs">
                <?= svg_icon('ticket', 'w-8 h-8 text-indigo-600') ?>
            </div>
        </div>

        <!-- Active Jobs -->
        <?php $activeCount = ($counts['pending_approval'] ?? 0) + ($counts['approved'] ?? 0) + ($counts['assigned'] ?? 0) + ($counts['en_route'] ?? 0) + ($counts['in_progress'] ?? 0) + ($counts['waiting_parts'] ?? 0); ?>
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-indigo-700">กำลังดำเนินการ</p>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-indigo-600 mt-1"><?= $activeCount ?></h3>
                <p class="text-sm text-slate-600 mt-1 font-medium">รวมขั้นตอนเดินทาง ตรวจสอบ และซ่อมแซม</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-xs">
                <?= svg_icon('wrench', 'w-8 h-8 text-indigo-600') ?>
            </div>
        </div>

        <!-- Resolved -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-wider text-emerald-700">ซ่อมเสร็จแล้ว</p>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-emerald-600 mt-1"><?= $counts['resolved'] ?></h3>
                <p class="text-sm text-emerald-700 mt-1 font-medium">รอให้คุณตรวจรับและประเมินผลงาน</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-xs">
                <?= svg_icon('check-circle', 'w-8 h-8 text-emerald-600') ?>
            </div>
        </div>
    </div>

    <!-- Ticket History Table with Quick Filter Chips -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <!-- Table Header & Controls -->
        <div class="p-6 border-b border-slate-200 bg-slate-50/50 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                        <?= svg_icon('clipboard', 'w-5 h-5 text-indigo-600') ?>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">รายการแจ้งซ่อมของฉัน</h2>
                        <p class="text-sm text-slate-600 font-medium">ตรวจสอบสถานะการซ่อม สถานที่ และกดยืนยันปิดงาน</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <!-- Quick Instant Search Input -->
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <?= svg_icon('search', 'w-4 h-4 text-slate-500') ?>
                        </div>
                        <input type="text" id="user-ticket-search" placeholder="พิมพ์ค้นหาด่วน (ชื่อ, สถานที่, รหัส)..." 
                               class="w-full pl-10 pr-8 py-2 bg-white border border-slate-300 rounded-full text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 shadow-2xs">
                        <button type="button" id="clear-search-btn" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 hidden text-base">
                            &times;
                        </button>
                    </div>

                    <span id="filtered-badge" class="text-sm font-bold px-3.5 py-2 rounded-full bg-white text-indigo-700 border border-slate-200 shadow-2xs whitespace-nowrap text-center">
                        แสดง <span id="visible-count" class="text-indigo-600"><?= count($tickets) ?></span> จาก <?= count($tickets) ?> รายการ
                    </span>
                </div>
            </div>

            <!-- Capsule Filter Track (แคปซูลกรองงาน) -->
            <div class="capsule-track flex items-center gap-2 p-1.5 bg-slate-100/90 border border-slate-200 rounded-full overflow-x-auto w-fit max-w-full shadow-inner" id="ticket-filter-chips">
                <button type="button" data-filter="all" class="filter-chip active inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm min-h-[42px] transition-all duration-150 whitespace-nowrap cursor-pointer border bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-500/25 ring-2 ring-indigo-500/20">
                    <?= svg_icon('filter', 'w-4 h-4') ?>
                    <span>ทั้งหมด</span>
                    <span class="chip-count inline-flex items-center justify-center min-w-[1.4rem] h-5 px-1.5 rounded-full text-xs font-extrabold bg-white text-indigo-700 shadow-2xs" id="count-chip-all"><?= count($tickets) ?></span>
                </button>
                <button type="button" data-filter="in_progress_group" class="filter-chip inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm min-h-[42px] transition-all duration-150 whitespace-nowrap cursor-pointer border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 shadow-2xs">
                    <?= svg_icon('wrench', 'w-4 h-4 text-indigo-500') ?>
                    <span>กำลังดำเนินการ</span>
                    <span class="chip-count inline-flex items-center justify-center min-w-[1.4rem] h-5 px-1.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700" id="count-chip-inprogress"><?= $activeCount ?></span>
                </button>
                <button type="button" data-filter="waiting_parts" class="filter-chip inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm min-h-[42px] transition-all duration-150 whitespace-nowrap cursor-pointer border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 shadow-2xs">
                    <?= svg_icon('package', 'w-4 h-4 text-orange-500') ?>
                    <span>รออะไหล่</span>
                    <span class="chip-count inline-flex items-center justify-center min-w-[1.4rem] h-5 px-1.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700" id="count-chip-parts"><?= $counts['waiting_parts'] ?></span>
                </button>
                <button type="button" data-filter="resolved" class="filter-chip inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm min-h-[42px] transition-all duration-150 whitespace-nowrap cursor-pointer border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 shadow-2xs">
                    <?= svg_icon('check-circle', 'w-4 h-4 text-emerald-500') ?>
                    <span>ซ่อมเสร็จแล้ว (รอตรวจรับ)</span>
                    <span class="chip-count inline-flex items-center justify-center min-w-[1.4rem] h-5 px-1.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700" id="count-chip-resolved"><?= $counts['resolved'] ?></span>
                </button>
                <button type="button" data-filter="closed" class="filter-chip inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm min-h-[42px] transition-all duration-150 whitespace-nowrap cursor-pointer border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 shadow-2xs">
                    <?= svg_icon('check', 'w-4 h-4 text-slate-500') ?>
                    <span>ปิดงานแล้ว</span>
                    <span class="chip-count inline-flex items-center justify-center min-w-[1.4rem] h-5 px-1.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700" id="count-chip-closed"><?= $counts['closed'] ?></span>
                </button>
                <button type="button" data-filter="pending_approval" class="filter-chip inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm min-h-[42px] transition-all duration-150 whitespace-nowrap cursor-pointer border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 shadow-2xs">
                    <?= svg_icon('clock', 'w-4 h-4 text-amber-500') ?>
                    <span>รออนุมัติ</span>
                    <span class="chip-count inline-flex items-center justify-center min-w-[1.4rem] h-5 px-1.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700" id="count-chip-pending"><?= $counts['pending_approval'] ?></span>
                </button>
            </div>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="p-14 text-center">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-4">
                    <?= svg_icon('inbox', 'w-9 h-9 text-indigo-500') ?>
                </div>
                <h4 class="text-base font-bold text-slate-800">ยังไม่มีรายการแจ้งซ่อม</h4>
                <p class="text-sm text-slate-600 mt-1 mb-5">หากพบปัญหาอาคาร สถานที่ หรือระบบไอที สามารถกดแจ้งซ่อมได้ทันที</p>
                <a href="<?= url('/tickets/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-base font-semibold hover:bg-indigo-700 transition shadow-md shadow-indigo-200">
                    <?= svg_icon('plus-circle', 'w-5 h-5') ?>
                    <span>แจ้งซ่อมครั้งแรก</span>
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-base" id="user-tickets-table">
                    <thead class="bg-slate-50 border-b border-slate-200 text-sm font-bold text-slate-700 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-4">รหัส</th>
                            <th class="px-5 py-4">หัวข้อปัญหา</th>
                            <th class="px-5 py-4">สถานที่</th>
                            <th class="px-5 py-4">หมวดหมู่</th>
                            <th class="px-5 py-4">ความเร่งด่วน</th>
                            <th class="px-5 py-4">สถานะปัจจุบัน</th>
                            <th class="px-5 py-4">ช่างผู้ดูแล</th>
                            <th class="px-5 py-4 text-right">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-base" id="tickets-tbody">
                        <?php foreach ($tickets as $t): 
                            $statusEnum = \App\Enums\TicketStatus::tryFrom($t['status']);
                            $priorityEnum = \App\Enums\TicketPriority::tryFrom($t['priority']);
                            $isWaitingParts = ($t['status'] === 'waiting_parts');
                            $isInProgressGroup = in_array($t['status'], ['assigned', 'approved', 'en_route', 'in_progress', 'waiting_parts'], true);
                        ?>
                        <tr class="ticket-row hover:bg-slate-50/80 transition group" 
                            data-status="<?= htmlspecialchars($t['status']) ?>"
                            data-in-progress="<?= $isInProgressGroup ? '1' : '0' ?>">
                            <td class="px-5 py-4 font-bold text-slate-900">#<?= $t['id'] ?></td>
                            <td class="px-5 py-4">
                                <a href="<?= url('/tickets/' . $t['id']) ?>" class="font-bold text-slate-900 group-hover:text-indigo-600 transition block max-w-sm truncate text-base">
                                    <?= htmlspecialchars($t['title']) ?>
                                </a>
                            </td>
                            <td class="px-5 py-4 text-slate-800 font-medium">
                                <span class="inline-flex items-center gap-1.5">
                                    <?= svg_icon('map-pin', 'w-4 h-4 text-indigo-600 flex-shrink-0') ?>
                                    <span><?= htmlspecialchars($t['location']) ?></span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-700 font-medium"><?= htmlspecialchars($t['category_name']) ?></td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold <?= $priorityEnum ? $priorityEnum->badgeClass() : '' ?>">
                                    <?= $priorityEnum ? $priorityEnum->label() : $t['priority'] ?>
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-bold shadow-xs <?= $statusEnum ? $statusEnum->badgeClass() : '' ?>">
                                    <span class="w-2 h-2 rounded-full <?= $statusEnum ? $statusEnum->dotColor() : 'bg-slate-400' ?>"></span>
                                    <?= $statusEnum ? $statusEnum->label() : $t['status'] ?>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-700 font-medium">
                                <?= $t['technician_name'] ? htmlspecialchars($t['technician_name']) : '<span class="text-amber-700 font-bold">รอจ่ายงาน</span>' ?>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a href="<?= url('/tickets/' . $t['id']) ?>" class="inline-flex items-center gap-1.5 font-bold text-indigo-700 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition border border-indigo-100">
                                    <span>ติดตาม</span>
                                    <?= svg_icon('arrow-right', 'w-4 h-4') ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <!-- Empty state when filtered has 0 results -->
                        <tr id="empty-filter-row" class="hidden">
                            <td colspan="8" class="p-12 text-center text-slate-600 text-base">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <?= svg_icon('search', 'w-6 h-6 text-slate-500') ?>
                                </div>
                                <p class="font-bold text-slate-800">ไม่พบใบแจ้งซ่อมตามเงื่อนไขที่เลือก</p>
                                <p class="text-sm text-slate-500 mt-1">ลองเปลี่ยนคำค้นหา หรือกดปุ่มด้านล่างเพื่อแสดงรายการทั้งหมด</p>
                                <button type="button" onclick="resetUserFilter()" class="mt-3 px-4 py-2 rounded-full bg-indigo-50 text-indigo-700 font-bold text-sm hover:bg-indigo-100 transition inline-flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                    <?= svg_icon('filter', 'w-4 h-4') ?>
                                    <span>ล้างตัวกรองและแสดงทั้งหมด</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Interactive Client-side Filter & Instant Search Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chips = document.querySelectorAll('#ticket-filter-chips .filter-chip');
    const rows = document.querySelectorAll('#tickets-tbody .ticket-row');
    const visibleCountSpan = document.getElementById('visible-count');
    const emptyRow = document.getElementById('empty-filter-row');
    const searchInput = document.getElementById('user-ticket-search');
    const clearSearchBtn = document.getElementById('clear-search-btn');

    if (!rows.length) return;

    function applyFilters() {
        const activeChip = document.querySelector('#ticket-filter-chips .filter-chip.active');
        const filter = activeChip ? activeChip.getAttribute('data-filter') : 'all';
        const searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let visibleCount = 0;

        if (clearSearchBtn) {
            clearSearchBtn.classList.toggle('hidden', searchVal.length === 0);
        }

        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            const isInProgress = row.getAttribute('data-in-progress') === '1';
            const textContent = row.textContent.toLowerCase();

            let statusMatch = false;
            if (filter === 'all') {
                statusMatch = true;
            } else if (filter === 'in_progress_group') {
                statusMatch = isInProgress;
            } else {
                statusMatch = (status === filter);
            }

            const searchMatch = !searchVal || textContent.includes(searchVal);

            if (statusMatch && searchMatch) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        if (visibleCountSpan) {
            visibleCountSpan.textContent = visibleCount;
        }

        if (emptyRow) {
            emptyRow.classList.toggle('hidden', visibleCount > 0);
        }
    }

    const activeClasses = ['bg-indigo-600', 'text-white', 'border-indigo-600', 'shadow-md', 'shadow-indigo-500/25', 'ring-2', 'ring-indigo-500/20'];
    const inactiveClasses = ['bg-white', 'text-slate-700', 'border-slate-300', 'hover:bg-slate-50', 'hover:border-slate-400', 'shadow-2xs'];

    function setActiveChip(activeChip) {
        chips.forEach(c => {
            c.classList.remove('active', ...activeClasses);
            c.classList.add(...inactiveClasses);
            const badge = c.querySelector('.chip-count');
            if (badge) {
                badge.classList.remove('bg-white', 'text-indigo-700');
                badge.classList.add('bg-slate-100', 'text-slate-700');
            }
        });
        if (activeChip) {
            activeChip.classList.add('active', ...activeClasses);
            activeChip.classList.remove(...inactiveClasses);
            const activeBadge = activeChip.querySelector('.chip-count');
            if (activeBadge) {
                activeBadge.classList.remove('bg-slate-100', 'text-slate-700');
                activeBadge.classList.add('bg-white', 'text-indigo-700');
            }
        }
    }

    chips.forEach(chip => {
        chip.addEventListener('click', function() {
            setActiveChip(this);
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            applyFilters();
            searchInput.focus();
        });
    }

    window.resetUserFilter = function() {
        if (searchInput) searchInput.value = '';
        const allChip = document.querySelector('#ticket-filter-chips [data-filter="all"]');
        setActiveChip(allChip);
        applyFilters();
    };
});
</script>

