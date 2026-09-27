<div class="max-w-3xl mx-auto space-y-6">
    <!-- Back button & Title -->
    <div class="flex items-center gap-3.5">
        <a href="<?= url('/dashboard') ?>" class="p-3 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 transition shadow-xs" title="กลับแดชบอร์ด">
            <?= svg_icon('arrow-left', 'w-6 h-6 text-slate-700') ?>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">เปิดใบแจ้งซ่อมบำรุง / IT Helpdesk</h1>
            <p class="text-base text-slate-600 mt-1">กรอกข้อมูลปัญหา สถานที่ และแนบไฟล์เพื่อให้เจ้าหน้าที่และช่างเทคนิคเข้าดำเนินการได้อย่างรวดเร็ว</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form action="<?= url('/tickets') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">

            <!-- Category & Priority -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-base font-bold text-slate-800 mb-2">
                        หมวดหมู่งานซ่อม <span class="text-rose-500">*</span>
                    </label>
                    <select name="category_id" required class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-base focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- เลือกหมวดหมู่ปัญหา --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-base font-bold text-slate-800 mb-2">
                        ระดับความเร่งด่วน <span class="text-rose-500">*</span>
                    </label>
                    <select name="priority" required class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-base focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="low">ต่ำ - SLA 48 ชม.</option>
                        <option value="normal" selected>ปกติ - SLA 24 ชม.</option>
                        <option value="high">สูง - SLA 8 ชม.</option>
                        <option value="urgent">ด่วนที่สุด - SLA 4 ชม.</option>
                    </select>
                </div>
            </div>

            <!-- Location (สถานที่/อาคาร/ชั้น) -->
            <div>
                <label class="block text-base font-bold text-slate-800 mb-2">
                    สถานที่เกิดเหตุ / อาคาร / ชั้น / หมายเลขห้อง <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="location" required placeholder="เช่น อาคาร A ชั้น 3 แผนกบัญชี โต๊ะ 12 หรือ ห้องประชุม 201" 
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Title -->
            <div>
                <label class="block text-base font-bold text-slate-800 mb-2">
                    หัวข้อปัญหา <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" required placeholder="เช่น แอร์มีน้ำหยด, ท่อประปาอ่างล้างมือรั่ว, จอคอมพิวเตอร์เปิดไม่ติด" 
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-base font-bold text-slate-800 mb-2">
                    รายละเอียดและอาการ <span class="text-rose-500">*</span>
                </label>
                <textarea name="description" rows="4" required 
                          placeholder="อธิบายอาการที่เกิดขึ้น เวลาที่เริ่มพบปัญหา ผลกระทบต่อการทำงาน..."
                          class="w-full px-4 py-3 rounded-xl border border-slate-200 text-base focus:outline-none focus:ring-2 focus:ring-indigo-500 leading-relaxed"></textarea>
            </div>

            <!-- Drag & Drop File Upload -->
            <div>
                <label class="block text-base font-bold text-slate-800 mb-2">
                    แนบรูปถ่ายหน้างานหรือเอกสารประกอบ (ไม่บังคับ)
                </label>
                <div id="dropzone" class="border-2 border-dashed border-slate-300 hover:border-indigo-500 rounded-2xl p-6 text-center cursor-pointer transition bg-slate-50/50">
                    <input type="file" name="attachment" id="file-input" class="hidden" accept="image/*,.pdf,.txt,.zip">
                    <div class="space-y-2">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-2">
                            <?= svg_icon('camera', 'w-8 h-8 text-indigo-600') ?>
                        </div>
                        <p class="text-base text-slate-700 font-semibold">
                            <span class="text-indigo-600 font-bold hover:underline">คลิกเพื่อเลือกรูปภาพ</span> หรือลากไฟล์มาวาง
                        </p>
                        <p class="text-sm text-slate-500">รองรับรูปถ่าย PNG, JPG, WEBP หรือ PDF สูงสุด 5MB</p>
                    </div>
                    <div id="file-preview" class="hidden mt-3 text-sm text-indigo-800 font-bold bg-indigo-50 py-2 px-4 rounded-xl inline-block border border-indigo-200"></div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3.5 pt-5 border-t border-slate-100">
                <a href="<?= url('/dashboard') ?>" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 text-base font-bold transition">
                    ยกเลิก
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-7 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-base font-extrabold shadow-md shadow-indigo-200 transition transform active:scale-95">
                    <span>ส่งใบแจ้งซ่อม</span>
                    <?= svg_icon('arrow-right', 'w-5 h-5') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('file-input');
    const filePreview = document.getElementById('file-preview');

    dropzone.addEventListener('click', () => fileInput.click());

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('border-indigo-500', 'bg-indigo-50/40');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-indigo-500', 'bg-indigo-50/40');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-indigo-500', 'bg-indigo-50/40');
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            showPreview(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            showPreview(fileInput.files[0]);
        }
    });

    function showPreview(file) {
        filePreview.textContent = `ไฟล์ที่เลือก: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
        filePreview.classList.remove('hidden');
    }
</script>

