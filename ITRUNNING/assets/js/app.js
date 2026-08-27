/**
 * ==========================================================
 * ไฟล์: assets/js/app.js
 * คำอธิบาย: สคริปต์ JavaScript เสริมการทำงานของหน้าเว็บ
 * ==========================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // ฟังก์ชันช่วยจัดการ Live Preview รูปภาพ
    function setupImagePreview(inputId, containerId, imgId, placeholderId, nameId) {
        const input = document.getElementById(inputId);
        const container = document.getElementById(containerId);
        const img = document.getElementById(imgId);
        const placeholder = document.getElementById(placeholderId);
        const nameEl = document.getElementById(nameId);

        if (!input || !img) return;

        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // ตรวจสอบขนาดไฟล์ <= 2MB
                const maxSize = 2 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('⚠️ ไฟล์ ' + file.name + ' มีขนาดเกิน 2MB กรุณาเลือกไฟล์ใหม่');
                    this.value = '';
                    if (container) container.classList.add('hidden');
                    if (placeholder) placeholder.classList.remove('hidden');
                    return;
                }

                // แสดงตัวอย่างรูปภาพ
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    if (container) container.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                    if (nameEl) nameEl.textContent = `${file.name} (${(file.size / (1024 * 1024)).toFixed(2)} MB)`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 2. ตั้งค่า Live Preview สำหรับ Runner Photo และ Slip Image
    setupImagePreview('runner_photo', 'runner-photo-preview-container', 'runner-photo-preview-img', 'runner-photo-placeholder', 'runner-photo-name');
    setupImagePreview('slip_image', 'slip-preview-container', 'slip-preview-img', 'upload-placeholder', 'slip-file-name');

    // 3. Auto dismiss flash alert after 6 seconds
    const flashAlert = document.getElementById('flash-alert');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            flashAlert.style.opacity = '0';
            flashAlert.style.transform = 'translateY(-10px)';
            setTimeout(() => flashAlert.remove(), 500);
        }, 6000);
    }
});
