<?php
$statusEnum = \App\Enums\TicketStatus::tryFrom($ticket['status']);
$priorityEnum = \App\Enums\TicketPriority::tryFrom($ticket['priority']);
$currentStep = $statusEnum ? $statusEnum->stepIndex() : 1;

$steps = [
    1 => ['key' => 'pending_approval', 'label' => 'รออนุมัติ', 'desc' => 'รอหัวหน้าช่างตรวจ'],
    2 => ['key' => 'assigned', 'label' => 'มอบหมายช่าง', 'desc' => 'จ่ายงานให้ช่าง'],
    3 => ['key' => 'en_route', 'label' => 'กำลังเดินทาง', 'desc' => 'ช่างมุ่งหน้าสู่หน้างาน'],
    4 => ['key' => 'in_progress', 'label' => 'กำลังซ่อม', 'desc' => 'ตรวจเช็คหรือซ่อมแซม'],
    5 => ['key' => 'resolved', 'label' => 'ซ่อมเสร็จแล้ว', 'desc' => 'รอผู้แจ้งตรวจรับ'],
    6 => ['key' => 'closed', 'label' => 'ปิดงานสมบูรณ์', 'desc' => 'ประเมินความพึงพอใจ'],
];
?>

<div class="space-y-8 max-w-6xl mx-auto">
    <!-- Top Bar: Back & Ticket Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="<?= url('/dashboard') ?>" class="p-3 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 transition shadow-xs" title="กลับแดชบอร์ด">
                <?= svg_icon('arrow-left', 'w-6 h-6 text-slate-700') ?>
            </a>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="text-sm font-bold px-3 py-1 rounded-full bg-slate-200 text-slate-900">
                        ใบงานที่ #<?= $ticket['id'] ?>
                    </span>
                    <span class="text-sm font-bold px-3 py-1 rounded-full <?= $priorityEnum ? $priorityEnum->badgeClass() : '' ?>">
                        ความเร่งด่วน: <?= $priorityEnum ? $priorityEnum->label() : $ticket['priority'] ?>
                    </span>
                    <span class="text-sm text-slate-800 font-semibold inline-flex items-center gap-1.5">
                        <?= svg_icon('map-pin', 'w-4 h-4 text-indigo-600') ?> <?= htmlspecialchars($ticket['location']) ?>
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1.5"><?= htmlspecialchars($ticket['title']) ?></h1>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-base font-bold shadow-xs <?= $statusEnum ? $statusEnum->badgeClass() : '' ?>">
                <span class="w-2.5 h-2.5 rounded-full <?= $statusEnum ? $statusEnum->dotColor() : '' ?>"></span>
                <?= $statusEnum ? $statusEnum->label() : $ticket['status'] ?>
            </span>

            <?php if ($statusEnum && $statusEnum->canCancel() && $user['role'] === 'user'): ?>
                <button onclick="document.getElementById('cancel-modal').classList.remove('hidden')" 
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-bold transition">
                    <?= svg_icon('x-circle', 'w-4 h-4 text-rose-600') ?>
                    <span>ยกเลิกคำขอ</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 1. Progress Stepper: Visual Timeline -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
        <h3 class="text-base font-bold text-slate-800 mb-6 flex items-center gap-2">
            <?= svg_icon('clock', 'w-5 h-5 text-indigo-600') ?>
            <span>ไทม์ไลน์ขั้นตอนการดำเนินงานซ่อม</span>
        </h3>
        
        <?php if ($ticket['status'] === 'cancelled'): ?>
            <div class="p-5 bg-rose-50 border border-rose-200 rounded-xl text-center text-rose-800 font-bold text-base flex items-center justify-center gap-2">
                <?= svg_icon('x-circle', 'w-5 h-5 text-rose-600') ?>
                <span>ใบแจ้งซ่อมนี้ถูกยกเลิกแล้ว</span>
            </div>
        <?php else: ?>
            <div class="relative flex items-center justify-between w-full">
                <!-- Connecting Line -->
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1.5 bg-slate-200 z-0"></div>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1.5 bg-indigo-600 transition-all duration-500 z-0" 
                     style="width: <?= (($currentStep - 1) / (count($steps) - 1)) * 100 ?>%;"></div>

                <?php foreach ($steps as $idx => $st): 
                    $isCompleted = $idx < $currentStep;
                    $isCurrent = $idx === $currentStep;
                ?>
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center font-extrabold text-sm sm:text-base transition-all duration-300 shadow-sm
                            <?= $isCompleted ? 'bg-indigo-600 text-white' : ($isCurrent ? 'bg-indigo-600 text-white ring-4 ring-indigo-200 scale-110' : 'bg-white border-2 border-slate-300 text-slate-500') ?>">
                            <?= $isCompleted ? '✓' : $idx ?>
                        </div>
                        <div class="text-center mt-2.5">
                            <p class="text-xs sm:text-base font-bold <?= $isCurrent ? 'text-indigo-700 font-extrabold' : ($isCompleted ? 'text-slate-900' : 'text-slate-500') ?>">
                                <?= $st['label'] ?>
                            </p>
                            <p class="text-xs sm:text-sm text-slate-600 hidden md:block mt-0.5"><?= $st['desc'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ACTION PANEL: For Resolved Status (Confirm or Reject) -->
    <?php if ($ticket['status'] === 'resolved'): ?>
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 sm:p-8 text-white shadow-lg flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="space-y-1.5 text-center sm:text-left">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-sm font-bold">
                    <?= svg_icon('sparkles', 'w-4 h-4 text-emerald-200') ?>
                    <span>ช่างซ่อมแซมเสร็จสิ้นแล้ว</span>
                </div>
                <h3 class="text-xl sm:text-2xl font-extrabold">กรุณาตรวจรับงานและประเมินความพึงพอใจ</h3>
                <p class="text-emerald-100 text-base leading-relaxed">หากการซ่อมเรียบร้อยตามที่ต้องการ กรุณากดยืนยันปิดงานพร้อมให้คะแนน หรือกดปฏิเสธหากปัญหายังไม่หาย</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <button onclick="document.getElementById('reject-modal').classList.remove('hidden')" 
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-base border border-white/30 transition">
                    <?= svg_icon('x-circle', 'w-5 h-5') ?>
                    <span>ปฏิเสธ (ปัญหายังไม่หาย)</span>
                </button>
                <button onclick="document.getElementById('rating-modal').classList.remove('hidden')" 
                        class="inline-flex items-center gap-2 px-7 py-3 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 font-extrabold text-base shadow-md transition transform active:scale-95">
                    <?= svg_icon('star', 'w-5 h-5 text-amber-500') ?>
                    <span>ยืนยันปิดงานและให้คะแนน</span>
                </button>
            </div>
        </div>
    <?php elseif ($ticket['status'] === 'closed' && $rating): ?>
        <div class="bg-slate-100 rounded-2xl p-6 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <?= svg_icon('star', 'w-7 h-7 text-amber-500') ?>
                </div>
                <div>
                    <h4 class="font-bold text-base text-slate-900">งานซ่อมนี้ปิดสมบูรณ์แล้ว</h4>
                    <p class="text-sm text-slate-700">คะแนนความพึงพอใจของคุณ: <span class="text-amber-500 font-extrabold"><?= str_repeat('★', $rating['score']) . str_repeat('☆', 5 - $rating['score']) ?></span> (<?= $rating['score'] ?> จาก 5 ดาว)</p>
                    <?php if (!empty($rating['feedback'])): ?>
                        <p class="text-sm text-slate-800 italic mt-1 font-medium">"<?= htmlspecialchars($rating['feedback']) ?>"</p>
                    <?php endif; ?>
                </div>
            </div>
            <span class="text-sm font-semibold px-3.5 py-1.5 bg-white rounded-xl text-slate-700 border border-slate-200 shadow-xs">
                ปิดงานเมื่อ: <?= date('d/m/Y H:i', strtotime($ticket['closed_at'] ?? $rating['created_at'])) ?>
            </span>
        </div>
    <?php endif; ?>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left 2 Cols: Details, Repair Report & Discussion -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Detail Box -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <?= svg_icon('ticket', 'w-5 h-5 text-indigo-600') ?>
                        <span>รายละเอียดใบแจ้งซ่อม</span>
                    </h3>
                    <span class="text-sm text-slate-700">สถานที่: <strong class="text-slate-900"><?= htmlspecialchars($ticket['location']) ?></strong></span>
                </div>
                <div class="text-base text-slate-800 leading-relaxed font-normal">
                    <?= nl2br(htmlspecialchars($ticket['description'])) ?>
                </div>
            </div>

            <!-- Repair Completion Report (If Resolved or Closed) -->
            <?php if ($repairLog): ?>
            <div class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-6 sm:p-8 shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-emerald-200/80 pb-4">
                    <h3 class="text-base sm:text-lg font-extrabold text-emerald-950 flex items-center gap-2">
                        <?= svg_icon('clipboard', 'w-6 h-6 text-emerald-700') ?>
                        <span>รายงานผลการซ่อมแซมจากช่างเทคนิค</span>
                    </h3>
                    <span class="text-sm font-bold px-3 py-1 bg-emerald-100 text-emerald-900 rounded-xl">
                        ใช้เวลาซ่อมจริง: <?= $repairLog['actual_hours'] ?> ชั่วโมง
                    </span>
                </div>

                <div class="space-y-4 text-base">
                    <div>
                        <span class="font-bold text-emerald-950 block mb-1.5 flex items-center gap-1.5">
                            <?= svg_icon('search', 'w-4 h-4 text-emerald-700') ?>
                            <span>สาเหตุแท้จริงของปัญหา:</span>
                        </span>
                        <p class="text-slate-800 bg-white/90 p-3.5 rounded-xl border border-emerald-200 leading-relaxed"><?= nl2br(htmlspecialchars($repairLog['root_cause'])) ?></p>
                    </div>

                    <div>
                        <span class="font-bold text-emerald-950 block mb-1.5 flex items-center gap-1.5">
                            <?= svg_icon('wrench', 'w-4 h-4 text-emerald-700') ?>
                            <span>วิธีการแก้ไขที่ได้ดำเนินการ:</span>
                        </span>
                        <p class="text-slate-800 bg-white/90 p-3.5 rounded-xl border border-emerald-200 leading-relaxed"><?= nl2br(htmlspecialchars($repairLog['solution_note'])) ?></p>
                    </div>

                    <?php if (!empty($repairLog['proof_image_path'])): ?>
                    <div>
                        <span class="font-bold text-emerald-950 block mb-1.5 flex items-center gap-1.5">
                            <?= svg_icon('camera', 'w-4 h-4 text-emerald-700') ?>
                            <span>รูปถ่ายหลักฐานหลังการซ่อมเสร็จ:</span>
                        </span>
                        <a href="<?= asset('/' . ltrim($repairLog['proof_image_path'], '/')) ?>" target="_blank" class="inline-block group">
                            <img src="<?= asset('/' . ltrim($repairLog['proof_image_path'], '/')) ?>" alt="Proof" class="max-h-64 rounded-xl border border-emerald-200 shadow-xs group-hover:opacity-90 transition">
                            <span class="text-sm text-emerald-800 underline font-semibold mt-1.5 inline-flex items-center gap-1">ดูรูปภาพขนาดเต็ม <?= svg_icon('arrow-right', 'w-4 h-4 inline') ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Spare parts used in this repair -->
                <?php if (!empty($partsUsed)): ?>
                <div class="pt-4 border-t border-emerald-200/80">
                    <span class="font-bold text-emerald-950 block mb-2.5 text-base flex items-center gap-1.5">
                        <?= svg_icon('package', 'w-5 h-5 text-emerald-700') ?>
                        <span>อะไหล่หรือวัสดุที่ใช้ในงานนี้:</span>
                    </span>
                    <div class="space-y-2">
                        <?php foreach ($partsUsed as $pu): ?>
                            <div class="flex items-center justify-between text-base bg-white/90 px-4 py-2.5 rounded-xl border border-emerald-200">
                                <span class="font-bold text-slate-800"><?= htmlspecialchars($pu['part_name']) ?> (<?= htmlspecialchars($pu['part_code']) ?>)</span>
                                <span class="text-slate-700 font-bold"><?= $pu['quantity'] ?> ชิ้น [<?= $pu['status'] === 'approved' ? 'อนุมัติแล้ว' : ($pu['status'] === 'rejected' ? 'ไม่อนุมัติ' : 'รออนุมัติ') ?>]</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Chat & Discussion Thread -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <?= svg_icon('mail', 'w-5 h-5 text-indigo-600') ?>
                        <span>การสนทนาและข้อความตอบกลับ (<?= count($comments) ?> ข้อความ)</span>
                    </h3>
                </div>

                <div class="p-6 sm:p-8 space-y-6">
                    <?php if (empty($comments)): ?>
                        <div class="text-center py-6 text-slate-500">
                            <?= svg_icon('mail', 'w-8 h-8 text-slate-400 mx-auto mb-2') ?>
                            <p class="text-base">ยังไม่มีข้อความสนทนาในใบงานนี้</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $c): 
                            $isMe = (int)$c['user_id'] === (int)$user['id'];
                        ?>
                            <div class="flex flex-col <?= $isMe ? 'items-end' : 'items-start' ?>">
                                <div class="flex items-center gap-2 mb-1.5 text-sm text-slate-600">
                                    <span class="font-bold text-slate-800"><?= htmlspecialchars($c['user_name']) ?></span>
                                    <span>&bull; <?= date('d/m H:i', strtotime($c['created_at'])) ?></span>
                                </div>
                                <div class="max-w-xl rounded-2xl px-5 py-3.5 text-base shadow-xs <?= $isMe ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-slate-100 text-slate-900 rounded-tl-none border border-slate-200' ?>">
                                    <p class="whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($c['body']) ?></p>
                                    <?php if (!empty($c['image_path'])): ?>
                                        <div class="mt-3">
                                            <a href="<?= asset('/' . ltrim($c['image_path'], '/')) ?>" target="_blank" class="block group">
                                                <img src="<?= asset('/' . ltrim($c['image_path'], '/')) ?>" alt="Attached image" class="max-h-56 rounded-xl object-cover border border-white/20 group-hover:opacity-90 transition">
                                                <span class="text-sm underline mt-1.5 inline-block font-semibold <?= $isMe ? 'text-indigo-100' : 'text-slate-700' ?>">ดูภาพขนาดเต็ม ↗</span>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($ticket['status'] !== 'closed' && $ticket['status'] !== 'cancelled'): ?>
                        <form action="<?= url('/tickets/' . $ticket['id'] . '/comments') ?>" method="POST" enctype="multipart/form-data" class="mt-6 pt-5 border-t border-slate-100 space-y-4">
                            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                            <div>
                                <textarea name="body" rows="3" placeholder="พิมพ์ข้อความตอบกลับ หรือสอบถามข้อมูลเพิ่มเติมกับช่าง..." 
                                          class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 hover:text-indigo-600 cursor-pointer">
                                    <input type="file" name="image" class="hidden" accept="image/*" onchange="this.nextElementSibling.nextElementSibling.textContent = this.files[0].name">
                                    <?= svg_icon('camera', 'w-5 h-5 text-slate-500') ?>
                                    <span>แนบรูปภาพ</span>
                                    <span class="text-xs text-indigo-600 font-bold ml-1"></span>
                                </label>
                                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-base font-bold shadow-md shadow-indigo-200 transition">
                                    ส่งข้อความ
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Col: Ticket Details, SLA & Audit Logs -->
        <div class="space-y-6">
            <!-- Summary Info Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4 text-base">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <?= svg_icon('building', 'w-5 h-5 text-indigo-600') ?>
                    <span>ข้อมูลใบงานแจ้งซ่อม</span>
                </h3>
                <dl class="divide-y divide-slate-100">
                    <div class="py-3 flex justify-between gap-2">
                        <dt class="text-slate-600 font-medium">สถานที่:</dt>
                        <dd class="font-bold text-slate-900 text-right"><?= htmlspecialchars($ticket['location']) ?></dd>
                    </div>
                    <div class="py-3 flex justify-between gap-2">
                        <dt class="text-slate-600 font-medium">หมวดหมู่:</dt>
                        <dd class="font-bold text-slate-900"><?= htmlspecialchars($ticket['category_name']) ?></dd>
                    </div>
                    <div class="py-3 flex justify-between gap-2">
                        <dt class="text-slate-600 font-medium">ช่างเทคนิคผู้ดูแล:</dt>
                        <dd class="font-bold text-slate-900">
                            <?= $ticket['technician_name'] ? htmlspecialchars($ticket['technician_name']) : '<span class="text-amber-700 font-bold">รอการจ่ายงาน</span>' ?>
                        </dd>
                    </div>
                    <?php if ($ticket['sla_due_at']): ?>
                    <div class="py-3 flex justify-between gap-2">
                        <dt class="text-slate-600 font-medium">กำหนดเวลาเสร็จสิ้น:</dt>
                        <dd class="font-bold <?= $ticket['is_overdue'] ? 'text-rose-700 animate-pulse' : 'text-slate-900' ?>">
                            <?= date('d/m/Y H:i', strtotime($ticket['sla_due_at'])) ?>
                            <?= $ticket['is_overdue'] ? ' (เกินกำหนด)' : '' ?>
                        </dd>
                    </div>
                    <?php endif; ?>
                    <div class="py-3 flex justify-between gap-2">
                        <dt class="text-slate-600 font-medium">วันที่แจ้ง:</dt>
                        <dd class="text-slate-800 font-semibold"><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Audit Trail / Status Logs -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <?= svg_icon('clock', 'w-5 h-5 text-indigo-600') ?>
                    <span>ประวัติการดำเนินงาน</span>
                </h3>
                <div class="space-y-4">
                    <?php foreach ($logs as $log): 
                        $toSt = \App\Enums\TicketStatus::tryFrom($log['to_status']);
                    ?>
                        <div class="flex items-start gap-3 border-l-2 border-indigo-500 pl-3.5 py-1">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-base text-slate-900"><?= $toSt ? $toSt->label() : $log['to_status'] ?></span>
                                    <span class="text-slate-500 text-xs font-semibold"><?= date('d/m H:i', strtotime($log['created_at'])) ?></span>
                                </div>
                                <p class="text-slate-600 text-sm mt-0.5">โดย: <strong class="text-slate-800"><?= htmlspecialchars($log['changed_by_name']) ?></strong></p>
                                <?php if (!empty($log['note'])): ?>
                                    <p class="text-slate-800 bg-slate-50 p-2 rounded-xl mt-1.5 text-sm border border-slate-200"><?= htmlspecialchars($log['note']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Cancel Ticket -->
<div id="cancel-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-rose-600 flex items-center gap-2">
                <?= svg_icon('x-circle', 'w-6 h-6 text-rose-600') ?>
                <span>ยกเลิกใบแจ้งซ่อม</span>
            </h3>
            <button onclick="document.getElementById('cancel-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form action="<?= url('/tickets/' . $ticket['id'] . '/cancel') ?>" method="POST" class="space-y-4 text-base">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <p class="text-slate-700 leading-relaxed">คุณสามารถยกเลิกใบแจ้งซ่อมนี้ได้ เนื่องจากช่างยังไม่ได้เริ่มลงมือปฏิบัติงานซ่อม</p>
            <div>
                <label class="block font-bold text-slate-800 mb-2">เหตุผลในการขอยกเลิก <span class="text-rose-500">*</span></label>
                <textarea name="reason" rows="3" required placeholder="เช่น สามารถแก้ไขเองได้แล้ว, แจ้งข้อมูลผิด..." 
                          class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:ring-2 focus:ring-rose-500"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-3">
                <button type="button" onclick="document.getElementById('cancel-modal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-100 transition">
                    ไม่ยกเลิก
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold shadow-md transition">
                    ยืนยันการยกเลิก
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Confirm & Rate -->
<div id="rating-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden transition-opacity duration-200">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-5 border border-slate-100 transform transition-all">
        <div class="flex justify-between items-start border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <?= svg_icon('star', 'w-6 h-6 text-amber-500') ?>
                    <span>ประเมินความพึงพอใจและปิดงาน</span>
                </h3>
                <p class="text-sm text-slate-600 mt-1">ใบแจ้งซ่อม #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['title']) ?></p>
            </div>
            <button type="button" onclick="closeRatingModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition text-2xl font-bold">&times;</button>
        </div>

        <form action="<?= url('/tickets/' . $ticket['id'] . '/confirm') ?>" method="POST" id="rating-form" class="space-y-5">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            
            <!-- Interactive Star Rating UI -->
            <div>
                <label class="block text-base font-bold text-slate-800 mb-2">
                    ระดับความพึงพอใจต่อการให้บริการ <span class="text-rose-500">*</span>
                </label>
                
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5 flex flex-col items-center justify-center gap-3">
                    <!-- Hidden radio inputs -->
                    <div class="hidden">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <input type="radio" id="rating-score-<?= $s ?>" name="score" value="<?= $s ?>" <?= $s === 5 ? 'checked' : '' ?>>
                        <?php endfor; ?>
                    </div>

                    <!-- Interactive Star Icons -->
                    <div class="flex items-center gap-3 text-4xl select-none" id="star-container">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <button type="button" data-score="<?= $s ?>" 
                                    class="star-btn transition-transform duration-150 transform hover:scale-125 focus:outline-hidden cursor-pointer"
                                    title="<?= $s ?> ดาว">
                                <span class="star-icon text-amber-400">★</span>
                            </button>
                        <?php endfor; ?>
                    </div>

                    <!-- Dynamic Score Text Label -->
                    <div id="star-desc" class="text-sm font-bold text-emerald-800 bg-emerald-100 px-4 py-1.5 rounded-full border border-emerald-300 mt-1">
                        5 ดาว - ยอดเยี่ยมมาก (ประทับใจมาก)
                    </div>
                </div>
            </div>

            <!-- Feedback Field -->
            <div>
                <label class="block text-base font-bold text-slate-800 mb-2">
                    ข้อเสนอแนะหรือความคิดเห็นเพิ่มเติม
                </label>
                <textarea name="feedback" id="rating-feedback" rows="3" 
                          placeholder="ช่างให้บริการดี ตรงต่อเวลา อุปกรณ์ทำงานได้สมบูรณ์..." 
                          class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"></textarea>
                <p class="text-sm text-slate-500 mt-1.5 font-medium">ความคิดเห็นของคุณจะช่วยปรับปรุงและพัฒนาคุณภาพการบริการให้ดียิ่งขึ้น</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 gap-3">
                <button type="button" onclick="closeRatingModal()" 
                        class="px-5 py-3 rounded-xl border border-slate-200 text-slate-700 text-base font-bold hover:bg-slate-100 transition">
                    ไว้ประเมินภายหลัง
                </button>
                <button type="submit" 
                        class="px-7 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-base font-bold shadow-md shadow-emerald-600/20 transition transform active:scale-95 flex items-center gap-2">
                    <?= svg_icon('check', 'w-5 h-5') ?>
                    <span>บันทึกและปิดงาน</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reject Resolution -->
<div id="reject-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-rose-600 flex items-center gap-2">
                <?= svg_icon('x-circle', 'w-6 h-6 text-rose-600') ?>
                <span>ปฏิเสธผลการแก้ไข</span>
            </h3>
            <button onclick="document.getElementById('reject-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form action="<?= url('/tickets/' . $ticket['id'] . '/reject') ?>" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <p class="text-base text-slate-700 leading-relaxed">
                หากอุปกรณ์ยังพบปัญหาหรือใช้งานได้ไม่สมบูรณ์ ใบงานจะถูกส่งกลับไปให้ช่างเทคนิคเพื่อเข้าตรวจสอบและแก้ไขต่อทันที
            </p>
            <div>
                <label class="block text-base font-bold text-slate-800 mb-2">ระบุเหตุผลที่ปัญหายังไม่หาย <span class="text-rose-500">*</span></label>
                <textarea name="reason" rows="3" required placeholder="ทดสอบเปิดแล้วยังพบอาการเดิม เช่น น้ำยังหยด..." 
                          class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:ring-2 focus:ring-rose-500"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-3">
                <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-base font-bold hover:bg-slate-100">
                    ยกเลิก
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-base font-bold shadow-md shadow-rose-600/20">
                    ส่งกลับไปแก้ไขใหม่
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript: Auto-open modal on ?action=rate & interactive star rating -->
<script>
function openRatingModal() {
    const modal = document.getElementById('rating-modal');
    if (modal) {
        modal.classList.remove('hidden');
        const feedback = document.getElementById('rating-feedback');
        if (feedback) feedback.focus();
    }
}

function closeRatingModal() {
    const modal = document.getElementById('rating-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const isResolved = <?= ($ticket['status'] === 'resolved') ? 'true' : 'false' ?>;
    const isClosed = <?= ($ticket['status'] === 'closed') ? 'true' : 'false' ?>;
    
    // 1. Check URL parameters & hash for trigger
    const urlParams = new URLSearchParams(window.location.search);
    const hasRateParam = urlParams.get('action') === 'rate' || urlParams.get('rate') === '1' || window.location.hash === '#rating-modal' || window.location.hash === '#rate';

    if (hasRateParam) {
        if (isResolved) {
            // Auto open rating modal when requester clicks email button!
            openRatingModal();
        } else if (isClosed) {
            // Friendly notice if already evaluated
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-6 right-6 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-xl border border-slate-700 z-50 text-xs flex items-center gap-2 animate-bounce';
            toast.innerHTML = '<span>⭐</span> <span>ใบแจ้งซ่อมนี้ได้รับการประเมินความพึงพอใจและปิดงานสมบูรณ์แล้ว ขอขอบคุณครับ</span>';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 6000);
        }
    }

    // 2. Interactive Star Rating System
    const starLabels = {
        1: '😞 1 ดาว - ต้องปรับปรุงอย่างยิ่ง',
        2: '🙁 2 ดาว - พอใช้ (ควรปรับปรุง)',
        3: '😐 3 ดาว - ปานกลาง (มาตรฐาน)',
        4: '😊 4 ดาว - ดีมาก (น่าพึงพอใจ)',
        5: '🌟 5 ดาว - ยอดเยี่ยมมาก (ประทับใจมาก)'
    };

    let selectedScore = 5;
    const starButtons = document.querySelectorAll('.star-btn');
    const starDesc = document.getElementById('star-desc');

    function updateStarVisuals(score) {
        starButtons.forEach(btn => {
            const btnScore = parseInt(btn.getAttribute('data-score'), 10);
            const icon = btn.querySelector('.star-icon');
            if (btnScore <= score) {
                icon.textContent = '★';
                icon.className = 'star-icon text-amber-400';
            } else {
                icon.textContent = '☆';
                icon.className = 'star-icon text-slate-300';
            }
        });

        if (starDesc && starLabels[score]) {
            starDesc.textContent = starLabels[score];
            if (score >= 4) {
                starDesc.className = 'text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 mt-1';
            } else if (score === 3) {
                starDesc.className = 'text-xs font-bold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200 mt-1';
            } else {
                starDesc.className = 'text-xs font-bold text-rose-700 bg-rose-50 px-3 py-1 rounded-full border border-rose-200 mt-1';
            }
        }
    }

    starButtons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            const hoverScore = parseInt(this.getAttribute('data-score'), 10);
            updateStarVisuals(hoverScore);
        });

        btn.addEventListener('click', function() {
            selectedScore = parseInt(this.getAttribute('data-score'), 10);
            const radio = document.getElementById('rating-score-' + selectedScore);
            if (radio) radio.checked = true;
            updateStarVisuals(selectedScore);
        });
    });

    const starContainer = document.getElementById('star-container');
    if (starContainer) {
        starContainer.addEventListener('mouseleave', function() {
            updateStarVisuals(selectedScore);
        });
    }

    // Initialize with 5 stars
    updateStarVisuals(5);

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRatingModal();
            const rejectModal = document.getElementById('reject-modal');
            if (rejectModal) rejectModal.classList.add('hidden');
        }
    });
});
</script>
