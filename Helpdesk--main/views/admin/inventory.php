<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">คลังอะไหล่และอุปกรณ์</h1>
            <p class="text-base text-slate-600 font-medium">จัดการรายการวัสดุ/อุปกรณ์และอะไหล่คงคลังสำหรับการซ่อมแซมและบำรุงรักษา</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="<?= url('/admin/parts-requests') ?>" class="px-4 py-2.5 rounded-xl bg-orange-50 hover:bg-orange-100 text-orange-800 font-bold text-sm border border-orange-200 transition inline-flex items-center gap-1.5 shadow-xs">
                <?= svg_icon('package', 'w-4 h-4 text-orange-600') ?>
                <span>ตรวจสอบคำขอเบิกอะไหล่</span>
            </a>
            <button onclick="document.getElementById('add-part-modal').classList.remove('hidden')" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-200 transition">
                <?= svg_icon('plus-circle', 'w-4 h-4') ?>
                <span>เพิ่มอะไหล่ใหม่</span>
            </button>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200 uppercase font-bold tracking-wider text-slate-700 text-sm">
                    <tr>
                        <th class="px-6 py-4">รหัสอะไหล่</th>
                        <th class="px-6 py-4">ชื่อรายการ</th>
                        <th class="px-6 py-4">จำนวนคงเหลือ</th>
                        <th class="px-6 py-4">ราคาต่อหน่วย (บาท)</th>
                        <th class="px-6 py-4 text-right">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-base">
                    <?php if (empty($parts)): ?>
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-500">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-3">
                                    <?= svg_icon('package', 'w-6 h-6') ?>
                                </div>
                                <p class="text-base font-bold text-slate-800">ยังไม่มีรายการอะไหล่ในคลัง</p>
                                <p class="text-sm text-slate-500 mt-1">กดปุ่ม "เพิ่มอะไหล่ใหม่" เพื่อเริ่มบันทึกข้อมูลเข้าสู่ระบบ</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($parts as $p): 
                            $isLowStock = $p['stock_quantity'] <= 5;
                        ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900"><?= htmlspecialchars($p['part_code']) ?></td>
                            <td class="px-6 py-4 font-semibold text-slate-800"><?= htmlspecialchars($p['name']) ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-bold <?= $isLowStock ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' ?>">
                                    <?php if ($isLowStock): ?>
                                        <?= svg_icon('alert-triangle', 'w-3.5 h-3.5 text-rose-600') ?>
                                    <?php endif; ?>
                                    <?= $p['stock_quantity'] ?> ชิ้น <?= $isLowStock ? '(ใกล้หมด)' : '' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-800 font-bold">
                                <?= number_format((float)$p['unit_price'], 2) ?> ฿
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-1.5">
                                <button onclick="openEditPartModal(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['part_code'])) ?>', '<?= htmlspecialchars(addslashes($p['name'])) ?>', <?= $p['stock_quantity'] ?>, <?= $p['unit_price'] ?>)" 
                                        class="px-3 py-1.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-sm inline-flex items-center gap-1 transition">
                                    <?= svg_icon('pencil', 'w-3.5 h-3.5 text-slate-600') ?>
                                    <span>แก้ไข</span>
                                </button>
                                <form action="<?= url('/admin/inventory/' . $p['id'] . '/delete') ?>" method="POST" class="inline" onsubmit="return confirm('ยืนยันลบอะไหล่นี้?')">
                                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-sm inline-flex items-center gap-1 border border-rose-200 transition">
                                        <?= svg_icon('trash', 'w-3.5 h-3.5 text-rose-600') ?>
                                        <span>ลบ</span>
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

<!-- Modal: Add Part -->
<div id="add-part-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-base">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <?= svg_icon('plus-circle', 'w-5 h-5 text-indigo-600') ?>
                <span>เพิ่มอะไหล่เข้าคลัง</span>
            </h3>
            <button onclick="document.getElementById('add-part-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 text-xl leading-none">&times;</button>
        </div>
        <form action="<?= url('/admin/inventory') ?>" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">รหัสอะไหล่ <span class="text-rose-500">*</span></label>
                <input type="text" name="part_code" required placeholder="เช่น RAM-DDR4-8G" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">ชื่อรายการอะไหล่ <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="เช่น RAM DDR4 8GB Kingston" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">จำนวนตั้งต้น</label>
                    <input type="number" name="stock_quantity" min="0" value="10" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">ราคาต่อหน่วย (บาท)</label>
                    <input type="number" step="0.01" name="unit_price" value="0.00" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('add-part-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">ยกเลิก</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md transition flex items-center gap-1.5">
                    <?= svg_icon('check-circle', 'w-4 h-4') ?>
                    <span>บันทึก</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Part -->
<div id="edit-part-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-base">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <?= svg_icon('pencil', 'w-5 h-5 text-indigo-600') ?>
                <span>แก้ไขข้อมูลอะไหล่</span>
            </h3>
            <button onclick="document.getElementById('edit-part-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 text-xl leading-none">&times;</button>
        </div>
        <form id="edit-part-form" action="" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">รหัสอะไหล่</label>
                <input type="text" name="part_code" id="edit-part-code" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">ชื่อรายการอะไหล่</label>
                <input type="text" name="name" id="edit-part-name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">จำนวนคงเหลือ</label>
                    <input type="number" name="stock_quantity" id="edit-part-stock" min="0" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">ราคาต่อหน่วย (บาท)</label>
                    <input type="number" step="0.01" name="unit_price" id="edit-part-price" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('edit-part-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">ยกเลิก</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md transition flex items-center gap-1.5">
                    <?= svg_icon('check-circle', 'w-4 h-4') ?>
                    <span>บันทึกการแก้ไข</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditPartModal(id, code, name, stock, price) {
        document.getElementById('edit-part-form').action = `<?= url('/admin/inventory') ?>/${id}`;
        document.getElementById('edit-part-code').value = code;
        document.getElementById('edit-part-name').value = name;
        document.getElementById('edit-part-stock').value = stock;
        document.getElementById('edit-part-price').value = price;
        document.getElementById('edit-part-modal').classList.remove('hidden');
    }
</script>
