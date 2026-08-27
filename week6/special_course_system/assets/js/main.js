/**
 * ระบบขอเปิดหมู่เรียนพิเศษ — Global JavaScript
 * HCI: Real-time Feedback, Error Prevention
 */

'use strict';

// ─── Bootstrap Form Validation ────────────────────────────────────────────────
(function () {
    const forms = document.querySelectorAll('form.needs-validation');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        // Real-time field-level feedback on blur
        form.querySelectorAll('input, select, textarea').forEach(function (field) {
            field.addEventListener('blur', function () {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });
    });
})();

// ─── Character counter for reason textarea ────────────────────────────────────
(function () {
    const reason  = document.getElementById('reason');
    const counter = document.getElementById('reason-counter');
    if (!reason || !counter) return;

    const MIN_CHARS = 20;

    function update() {
        const len = reason.value.length;
        counter.textContent = len;
        counter.style.color = len < MIN_CHARS ? '#dc3545' : '#198754';
    }

    reason.addEventListener('input', update);
    update();
})();

// ─── Auto-dismiss flash messages ──────────────────────────────────────────────
(function () {
    document.querySelectorAll('.alert-dismissible[data-auto-dismiss]').forEach(function (el) {
        const delay = parseInt(el.dataset.autoDismiss, 10) || 4000;
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            bsAlert.close();
        }, delay);
    });
})();
