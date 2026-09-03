<?php
/**
 * ==========================================================
 * ไฟล์: includes/functions.php
 * คำอธิบาย: ฟังก์ชันช่วยเหลือส่วนกลาง (Helper Functions)
 * ==========================================================
 */

/**
 * ป้องกัน XSS Injection (Sanitize HTML Output)
 */
if (!function_exists('e')) {
    function e(?string $string): string {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * แปลงวันที่เป็นรูปแบบภาษาไทย
 * เช่น "2026-10-15" => "15 ต.ค. 2026"
 */
if (!function_exists('thai_date')) {
    function thai_date(?string $datetime, bool $show_time = false): string {
        if (empty($datetime)) return '-';
        
        $timestamp = strtotime($datetime);
        if (!$timestamp) return $datetime;

        $thai_months = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];

        $day   = date('j', $timestamp);
        $month = $thai_months[(int)date('n', $timestamp)];
        $year  = date('Y', $timestamp);

        if ($show_time) {
            $time = date('H:i', $timestamp) . ' น.';
            return "$day $month $year ($time)";
        }

        return "$day $month $year";
    }
}

/**
 * จัดรูปแบบตัวเลขเงินบาท
 */
if (!function_exists('format_baht')) {
    function format_baht(float|int|string|null $amount): string {
        if ($amount === null || $amount === '') return '฿0';
        return '฿' . number_format((float)$amount, 0);
    }
}

/**
 * คืนค่า Path รูปภาพอัปโหลดที่ถูกต้อง (รองรับทั้ง uploads/ และ subdirectories)
 */
if (!function_exists('get_upload_url')) {
    function get_upload_url(?string $filename, string $subfolder = ''): string {
        if (empty($filename)) return '';
        
        // หากมีไฟล์ใน uploads/ โดยตรง
        if (file_exists(ROOT_DIR . '/uploads/' . $filename)) {
            return 'uploads/' . $filename;
        }
        
        // หากมีไฟล์ในโฟลเดอร์ย่อย เช่น uploads/photos/ หรือ uploads/slips/
        if ($subfolder && file_exists(ROOT_DIR . '/uploads/' . $subfolder . '/' . $filename)) {
            return 'uploads/' . $subfolder . '/' . $filename;
        }

        return 'uploads/' . $filename;
    }
}

/**
 * แสดงกล่องแจ้งเตือน Flash Message (Success / Error)
 */
if (!function_exists('render_flash_alert')) {
    function render_flash_alert(): void {
        if (isset($_SESSION['msg'])) {
            $msg = $_SESSION['msg'];
            unset($_SESSION['msg']);
            echo '
            <div id="flash-alert" class="max-w-7xl mx-auto px-4 pt-4 no-print">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between shadow-sm animate-fade-in">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
                        <span class="text-sm font-medium">' . e($msg) . '</span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>';
        }

        if (isset($_SESSION['error'])) {
            $err = $_SESSION['error'];
            unset($_SESSION['error']);
            echo '
            <div id="flash-alert" class="max-w-7xl mx-auto px-4 pt-4 no-print">
                <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl flex items-center justify-between shadow-sm animate-fade-in">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 flex-shrink-0"></i>
                        <span class="text-sm font-medium">' . e($err) . '</span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-rose-500 hover:text-rose-800">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>';
        }
    }
}
