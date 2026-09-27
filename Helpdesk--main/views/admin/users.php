<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">ระบบจัดการผู้ใช้งาน</h1>
            <p class="text-base text-slate-600 font-medium">จัดการข้อมูลผู้ใช้งาน กำหนดสิทธิ์ และมอบหมายบทบาท (ผู้แจ้งซ่อม, ช่างเทคนิค, ผู้ดูแลระบบ)</p>
        </div>
        <div>
            <button onclick="document.getElementById('add-user-modal').classList.remove('hidden')" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-200 transition">
                <?= svg_icon('plus-circle', 'w-4 h-4') ?>
                <span>เพิ่มผู้ใช้งานใหม่</span>
            </button>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200 uppercase font-bold tracking-wider text-slate-700 text-sm">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">ชื่อ-นามสกุล</th>
                        <th class="px-6 py-4">อีเมล</th>
                        <th class="px-6 py-4">บทบาท</th>
                        <th class="px-6 py-4">วันที่สร้าง</th>
                        <th class="px-6 py-4 text-right">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-base">
                    <?php foreach ($users as $u): 
                        $roleEnum = \App\Enums\UserRole::tryFrom($u['role']);
                    ?>
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">#<?= $u['id'] ?></td>
                        <td class="px-6 py-4 font-semibold text-slate-900 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm">
                                <?= mb_substr($u['name'], 0, 1, 'UTF-8') ?>
                            </span>
                            <span><?= htmlspecialchars($u['name']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full font-bold text-sm <?= $roleEnum ? $roleEnum->badgeClass() : '' ?>">
                                <?= $roleEnum ? $roleEnum->label() : $u['role'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-medium text-sm"><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                        <td class="px-6 py-4 text-right whitespace-nowrap space-x-1.5">
                            <button onclick="openEditUserModal(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>', '<?= htmlspecialchars(addslashes($u['email'])) ?>', '<?= $u['role'] ?>')" 
                                    class="px-3 py-1.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-sm inline-flex items-center gap-1 transition">
                                <?= svg_icon('pencil', 'w-3.5 h-3.5 text-slate-600') ?>
                                <span>แก้ไข</span>
                            </button>
                            <?php if ($u['id'] !== (int)$user['id']): ?>
                                <form action="<?= url('/admin/users/' . $u['id'] . '/delete') ?>" method="POST" class="inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้นี้?')">
                                    <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-sm inline-flex items-center gap-1 border border-rose-200 transition">
                                        <?= svg_icon('trash', 'w-3.5 h-3.5 text-rose-600') ?>
                                        <span>ลบ</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add User -->
<div id="add-user-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-base">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <?= svg_icon('plus-circle', 'w-5 h-5 text-indigo-600') ?>
                <span>เพิ่มผู้ใช้งานใหม่</span>
            </h3>
            <button onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 text-xl leading-none">&times;</button>
        </div>
        <form action="<?= url('/admin/users') ?>" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">ชื่อ-นามสกุล <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="สมชาย หมายดี" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">อีเมล <span class="text-rose-500">*</span></label>
                <input type="email" name="email" required placeholder="somchai@helpdesk.local" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">บทบาท <span class="text-rose-500">*</span></label>
                <select name="role" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
                    <option value="user">ผู้แจ้งซ่อม</option>
                    <option value="technician">ช่างเทคนิค</option>
                    <option value="admin">ผู้ดูแลระบบ</option>
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">รหัสผ่าน <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('add-user-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">ยกเลิก</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md transition flex items-center gap-1.5">
                    <?= svg_icon('check-circle', 'w-4 h-4') ?>
                    <span>บันทึกผู้ใช้</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit User -->
<div id="edit-user-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-base">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <?= svg_icon('pencil', 'w-5 h-5 text-indigo-600') ?>
                <span>แก้ไขข้อมูลผู้ใช้งาน</span>
            </h3>
            <button onclick="document.getElementById('edit-user-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 text-xl leading-none">&times;</button>
        </div>
        <form id="edit-user-form" action="" method="POST" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">ชื่อ-นามสกุล</label>
                <input type="text" name="name" id="edit-user-name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">อีเมล</label>
                <input type="email" name="email" id="edit-user-email" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">บทบาท</label>
                <select name="role" id="edit-user-role" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
                    <option value="user">ผู้แจ้งซ่อม</option>
                    <option value="technician">ช่างเทคนิค</option>
                    <option value="admin">ผู้ดูแลระบบ</option>
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-700 text-sm uppercase mb-1.5">เปลี่ยนรหัสผ่าน (เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยน)</label>
                <input type="password" name="password" placeholder="••••••••" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-base text-slate-800 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('edit-user-modal').classList.add('hidden')" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition">ยกเลิก</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md transition flex items-center gap-1.5">
                    <?= svg_icon('check-circle', 'w-4 h-4') ?>
                    <span>บันทึกการแก้ไข</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditUserModal(id, name, email, role) {
        document.getElementById('edit-user-form').action = `<?= url('/admin/users') ?>/${id}`;
        document.getElementById('edit-user-name').value = name;
        document.getElementById('edit-user-email').value = email;
        document.getElementById('edit-user-role').value = role;
        document.getElementById('edit-user-modal').classList.remove('hidden');
    }
</script>
