/**
 * ==========================================================
 * ไฟล์: assets/js/app.js
 * คำอธิบาย: สคริปต์ JavaScript จัดการ UI, Menu, Preview และ Flash Alerts
 * ==========================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. จัดการ Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // 2. ฟังก์ชันช่วยจัดการ Live Preview รูปภาพ พร้อมตรวจขนาดไฟล์
    function bindImagePreview(inputId, boxId, imgId, maxSizeMB = 5) {
        const input = document.getElementById(inputId);
        const box = document.getElementById(boxId);
        const img = document.getElementById(imgId);

        if (!input || !box || !img) return;

        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // ตรวจสอบขนาดไฟล์
                const maxBytes = maxSizeMB * 1024 * 1024;
                if (file.size > maxBytes) {
                    alert(`⚠️ ไฟล์ "${file.name}" มีขนาดเกิน ${maxSizeMB}MB กรุณาเลือกไฟล์ที่มีขนาดเล็กลง`);
                    this.value = '';
                    box.classList.add('hidden');
                    box.classList.remove('flex');
                    img.src = '';
                    return;
                }

                // แสดงตัวอย่างรูปภาพทันที
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    box.classList.remove('hidden');
                    box.classList.add('flex');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // เรียกใช้งาน Preview สำหรับหน้าสมัครวิ่ง
    bindImagePreview('runner_photo_input', 'runner_photo_preview_box', 'runner_photo_preview', 5);
    bindImagePreview('slip_input', 'slip_preview_box', 'slip_preview', 5);

    // 3. ปิดกล่องข้อความแจ้งเตือน Flash Alert อัตโนมัติหลัง 6 วินาที
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
