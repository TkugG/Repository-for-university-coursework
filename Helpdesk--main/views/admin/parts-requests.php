<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">คำขอเบิกอะไหล่</h1>
            <p class="text-base text-slate-600 font-medium">ตรวจสอบและอนุมัติการเบิกใช้อะไหล่/อุปกรณ์ในงานซ่อมบำรุงโดยช่างเทคนิค</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="<?= url('/admin/inventory') ?>" class="px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 font-bold text-sm hover:bg-slate-50 transition inline-flex items-center gap-1.5 shadow-xs">
                <?= svg_icon('package', 'w-4 h-4 text-slate-600') ?>
                <span>ดูคลังอะไหล่ทั้งหมด</span>
            </a>
            <span class="text-sm font-bold px-3 py-1.5 rounded-xl bg-orange-50 text-orange-800 border border-orange-200 inline-flex items-center gap-1.5 shadow-xs">
                <?= svg_icon('clock', 'w-4 h-4 text-orange-600') ?>
                รอการพิจารณา <?= count($requests) ?> รายการ
            </span>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200 uppercase font-bold tracking-wider text-slate-700 text-sm">
                    <tr>
                        <th class="px-6 py-4">ใบงาน</th>
                        <th class="px-6 py-4">สถานที่</th>
                        <th class="px-6 py-4">ช่างผู้ขอเบิก</th>
                        <th class="px-6 py-4">รายการอะไหล่</th>
                        <th class="px-6 py-4">จำนวนที่ขอ</th>
                        <th class="px-6 py-4">สต็อกคงเหลือ</th>
                        <th class="px-6 py-4">มูลค่ารวม (บาท)</th>
                        <th class="px-6 py-4 text-right">การพิจารณา</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-base">
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="8" class="p-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                                    <?= svg_icon('check-circle', 'w-7 h-7') ?>
                                </div>
                                <p class="text-base font-bold text-slate-800">ไม่มีรายการขอเบิกอะไหล่ค้างในระบบ</p>
                                <p class="text-sm text-slate-500 mt-1">คำขอเบิกอะไหล่ทั้งหมดได้รับการพิจารณาเรียบร้อยแล้ว</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): 
                            $hasEnoughStock = $r['stock_quantity'] >= $r['quantity'];
                        ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4">
                                <a href="<?= url('/tickets/' . $r['ticket_id']) ?>" target="_blank" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                    #<?= $r['ticket_id'] ?>: <?= htmlspecialchars($r['ticket_title']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-700 font-medium"><?= htmlspecialchars($r['ticket_location']) ?></td>
                            <td class="px-6 py-4 font-semibold text-slate-900"><?= htmlspecialchars($r['technician_name'] ?? 'ช่างเทคนิค') ?></td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900"><?= htmlspecialchars($r['part_name']) ?></div>
                                <div class="text-slate-500 font-mono text-xs mt-0.5"><?= htmlspecialchars($r['part_code']) ?></div>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 text-base"><?= $r['quantity'] ?> ชิ้น</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full font-bold text-sm <?= $hasEnoughStock ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200' ?>">
                                    <?= $r['stock_quantity'] ?> ชิ้น
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-800 font-bold">
                                <?= number_format((float)$r['total_price'], 2) ?> ฿
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                <?php if ($hasEnoughStock): ?>
                                    <form action="<?= url('/admin/parts-requests/' . $r['id'] . '/approve') ?>" method="POST" class="inline">
                                        <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                                        <button type="submit" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm transition shadow-xs inline-flex items-center gap-1">
                                            <?= svg_icon('check-circle', 'w-4 h-4') ?>
                                            <span>อนุมัติเบิก</span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-sm text-rose-600 font-bold mr-2 inline-flex items-center gap-1">
                                        <?= svg_icon('alert-triangle', 'w-4 h-4') ?>
                                        สต็อกไม่พอ
                                    </span>
                                <?php endif; ?>

                                <form action="<?= url('/admin/parts-requests/' . $r['id'] . '/reject') ?>" method="POST" class="inline" onsubmit="return confirm('ยืนยันปฏิเสธการขอเบิกอะไหล่นี้?')">
                                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                                    <button type="submit" class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-sm transition inline-flex items-center gap-1 border border-rose-200">
                                        <?= svg_icon('x-circle', 'w-4 h-4') ?>
                                        <span>ไม่อนุมัติ</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
