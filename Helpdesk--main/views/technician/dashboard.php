<div class="space-y-8">
    <!-- Header banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-gradient-to-r from-amber-600 via-amber-700 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl shadow-amber-950/20">
        <div>
            <span class="inline-block px-3.5 py-1.5 bg-white/10 rounded-full text-sm font-bold text-amber-100 mb-2">พื้นที่ปฏิบัติงานของช่างเทคนิค</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">สวัสดีครับ, ช่าง<?= htmlspecialchars($user['name']) ?></h1>
            <p class="text-amber-100 text-base mt-1">คิวงานซ่อมบำรุงและงานระบบตามลำดับความเร่งด่วน พร้อมระบบแจ้งเตือนกำหนดเวลา SLA</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-5 py-2.5 rounded-xl bg-white/10 text-white font-extrabold text-base border border-white/20 shadow-xs">
                งานที่ต้องปฏิบัติ: <?= $counts['assigned'] + $counts['en_route'] + $counts['in_progress'] + $counts['waiting_parts'] ?> งาน
            </span>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
            <p class="text-sm font-bold uppercase tracking-wider text-slate-600">งานทั้งหมด</p>
            <h3 class="text-3xl font-extrabold text-slate-900 mt-1"><?= $counts['total'] ?></h3>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
            <p class="text-sm font-bold uppercase tracking-wider text-indigo-700">เดินทาง/กำลังซ่อม</p>
            <h3 class="text-3xl font-extrabold text-indigo-600 mt-1"><?= $counts['en_route'] + $counts['in_progress'] ?></h3>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
            <p class="text-sm font-bold uppercase tracking-wider text-orange-700">รออะไหล่</p>
            <h3 class="text-3xl font-extrabold text-orange-600 mt-1"><?= $counts['waiting_parts'] ?></h3>
        </div>
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
            <p class="text-sm font-bold uppercase tracking-wider text-rose-700">เกินกำหนดเวลา SLA</p>
            <h3 class="text-3xl font-extrabold text-rose-600 mt-1"><?= $counts['overdue'] ?></h3>
        </div>
    </div>

    <!-- Assigned Jobs Queue with Quick Filter Capsules -->
    <div class="space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">คิวงานซ่อมของฉัน</h2>
                <p class="text-sm text-slate-600 font-medium">จัดลำดับความเร่งด่วนตามสถานะ SLA และการแจ้งเตือน</p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <!-- Instant Search Bar -->
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <?= svg_icon('search', 'w-4 h-4 text-slate-500') ?>
                    </div>
                    <input type="text" id="tech-job-search" placeholder="ค้นหาชื่องาน, อาคาร, รหัส #..." 
                           class="w-full pl-10 pr-8 py-2 bg-white border border-slate-300 rounded-full text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 shadow-2xs">
                    <button type="button" id="tech-clear-search-btn" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 hidden text-base">
                        &times;
                    </button>
                </div>

                <span class="text-sm font-bold px-3.5 py-2 rounded-full bg-white text-indigo-700 border border-slate-200 shadow-2xs whitespace-nowrap text-center">
                    แสดง <span id="tech-visible-count" class="text-indigo-600"><?= count($jobs) ?></span> จาก <?= count($jobs) ?> งาน
                </span>
            </div>
        </div>

        <!-- Operational Capsule Filter Track (แคปซูลกรองงาน) -->
        <div class="capsule-track flex items-center gap-2 p-1.5 bg-slate-100/90 border border-slate-200 rounded-full overflow-x-auto w-fit max-w-full shadow-inner" id="tech-filter-chips">
            <button type="button" data-filter="all" class="filter-chip active inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm min-h-[42px] transition-all duration-150 whitespace-nowrap cursor-pointer border bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-500/25 ring-2 ring-indigo-500/20">
                <?= svg_icon('filter', 'w-4 h-4') ?>
                <span>ทั้งหมด</span>
                <span class="chip-count inline-flex items-center justify-center min-w-[1.4rem] h-5 px-1.5 rounded-full text-xs font-extrabold bg-white text-indigo-700 shadow-2xs"><?= count($jobs) ?></span>
            </button>
            <button type="button" data-filter="active" class="filter-chip inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm min-h-[42px] transition-all duration-150 whitespace-nowrap cursor-pointer border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 shadow-2xs">
                <?= svg_icon('wrench', 'w-4 h-4 text-indigo-500') ?>
                <span>กำลังดำเนินการ</span>
                <span class="chip-count inline-flex items-center justify-center min-w-[1.4rem] h-5 px-1.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700"><?= $counts['assigned'] + $counts['en_route'] + $counts['in_progress'] ?></span>
            </button>
            <button type="button" data-filter="waiting_parts" class="filter-chip inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm min-h-[42px] transition-all duration-150 whitespace-nowrap cursor-pointer border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 shadow-2xs">
                <?= svg_icon('package', 'w-4 h-4 text-orange-500') ?>
                <span>รออะไหล่</span>
                <span class="chip-count inline-flex items-center justify-center min-w-[1.4rem] h-5 px-1.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700"><?= $counts['waiting_parts'] ?></span>
            </button>
            <button type="button" data-filter="overdue" class="filter-chip inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm min-h-[42px] transition-all duration-150 whitespace-nowrap cursor-pointer border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 hover:border-slate-400 shadow-2xs">
                <?= svg_icon('alert-triangle', 'w-4 h-4 text-rose-500') ?>
                <span>เกินเวลา SLA</span>
                <span class="chip-count inline-flex items-center justify-center min-w-[1.4rem] h-5 px-1.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700"><?= $counts['overdue'] ?></span>
            </button>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="bg-white rounded-2xl border border-slate-200 p-14 text-center shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                    <?= svg_icon('check-circle', 'w-9 h-9 text-emerald-600') ?>
                </div>
                <h4 class="text-base font-bold text-slate-800">ไม่มีงานซ่อมค้างในขณะนี้</h4>
                <p class="text-sm text-slate-600 mt-1">คุณได้จัดการงานซ่อมทั้งหมดเรียบร้อยแล้ว หรือยังไม่มีการมอบหมายงานใหม่</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="tech-jobs-grid">
                <?php foreach ($jobs as $job): 
                    $statusEnum = \App\Enums\TicketStatus::tryFrom($job['status']);
                    $priorityEnum = \App\Enums\TicketPriority::tryFrom($job['priority']);
                    $isOverdue = !empty($job['is_overdue']);
                    $isActive = in_array($job['status'], ['assigned', 'en_route', 'in_progress'], true);
                ?>
                <div class="tech-job-card bg-white rounded-2xl border <?= $isOverdue ? 'border-rose-400 ring-2 ring-rose-500/20' : 'border-slate-200' ?> shadow-xs p-6 flex flex-col justify-between hover:shadow-md transition"
                     data-status="<?= htmlspecialchars($job['status']) ?>"
                     data-is-active="<?= $isActive ? '1' : '0' ?>"
                     data-is-overdue="<?= $isOverdue ? '1' : '0' ?>">
                    <div>
                        <!-- Header with Badges & SLA -->
                        <div class="flex items-center justify-between gap-2 mb-3.5">
                            <span class="text-sm font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800">
                                #<?= $job['id'] ?>
                            </span>
                            <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                <?php if ($isOverdue): ?>
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-rose-600 text-white animate-pulse">
                                         <?= svg_icon('alert-triangle', 'w-3.5 h-3.5 text-white') ?>
                                         <span>เกินกำหนด SLA</span>
                                     </span>
                                <?php endif; ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?= $priorityEnum ? $priorityEnum->badgeClass() : '' ?>">
                                    <?= $priorityEnum ? $priorityEnum->label() : $job['priority'] ?>
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?= $statusEnum ? $statusEnum->badgeClass() : '' ?>">
                                    <?= $statusEnum ? $statusEnum->label() : $job['status'] ?>
                                </span>
                            </div>
                        </div>

                        <!-- Title & Location -->
                        <h3 class="font-bold text-slate-900 text-lg mb-1.5 line-clamp-2">
                            <a href="<?= url('/technician/jobs/' . $job['id']) ?>" class="hover:text-amber-600 transition">
                                <?= htmlspecialchars($job['title']) ?>
                            </a>
                        </h3>
                        <p class="text-base font-bold text-slate-800 mb-1 flex items-center gap-1.5">
                            <?= svg_icon('map-pin', 'w-4 h-4 text-indigo-600 flex-shrink-0') ?>
                            <span><?= htmlspecialchars($job['location']) ?></span>
                        </p>
                        <p class="text-sm text-slate-600 mb-3 flex items-center gap-1">
                            <?= svg_icon('tag', 'w-4 h-4 text-slate-400') ?>
                            <span><?= htmlspecialchars($job['category_name']) ?></span>
                        </p>

                        <!-- Description Snippet -->
                        <p class="text-base text-slate-800 line-clamp-3 mb-4 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <?= htmlspecialchars($job['description']) ?>
                        </p>

                        <!-- User Info & SLA -->
                        <div class="space-y-1.5 text-sm text-slate-600 py-2.5 border-t border-slate-100">
                            <div class="flex justify-between">
                                <span>ผู้แจ้ง: <strong class="text-slate-900"><?= htmlspecialchars($job['user_name']) ?></strong></span>
                                <span><?= date('d/m H:i', strtotime($job['created_at'])) ?></span>
                            </div>
                            <?php if ($job['sla_due_at']): ?>
                            <div class="flex justify-between <?= $isOverdue ? 'text-rose-700 font-bold' : 'text-slate-700' ?>">
                                <span>กำหนด SLA:</span>
                                <span><?= date('d/m/Y H:i', strtotime($job['sla_due_at'])) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Action Bar -->
                    <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-between gap-2 flex-wrap">
                        <a href="<?= url('/technician/jobs/' . $job['id']) ?>" class="text-sm font-bold text-slate-700 hover:text-slate-900 px-3 py-2 rounded-xl hover:bg-slate-100 transition">
                            ดูรายละเอียด
                        </a>

                        <?php if ($job['status'] === 'assigned'): ?>
                            <form action="<?= url('/technician/jobs/' . $job['id'] . '/en-route') ?>" method="POST">
                                <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm shadow-xs transition">
                                    <?= svg_icon('truck', 'w-4 h-4') ?>
                                    <span>กำลังเดินทาง</span>
                                </button>
                            </form>
                        <?php elseif ($job['status'] === 'en_route'): ?>
                            <form action="<?= url('/technician/jobs/' . $job['id'] . '/start') ?>" method="POST">
                                <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-xs transition">
                                    <?= svg_icon('wrench', 'w-4 h-4') ?>
                                    <span>เริ่มซ่อมแซม</span>
                                </button>
                            </form>
                        <?php elseif (in_array($job['status'], ['in_progress', 'waiting_parts'], true)): ?>
                            <a href="<?= url('/technician/jobs/' . $job['id']) ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm shadow-xs transition">
                                <?= svg_icon('check-circle', 'w-4 h-4') ?>
                                <span>จัดการ/ปิดงาน</span>
                            </a>
                        <?php else: ?>
                            <span class="text-sm text-slate-500 font-semibold italic">ส่งมอบแล้ว</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Empty filter alert for tech -->
            <div id="tech-empty-filter" class="hidden bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-xs">
                <?= svg_icon('search', 'w-10 h-10 text-slate-400 mx-auto mb-3') ?>
                <p class="font-bold text-slate-800 text-lg">ไม่พบงานที่ตรงกับตัวกรองหรือคำค้นหานี้</p>
                <p class="text-sm text-slate-500 mt-1 mb-4">ลองพิมพ์คำค้นหาใหม่ หรือคลิกปุ่มด้านล่างเพื่อล้างการค้นหา</p>
                <button type="button" onclick="resetTechFilter()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-indigo-600 text-white font-bold text-sm shadow-sm hover:bg-indigo-700 transition">
                    <?= svg_icon('filter', 'w-4 h-4') ?> ล้างตัวกรองทั้งหมด
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chips = document.querySelectorAll('#tech-filter-chips .filter-chip');
    const cards = document.querySelectorAll('#tech-jobs-grid .tech-job-card');
    const searchInput = document.getElementById('tech-job-search');
    const clearBtn = document.getElementById('tech-clear-search-btn');
    const visibleCountEl = document.getElementById('tech-visible-count');
    const emptyState = document.getElementById('tech-empty-filter');

    if (!cards.length) return;

    function applyTechFilters() {
        const activeChip = document.querySelector('#tech-filter-chips .filter-chip.active');
        const filter = activeChip ? activeChip.getAttribute('data-filter') : 'all';
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();

        if (clearBtn) {
            clearBtn.classList.toggle('hidden', query === '');
        }

        let visibleCount = 0;

        cards.forEach(card => {
            const status = card.getAttribute('data-status');
            const isActive = card.getAttribute('data-is-active') === '1';
            const isOverdue = card.getAttribute('data-is-overdue') === '1';

            let matchesStatus = false;
            if (filter === 'all') matchesStatus = true;
            else if (filter === 'active') matchesStatus = isActive;
            else if (filter === 'waiting_parts') matchesStatus = (status === 'waiting_parts');
            else if (filter === 'overdue') matchesStatus = isOverdue;

            let matchesSearch = true;
            if (query !== '') {
                const cardText = card.textContent.toLowerCase();
                matchesSearch = cardText.includes(query);
            }

            if (matchesStatus && matchesSearch) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        if (visibleCountEl) visibleCountEl.textContent = visibleCount;
        if (emptyState) {
            emptyState.classList.toggle('hidden', visibleCount > 0);
        }
    }

    const activeClasses = ['bg-indigo-600', 'text-white', 'border-indigo-600', 'shadow-md', 'shadow-indigo-500/25', 'ring-2', 'ring-indigo-500/20'];
    const inactiveClasses = ['bg-white', 'text-slate-700', 'border-slate-300', 'hover:bg-slate-50', 'hover:border-slate-400', 'shadow-2xs'];

    function setActiveTechChip(activeChip) {
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
            setActiveTechChip(this);
            applyTechFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyTechFilters);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            applyTechFilters();
            searchInput.focus();
        });
    }

    window.resetTechFilter = function() {
        if (searchInput) searchInput.value = '';
        const allChip = document.querySelector('#tech-filter-chips [data-filter="all"]');
        setActiveTechChip(allChip);
        applyTechFilters();
    };
});
</script>

