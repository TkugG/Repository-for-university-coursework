<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">จัดการหมวดหมู่ปัญหา</h1>
            <p class="text-base text-slate-600 font-medium">หมวดหมู่สำหรับจัดกลุ่มปัญหาเพื่อให้สามารถกระจายงานและเก็บสถิติได้อย่างมีประสิทธิภาพ</p>
        </div>
        <div>
            <button onclick="document.getElementById('add-cat-modal').classList.remove('hidden')" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-200 transition">
                <?= svg_icon('plus-circle', 'w-4 h-4') ?>
                <span>เพิ่มหมวดหมู่ใหม่</span>
            </button>
        </div>
    </div>

    <!-- Categories Grid / Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200 uppercase font-bold tracking-wider text-slate-700 text-sm">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">ชื่อหมวดหมู่</th>
                    <th class="px-6 py-4">คำอธิบาย</th>
                    <th class="px-6 py-4">จำนวนใบงาน</th>
                    <th class="px-6 py-4 text-right">การจัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-base">
                <?php foreach ($categories as $c): ?>
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-6 py-4 font-bold text-slate-900">#<?= $c['id'] ?></td>
                    <td class="px-6 py-4 font-semibold text-slate-900 flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600">
                            <?= svg_icon('tag', 'w-4 h-4') ?>
                        </span>
                        <span><?= htmlspecialchars($c['name']) ?></span>
                    </td>
                    <td class="px-6 py-4 text-slate-600 font-medium max-w-xs truncate text-sm"><?= htmlspecialchars($c['description'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 font-bold text-sm border border-slate-200">
                            <?= $c['ticket_count'] ?? 0 ?> รายการ
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap space-x-1.5">
                        <button onclick="openEditCatModal(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['name'])) ?>', '<?= htmlspecialchars(addslashes($c['description'] ?? '')) ?>')" 
                                class="px-3 py-1.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-sm inline-flex items-center gap-1 transition">
                            <?= svg_icon('pencil', 'w-3.5 h-3.5 text-slate-600') ?>
                            <span>แก้ไข</span>
                        </button>
                        <form action="<?= url('/admin/categories/' . $c['id'] . '/delete') ?>" method="POST" class="inline" onsubmit="return confirm('ยืนยันลบหมวดหมู่นี้?')">
                            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-sm inline-flex items-center gap-1 border border-rose-200 transition">
                                <?= svg_icon('trash', 'w-3.5 h-3.5 text-rose-600') ?>
                                <span>ลบ</span>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Category -->
<div id="add-cat-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-base">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <?= svg_icon('plus-circle', 'w-5 h-5 text-indigo-600') ?>
                <span>เพิ่มหมวดหมู่ปัญหา</span>
            </h3>
            <button onclick="document.getElementById('add-cat-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 text-xl leading-none">&times;</button>
        </div>
        <form action="<?= url('/admin/categories') ?>" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">ชื่อหมวดหมู่ <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="เช่น Hardware, Software, Network" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">คำอธิบายรายละเอียด</label>
                <textarea name="description" rows="3" placeholder="ระบุประเภทของปัญหาที่จัดอยู่ในหมวดหมู่นี้..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('add-cat-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">ยกเลิก</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md transition flex items-center gap-1.5">
                    <?= svg_icon('check-circle', 'w-4 h-4') ?>
                    <span>บันทึก</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Category -->
<div id="edit-cat-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-base">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <?= svg_icon('pencil', 'w-5 h-5 text-indigo-600') ?>
                <span>แก้ไขหมวดหมู่</span>
            </h3>
            <button onclick="document.getElementById('edit-cat-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 text-xl leading-none">&times;</button>
        </div>
        <form id="edit-cat-form" action="" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">ชื่อหมวดหมู่</label>
                <input type="text" name="name" id="edit-cat-name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">คำอธิบาย</label>
                <textarea name="description" id="edit-cat-desc" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('edit-cat-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">ยกเลิก</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md transition flex items-center gap-1.5">
                    <?= svg_icon('check-circle', 'w-4 h-4') ?>
                    <span>บันทึกการแก้ไข</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditCatModal(id, name, desc) {
        document.getElementById('edit-cat-form').action = `<?= url('/admin/categories') ?>/${id}`;
        document.getElementById('edit-cat-name').value = name;
        document.getElementById('edit-cat-desc').value = desc;
        document.getElementById('edit-cat-modal').classList.remove('hidden');
    }
</script>
