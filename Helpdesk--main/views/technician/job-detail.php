<?php
$statusEnum = \App\Enums\TicketStatus::tryFrom($ticket['status']);
$priorityEnum = \App\Enums\TicketPriority::tryFrom($ticket['priority']);
$isOverdue = !empty($ticket['is_overdue']);
?>

<div class="space-y-6 max-w-6xl mx-auto">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <a href="<?= url('/technician/dashboard') ?>" class="p-3 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 transition shadow-xs" title="กลับคิวงาน">
                <?= svg_icon('arrow-left', 'w-6 h-6 text-slate-700') ?>
            </a>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="text-sm font-bold px-3 py-1 rounded-full bg-slate-200 text-slate-900">ใบงาน #<?= $ticket['id'] ?></span>
                    <span class="text-sm font-bold px-3 py-1 rounded-full <?= $priorityEnum ? $priorityEnum->badgeClass() : '' ?>">
                        <?= $priorityEnum ? $priorityEnum->label() : $ticket['priority'] ?>
                    </span>
                    <?php if ($isOverdue): ?>
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-rose-600 text-white animate-pulse inline-flex items-center gap-1">
                            <?= svg_icon('alert-triangle', 'w-3.5 h-3.5 text-white') ?>
                            <span>เกินกำหนด SLA</span>
                        </span>
                    <?php endif; ?>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1.5"><?= htmlspecialchars($ticket['title']) ?></h1>
                <p class="text-base font-bold text-indigo-700 mt-1 flex items-center gap-1.5">
                    <?= svg_icon('map-pin', 'w-4 h-4 text-indigo-600 flex-shrink-0') ?>
                    <span>สถานที่: <?= htmlspecialchars($ticket['location']) ?></span>
                </p>
            </div>
        </div>

        <!-- Quick Status Control Panel -->
        <div class="flex items-center gap-2.5 flex-wrap justify-end">
            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full text-sm font-bold shadow-xs <?= $statusEnum ? $statusEnum->badgeClass() : '' ?>">
                <span class="w-2.5 h-2.5 rounded-full <?= $statusEnum ? $statusEnum->dotColor() : '' ?>"></span>
                <?= $statusEnum ? $statusEnum->label() : $ticket['status'] ?>
            </span>

            <?php if ($ticket['status'] === 'assigned'): ?>
                <form action="<?= url('/technician/jobs/' . $ticket['id'] . '/en-route') ?>" method="POST" class="inline">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm shadow-xs transition">
                        <?= svg_icon('truck', 'w-4 h-4') ?>
                        <span>กำลังเดินทาง</span>
                    </button>
                </form>
            <?php endif; ?>

            <?php if (in_array($ticket['status'], ['assigned', 'en_route', 'waiting_parts'], true)): ?>
                <form action="<?= url('/technician/jobs/' . $ticket['id'] . '/start') ?>" method="POST" class="inline">
                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-xs transition">
                        <?= svg_icon('wrench', 'w-4 h-4') ?>
                        <span>เริ่มลงมือซ่อม</span>
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($ticket['status'] === 'in_progress'): ?>
                <button onclick="document.getElementById('part-modal').classList.remove('hidden')" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-xs transition">
                    <?= svg_icon('package', 'w-4 h-4') ?>
                    <span>ขอเบิกอะไหล่</span>
                </button>
                <button onclick="document.getElementById('close-job-modal').classList.remove('hidden')" 
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-md transition transform active:scale-95">
                    <?= svg_icon('check-circle', 'w-4 h-4') ?>
                    <span>บันทึกผล &amp; ส่งมอบงาน</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Ticket Information, Parts & Work Execution -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Detail Breakdown -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <?= svg_icon('ticket', 'w-5 h-5 text-indigo-600') ?>
                        <span>ข้อมูลปัญหาจากผู้แจ้ง</span>
                    </h3>
                    <span class="text-sm text-slate-600">หมวดหมู่: <strong class="text-slate-900"><?= htmlspecialchars($ticket['category_name']) ?></strong></span>
                </div>
                <div class="text-base text-slate-800 leading-relaxed font-normal">
                    <?= nl2br(htmlspecialchars($ticket['description'])) ?>
                </div>
            </div>

            <!-- Spare Parts Requisition Section -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <?= svg_icon('package', 'w-5 h-5 text-orange-600') ?>
                        <span>รายการอะไหล่ที่ขอเบิกใช้งาน (<?= count($partsUsed) ?> รายการ)</span>
                    </h3>
                    <?php if (in_array($ticket['status'], ['in_progress', 'waiting_parts'], true)): ?>
                        <button onclick="document.getElementById('part-modal').classList.remove('hidden')" 
                                class="text-sm px-3.5 py-1.5 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold border border-orange-200 transition">
                            + ขอเบิกเพิ่ม
                        </button>
                    <?php endif; ?>
                </div>

                <div class="p-6">
                    <?php if (empty($partsUsed)): ?>
                        <p class="text-sm text-slate-500 text-center py-3">ยังไม่มีการขอเบิกอะไหล่สำหรับใบงานนี้</p>
                    <?php else: ?>
                        <div class="space-y-2.5">
                            <?php foreach ($partsUsed as $pu): ?>
                                <div class="flex items-center justify-between text-base bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                                    <div>
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($pu['part_name']) ?></div>
                                        <div class="text-slate-600 text-sm font-mono mt-0.5">รหัส: <?= htmlspecialchars($pu['part_code']) ?> | ราคา: <?= number_format($pu['unit_price'], 2) ?> ฿</div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-bold text-slate-900 text-base"><?= $pu['quantity'] ?> ชิ้น</span>
                                        <?php if ($pu['status'] === 'approved'): ?>
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-sm border border-emerald-200">
                                                <?= svg_icon('check-circle', 'w-3.5 h-3.5 text-emerald-700') ?>
                                                อนุมัติแล้ว
                                            </span>
                                        <?php elseif ($pu['status'] === 'rejected'): ?>
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-rose-100 text-rose-800 font-bold text-sm border border-rose-200">
                                                <?= svg_icon('x-circle', 'w-3.5 h-3.5 text-rose-700') ?>
                                                ไม่อนุมัติ
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-sm border border-amber-200">
                                                <?= svg_icon('clock', 'w-3.5 h-3.5 text-amber-700') ?>
                                                รออนุมัติ
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Existing Repair Log (If resolved) -->
            <?php if ($repairLog): ?>
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 sm:p-8 space-y-4">
                <div class="flex items-center justify-between border-b border-emerald-200 pb-3">
                    <span class="font-bold text-emerald-950 text-base flex items-center gap-2">
                        <?= svg_icon('clipboard', 'w-5 h-5 text-emerald-700') ?>
                        <span>บันทึกการซ่อมแซมและส่งมอบงาน</span>
                    </span>
                    <span class="font-bold text-sm px-3 py-1 bg-emerald-100 text-emerald-800 rounded-xl">เวลาซ่อมจริง: <?= $repairLog['actual_hours'] ?> ชม.</span>
                </div>
                <div>
                    <span class="font-bold text-emerald-900 text-base block mb-1">สาเหตุจริง:</span>
                    <p class="text-slate-800 bg-white/90 p-3 rounded-xl border border-emerald-200 leading-relaxed text-base"><?= nl2br(htmlspecialchars($repairLog['root_cause'])) ?></p>
                </div>
                <div>
                    <span class="font-bold text-emerald-900 text-base block mb-1">วิธีแก้ไข:</span>
                    <p class="text-slate-800 bg-white/90 p-3 rounded-xl border border-emerald-200 leading-relaxed text-base"><?= nl2br(htmlspecialchars($repairLog['solution_note'])) ?></p>
                </div>
                <?php if (!empty($repairLog['proof_image_path'])): ?>
                <div>
                    <span class="font-bold text-emerald-900 text-base block mb-1.5">รูปภาพผลงานหลังซ่อม:</span>
                    <a href="<?= asset('/' . ltrim($repairLog['proof_image_path'], '/')) ?>" target="_blank" class="block mt-1">
                        <img src="<?= asset('/' . ltrim($repairLog['proof_image_path'], '/')) ?>" alt="Proof" class="max-h-56 rounded-xl border border-emerald-200">
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Comments Thread -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <?= svg_icon('mail', 'w-5 h-5 text-indigo-600') ?>
                        <span>บันทึกความเห็น &amp; สื่อสารกับผู้แจ้ง (<?= count($comments) ?>)</span>
                    </h3>
                </div>

                <div class="p-6 space-y-5">
                    <?php foreach ($comments as $c): 
                        $isTech = $c['user_role'] === 'technician' || $c['user_role'] === 'admin';
                        $roleEnum = \App\Enums\UserRole::tryFrom($c['user_role']);
                    ?>
                        <div class="flex flex-col <?= $isTech ? 'items-end' : 'items-start' ?>">
                            <div class="flex items-center gap-2 mb-1.5 text-sm text-slate-600">
                                <span class="font-bold text-slate-800"><?= htmlspecialchars($c['user_name']) ?></span>
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-xs font-bold text-slate-700"><?= $roleEnum ? $roleEnum->label() : $c['user_role'] ?></span>
                                <span>&bull; <?= date('d/m H:i', strtotime($c['created_at'])) ?></span>
                            </div>
                            <div class="max-w-xl rounded-2xl px-5 py-3.5 text-base shadow-xs <?= $isTech ? 'bg-amber-600 text-white rounded-tr-none' : 'bg-slate-100 text-slate-900 rounded-tl-none border border-slate-200' ?>">
                                <p class="whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($c['body']) ?></p>
                                <?php if (!empty($c['image_path'])): ?>
                                    <div class="mt-2.5">
                                        <a href="<?= asset('/' . ltrim($c['image_path'], '/')) ?>" target="_blank">
                                            <img src="<?= asset('/' . ltrim($c['image_path'], '/')) ?>" alt="Attached image" class="max-h-56 rounded-xl object-cover">
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($ticket['status'] !== 'closed' && $ticket['status'] !== 'cancelled'): ?>
                        <form action="<?= url('/technician/jobs/' . $ticket['id'] . '/comments') ?>" method="POST" enctype="multipart/form-data" class="mt-5 pt-4 border-t border-slate-100 space-y-4">
                            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                            <textarea name="body" rows="2" placeholder="พิมพ์ข้อความตอบกลับผู้แจ้ง หรือบันทึกโน้ตหน้างาน..." 
                                      class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:ring-2 focus:ring-amber-500"></textarea>
                            <div class="flex justify-between items-center gap-3">
                                <label class="text-sm text-slate-700 font-semibold cursor-pointer inline-flex items-center gap-1.5">
                                    <input type="file" name="image" class="hidden" accept="image/*">
                                    <?= svg_icon('camera', 'w-4 h-4 text-slate-500') ?>
                                    <span>แนบรูปภาพ</span>
                                </label>
                                <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-base font-bold transition shadow-xs">
                                    ส่งข้อความ
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Contact, SLA & Timeline Logs -->
        <div class="space-y-6">
            <!-- Contact Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4 text-base">
                <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <?= svg_icon('user', 'w-5 h-5 text-indigo-600') ?>
                    <span>ข้อมูลผู้แจ้ง &amp; สถานที่</span>
                </h3>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-base">
                        <?= svg_icon('user', 'w-6 h-6 text-indigo-600') ?>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-base"><?= htmlspecialchars($ticket['user_name']) ?></h4>
                        <p class="text-slate-600 text-sm"><?= htmlspecialchars($ticket['user_email']) ?></p>
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 space-y-3">
                    <div class="flex justify-between gap-2">
                        <span class="text-slate-600 font-medium">สถานที่:</span>
                        <span class="text-slate-900 font-bold text-right"><?= htmlspecialchars($ticket['location']) ?></span>
                    </div>
                    <?php if ($ticket['sla_due_at']): ?>
                    <div class="flex justify-between gap-2 <?= $isOverdue ? 'text-rose-700 font-bold' : 'text-slate-700' ?>">
                        <span>กำหนดเวลา SLA:</span>
                        <span><?= date('d/m/Y H:i', strtotime($ticket['sla_due_at'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status Logs -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <?= svg_icon('clock', 'w-5 h-5 text-indigo-600') ?>
                    <span>ประวัติการเปลี่ยนสถานะ</span>
                </h3>
                <div class="space-y-4">
                    <?php foreach ($logs as $log): 
                        $toStatusEnum = \App\Enums\TicketStatus::tryFrom($log['to_status']);
                    ?>
                        <div class="flex items-start gap-3 border-l-2 border-amber-500 pl-3.5 py-1">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-base text-slate-900"><?= $toStatusEnum ? $toStatusEnum->label() : htmlspecialchars($log['to_status']) ?></span>
                                    <span class="text-slate-500 text-xs font-semibold"><?= date('d/m H:i', strtotime($log['created_at'])) ?></span>
                                </div>
                                <p class="text-slate-600 text-sm mt-0.5">โดย: <strong class="text-slate-800"><?= htmlspecialchars($log['changed_by_name']) ?></strong></p>
                                <?php if (!empty($log['note'])): ?>
                                    <p class="text-slate-800 bg-slate-50 p-2 rounded-xl mt-1 text-sm border border-slate-200"><?= htmlspecialchars($log['note']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Spare Part Requisition -->
<div id="part-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-4 text-base">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <?= svg_icon('package', 'w-6 h-6 text-orange-600') ?>
                <span>ขอเบิกอะไหล่ / อุปกรณ์</span>
            </h3>
            <button onclick="document.getElementById('part-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">&times;</button>
        </div>
        <form action="<?= url('/technician/jobs/' . $ticket['id'] . '/request-part') ?>" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <div>
                <label class="block font-bold text-slate-800 mb-1.5">เลือกรายการอะไหล่ <span class="text-rose-500">*</span></label>
                <select name="part_id" required class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-base">
                    <option value="">-- เลือกอะไหล่จากคลัง --</option>
                    <?php foreach ($spareParts as $sp): ?>
                        <option value="<?= $sp['id'] ?>">
                            <?= htmlspecialchars($sp['name']) ?> (คงเหลือ: <?= $sp['stock_quantity'] ?> ชิ้น | <?= number_format($sp['unit_price'], 2) ?> ฿)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-800 mb-1.5">จำนวนที่ต้องการเบิก <span class="text-rose-500">*</span></label>
                <input type="number" name="quantity" min="1" value="1" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base">
            </div>
            <p class="text-sm text-slate-500 font-medium">คำขอเบิกจะถูกส่งไปยังหัวหน้างานเพื่อพิจารณาอนุมัติและตัดสต็อก</p>
            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('part-modal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-100">ยกเลิก</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold shadow-md">ส่งคำขอเบิก</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Log & Close Job (Repair Proof & Solution) -->
<div id="close-job-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-4 text-base">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <?= svg_icon('clipboard', 'w-6 h-6 text-emerald-600') ?>
                <span>บันทึกการซ่อมจริง &amp; ส่งมอบงาน</span>
            </h3>
            <button onclick="document.getElementById('close-job-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-2xl font-bold">&times;</button>
        </div>
        <form action="<?= url('/technician/jobs/' . $ticket['id'] . '/close') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <div>
                <label class="block font-bold text-slate-800 mb-1.5">สาเหตุจริงของปัญหา <span class="text-rose-500">*</span></label>
                <textarea name="root_cause" rows="2" required placeholder="เช่น ซีลยางวาล์วน้ำเสื่อมสภาพ, คอยล์ร้อนมีฝุ่นอุดตัน, สายต่อการ์ดจอหลวม..." 
                          class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>
            <div>
                <label class="block font-bold text-slate-800 mb-1.5">วิธีการแก้ไขปัญหา <span class="text-rose-500">*</span></label>
                <textarea name="solution_note" rows="3" required placeholder="อธิบายวิธีแก้ไข เช่น ทำการเปลี่ยนวาล์วตัวใหม่, ล้างแผงฟิลเตอร์และเติมน้ำยาแอร์..." 
                          class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>
            <div>
                <label class="block font-bold text-slate-800 mb-1.5">เวลาที่ใช้ซ่อมจริง (ชม.)</label>
                <input type="number" step="0.25" min="0.25" name="actual_hours" value="1.00" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base">
            </div>
            <div>
                <label class="block font-bold text-slate-800 mb-1.5">รูปถ่ายหลังซ่อมเสร็จ</label>
                <input type="file" name="proof_photo" accept="image/*" class="w-full text-base text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-emerald-50 file:text-emerald-800">
            </div>
            <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('close-job-modal').classList.add('hidden')" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-100">ยกเลิก</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">บันทึกผล &amp; ปิดงานส่งมอบ</button>
            </div>
        </form>
    </div>
</div>
