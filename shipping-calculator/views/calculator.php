<?php
/**
 * View: Smart Shipping Console
 * Template สำหรับแสดงผลหน้าต่างคำนวณค่าจัดส่ง (MVC - View Layer)
 * ได้รับตัวแปรมาจาก ShippingController
 */
?>
<!DOCTYPE html>
<html lang="th" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Shipping Console — ระบบคำนวณและแจกแจงค่าจัดส่งพัสดุ 77 จังหวัด</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Prompt', 'sans-serif'],
                        display: ['"Plus Jakarta Sans"', 'Prompt', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.02)',
                        'elevated': '0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.03)',
                        'receipt': '0 25px 50px -12px rgba(15, 23, 42, 0.12)',
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts: Prompt, Plus Jakarta Sans, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Canvas Confetti for Celebration Feedback -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #F8FAFC;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.06) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(249, 115, 22, 0.05) 0px, transparent 40%),
                radial-gradient(at 50% 100%, rgba(16, 185, 129, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
        }

        /* Range Slider Styling */
        input[type=range] {
            -webkit-appearance: none;
            background: transparent;
            cursor: pointer;
        }
        input[type=range]::-webkit-slider-track {
            background: #E2E8F0;
            height: 7px;
            border-radius: 9999px;
        }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 22px;
            width: 22px;
            border-radius: 50%;
            background: #4F46E5;
            border: 3px solid #FFFFFF;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.35);
            margin-top: -7.5px;
            transition: transform 0.15s ease;
        }
        input[type=range]::-webkit-slider-thumb:hover {
            transform: scale(1.15);
        }

        /* Micro-interactions */
        .btn-bounce {
            transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-bounce:hover {
            transform: translateY(-2px);
        }
        .btn-bounce:active {
            transform: translateY(0);
        }

        /* Locked Tab Styling */
        .step-tab-locked {
            opacity: 0.55;
            cursor: not-allowed !important;
            filter: grayscale(40%);
        }

        /* Shake animation when trying to click locked tabs */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-4px); }
            40%, 80% { transform: translateX(4px); }
        }
        .animate-shake {
            animation: shake 0.35s ease-in-out;
        }

        /* Custom sleek scrollbar for province autocomplete dropdown */
        .province-dropdown-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .province-dropdown-scroll::-webkit-scrollbar-track {
            background: #F8FAFC;
            border-radius: 9999px;
        }
        .province-dropdown-scroll::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 9999px;
        }
        .province-dropdown-scroll::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }

        /* Print Optimization */
        @media print {
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #000000 !important;
            }
            .no-print, header, nav, #stepSection1, #stepSection2, #toastAlert {
                display: none !important;
            }
            #stepSection3 {
                display: block !important;
            }
            .print-card {
                border: 1px solid #94A3B8 !important;
                box-shadow: none !important;
                border-radius: 8px !important;
                margin: 0 !important;
                padding: 20px !important;
                width: 100% !important;
            }
            .print-stamp {
                display: block !important;
            }
        }
    </style>
</head>
<body class="text-slate-800 min-h-screen py-8 px-4 sm:px-6 lg:px-8 selection:bg-indigo-500 selection:text-white">

<div class="max-w-4xl mx-auto space-y-7">

    <!-- 🚀 Branding Header -->
    <header class="no-print flex flex-col sm:flex-row items-center justify-between gap-4 pb-6 border-b border-slate-200/80">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-blue-500 p-0.5 shadow-md shadow-indigo-500/20 flex items-center justify-center">
                <div class="w-full h-full bg-white rounded-[14px] flex items-center justify-center text-2xl">
                    📦
                </div>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 font-display">
                        Smart Shipping Console
                    </h1>
                    <span class="text-[11px] font-extrabold uppercase px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 tracking-wider">
                        v3.1 Pro
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    คำนวณตามระยะทางจริง 77 จังหวัด • ระบบ Guard ตรวจสอบความถูกต้องก่อนประมวลผล
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 bg-white border border-slate-200 px-4 py-1.5 rounded-full shadow-sm text-xs font-semibold text-slate-600">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>

        </div>
    </header>

    <!-- 🧭 Strict Stepper Bar with Lock Protection -->
    <nav class="no-print bg-white p-3.5 sm:p-4 rounded-3xl border border-slate-200/80 shadow-soft">
        <div class="grid grid-cols-3 gap-2 text-center text-xs font-bold">
            <!-- Step 1 Tab (Always Available) -->
            <button type="button" id="tabStep1" class="step-tab flex items-center justify-center gap-2 py-3 px-3 rounded-2xl transition-all <?= ($currentStep === 1) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 hover:bg-slate-100' ?>">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-mono <?= ($currentStep === 1) ? 'bg-white text-indigo-700 font-bold' : 'bg-slate-200 text-slate-600' ?>">1</span>
                <span class="hidden sm:inline">1. พัสดุ & ต้นทาง-ปลายทาง</span>
                <span class="sm:hidden">1. พัสดุ</span>
            </button>

            <!-- Step 2 Tab (Guarded) -->
            <button type="button" id="tabStep2" class="step-tab flex items-center justify-center gap-2 py-3 px-3 rounded-2xl transition-all <?= ($currentStep === 2) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'step-tab-locked text-slate-400 bg-slate-50' ?>">
                <span class="step-icon-badge w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-mono <?= ($currentStep === 2) ? 'bg-white text-indigo-700 font-bold' : 'bg-slate-200 text-slate-500' ?>">
                    <?= ($currentStep >= 2) ? '2' : '🔒' ?>
                </span>
                <span class="hidden sm:inline">2. เปรียบเทียบขนส่ง</span>
                <span class="sm:hidden">2. ขนส่ง</span>
            </button>

            <!-- Step 3 Tab (Guarded) -->
            <button type="button" id="tabStep3" class="step-tab flex items-center justify-center gap-2 py-3 px-3 rounded-2xl transition-all <?= ($currentStep === 3) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'step-tab-locked text-slate-400 bg-slate-50' ?>">
                <span class="step-icon-badge w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-mono <?= ($currentStep === 3) ? 'bg-white text-indigo-700 font-bold' : 'bg-slate-200 text-slate-500' ?>">
                    <?= ($currentStep === 3) ? '3' : '🔒' ?>
                </span>
                <span class="hidden sm:inline">3. ใบแจกแจงค่าบริการ</span>
                <span class="sm:hidden">3. ใบเสร็จ</span>
            </button>
        </div>
    </nav>

    <!-- Floating Toast Notification for Guarded Action -->
    <div id="toastAlert" class="hidden no-print fixed bottom-6 right-6 max-w-md bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-elevated border border-slate-700 flex items-center gap-3 z-50 transition-all duration-300 transform translate-y-2 opacity-0">
        <span class="text-xl">⚠️</span>
        <div class="text-xs">
            <strong class="block font-bold text-amber-400" id="toastTitle">ขั้นตอนถูกล็อคอยู่</strong>
            <span id="toastMessage" class="text-slate-300">กรุณากดยืนยันข้อมูลในขั้นตอนที่ 1 ให้เรียบร้อยก่อน</span>
        </div>
    </div>

    <!-- ⚠️ Error Alert -->
    <?php if ($error): ?>
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3 shadow-sm no-print">
            <span class="text-2xl">🚨</span>
            <div>
                <p class="text-sm font-semibold">ข้อผิดพลาดในการประมวลผล</p>
                <p class="text-xs text-red-600"><?= htmlspecialchars($error) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Funnel Form -->
    <form id="funnelForm" method="POST" action="">
        <input type="hidden" name="step" id="formStep" value="<?= $currentStep ?>">
        <input type="hidden" name="provider" id="formProvider" value="<?= htmlspecialchars($selectedProvider) ?>">
        <input type="hidden" name="calculate_final" id="formCalculateFinal" value="1">

        <!-- =================================================================== -->
        <!-- STEP 1: PARCEL SPECS & PROVINCE ROUTE (เลือก 77 จังหวัด & พิกัด)   -->
        <!-- =================================================================== -->
        <div id="stepSection1" class="step-section <?= ($currentStep !== 1) ? 'hidden' : '' ?> space-y-6">
            
            <!-- 📍 Origin & Destination (77 Provinces Database + Live Distance) -->
            <div class="bg-white p-6 sm:p-7 rounded-3xl border border-slate-200/80 shadow-soft space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="text-indigo-600">📍</span> 1. กำหนดต้นทางและปลายทาง (77 จังหวัดทั่วไทย)
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            ระบบจะคำนวณระยะทางขับรถจริง (กม.) และตรวจจับพื้นที่พิเศษ/เกาะโดยอัตโนมัติ
                        </p>
                    </div>

                    <!-- Swap Route Button -->
                    <button type="button" id="btnSwapRoute" class="btn-bounce text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-3 py-1.5 rounded-xl flex items-center gap-1.5 self-start sm:self-auto transition">
                        <span>⇄ สลับต้นทาง-ปลายทาง</span>
                    </button>
                </div>

                <!-- Quick Route Presets -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-slate-400 font-medium">เส้นทางทดสอบยอดนิยม:</span>
                    <button type="button" class="route-preset-btn bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 px-3 py-1 rounded-lg border border-slate-200 font-medium transition"
                            data-origin="TH-10" data-dest="TH-61">
                        กรุงเทพฯ ➔ อุทัยธานี (~235 กม.)
                    </button>
                    <button type="button" class="route-preset-btn bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 px-3 py-1 rounded-lg border border-slate-200 font-medium transition"
                            data-origin="TH-10" data-dest="TH-50">
                        กรุงเทพฯ ➔ เชียงใหม่ (~710 กม.)
                    </button>
                    <button type="button" class="route-preset-btn bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 px-3 py-1 rounded-lg border border-slate-200 font-medium transition"
                            data-origin="TH-10" data-dest="TH-95">
                        กรุงเทพฯ ➔ ยะลา (เกาะ/พื้นที่พิเศษ 1,080 กม.)
                    </button>
                </div>

                <!-- Origin & Destination Inputs with Autocomplete / Searchable Combobox (77 Provinces) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Origin Combobox -->
                    <div class="space-y-2 relative" id="originComboboxContainer">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <span class="text-indigo-600">🛫</span> จังหวัดต้นทาง (Origin)
                            </span>
                            <span class="text-[10px] text-indigo-600 font-semibold bg-indigo-50 px-2 py-0.5 rounded-full">
                                ⌨️ พิมพ์ค้นหาได้
                            </span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="origin_code" id="originCode" value="<?= htmlspecialchars($inputOrigin) ?>">
                            <div class="relative flex items-center">
                                <span class="absolute left-4 text-slate-400 text-sm pointer-events-none">🔍</span>
                                <input type="text" id="originInput"
                                       value="<?= htmlspecialchars($allProvinces[$inputOrigin]['name'] ?? 'กรุงเทพมหานคร') ?>"
                                       placeholder="พิมพ์ชื่อจังหวัด เช่น กรุงเทพ, อุทัยธานี, เชียงใหม่..."
                                       autocomplete="off"
                                       spellcheck="false"
                                       class="w-full bg-slate-50 border border-slate-300 rounded-2xl pl-11 pr-16 py-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition shadow-sm placeholder:font-normal placeholder:text-slate-400">
                                <div class="absolute right-2.5 flex items-center gap-1">
                                    <button type="button" id="originClearBtn" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200 p-1.5 rounded-xl transition text-xs flex items-center justify-center w-7 h-7" title="ล้างข้อความเพื่อค้นหาใหม่">
                                        ✕
                                    </button>
                                    <button type="button" id="originToggleBtn" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200 p-1.5 rounded-xl transition text-xs flex items-center justify-center w-7 h-7" title="แสดงรายการทั้งหมด">
                                        ▼
                                    </button>
                                </div>
                            </div>

                            <!-- Floating Autocomplete Suggestions -->
                            <div id="originDropdownList" class="hidden absolute z-50 left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-elevated max-h-64 overflow-y-auto divide-y divide-slate-100 province-dropdown-scroll">
                                <!-- Generated dynamically by JS -->
                            </div>
                        </div>
                    </div>

                    <!-- Destination Combobox -->
                    <div class="space-y-2 relative" id="destComboboxContainer">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <span class="text-emerald-600">🛬</span> จังหวัดปลายทาง (Destination)
                            </span>
                            <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full">
                                ⌨️ พิมพ์ค้นหาได้
                            </span>
                        </label>
                        <div class="relative">
                            <input type="hidden" name="dest_code" id="destCode" value="<?= htmlspecialchars($inputDest) ?>">
                            <div class="relative flex items-center">
                                <span class="absolute left-4 text-slate-400 text-sm pointer-events-none">🔍</span>
                                <input type="text" id="destInput"
                                       value="<?= htmlspecialchars($allProvinces[$inputDest]['name'] ?? 'อุทัยธานี') ?>"
                                       placeholder="พิมพ์ชื่อจังหวัด เช่น อุทัยธานี, เชียงใหม่, ภูเก็ต..."
                                       autocomplete="off"
                                       spellcheck="false"
                                       class="w-full bg-slate-50 border border-slate-300 rounded-2xl pl-11 pr-16 py-3.5 text-sm font-bold text-slate-800 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 transition shadow-sm placeholder:font-normal placeholder:text-slate-400">
                                <div class="absolute right-2.5 flex items-center gap-1">
                                    <button type="button" id="destClearBtn" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200 p-1.5 rounded-xl transition text-xs flex items-center justify-center w-7 h-7" title="ล้างข้อความเพื่อค้นหาใหม่">
                                        ✕
                                    </button>
                                    <button type="button" id="destToggleBtn" class="text-slate-400 hover:text-slate-600 hover:bg-slate-200 p-1.5 rounded-xl transition text-xs flex items-center justify-center w-7 h-7" title="แสดงรายการทั้งหมด">
                                        ▼
                                    </button>
                                </div>
                            </div>

                            <!-- Floating Autocomplete Suggestions -->
                            <div id="destDropdownList" class="hidden absolute z-50 left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-elevated max-h-64 overflow-y-auto divide-y divide-slate-100 province-dropdown-scroll">
                                <!-- Generated dynamically by JS -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 🚗 Real-time Road Distance & Zone Banner -->
                <div id="routeStatusCard" class="p-4 rounded-2xl bg-indigo-50/80 border border-indigo-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg shadow-sm font-mono">
                            🛣️
                        </div>
                        <div>
                            <div class="font-bold text-indigo-950 text-sm flex items-center gap-2">
                                <span id="routeOriginNameDisplay">กรุงเทพมหานคร</span>
                                <span class="text-indigo-400">➔</span>
                                <span id="routeDestNameDisplay">อุทัยธานี</span>
                            </div>
                            <div class="text-slate-500 text-[11px] mt-0.5" id="routeZoneBadge">
                                ขนส่งข้ามภูมิภาค • คิดเรทระยะทางจริง
                            </div>
                        </div>
                    </div>

                    <div class="text-left sm:text-right">
                        <span class="text-slate-500 text-[11px] block">ระยะทางขับรถจริงโดยประมาณ</span>
                        <div class="font-mono font-extrabold text-xl text-indigo-700 flex items-baseline sm:justify-end gap-1">
                            <span id="routeDistanceDisplay">235</span>
                            <span class="text-xs font-semibold text-slate-500">กม.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📐 Box Dimensions & Weight -->
            <div class="bg-white p-6 sm:p-7 rounded-3xl border border-slate-200/80 shadow-soft space-y-6">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="text-indigo-600">📐</span> 2. ระบุขนาดและน้ำหนักพัสดุ
                    </h2>
                    <span class="text-[11px] text-slate-500 font-mono bg-slate-100 px-2.5 py-1 rounded-md">
                        Volumetric Divisor: 5000
                    </span>
                </div>

                <!-- Box Presets -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        เลือกขนาดกล่องสำเร็จรูปยอดนิยม (Box Presets)
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                        <button type="button" class="box-preset-btn btn-bounce p-3 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-indigo-500 hover:shadow-sm transition text-left"
                                data-w="22" data-l="31" data-h="1" data-weight="0.2">
                            <div class="text-sm font-bold text-slate-900">✉️ ซอง A4</div>
                            <div class="text-[10px] text-slate-500 font-mono">22×31×1 ซม. (0.2 kg)</div>
                        </button>
                        <button type="button" class="box-preset-btn btn-bounce p-3 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-indigo-500 hover:shadow-sm transition text-left"
                                data-w="11" data-l="17" data-h="6" data-weight="0.5">
                            <div class="text-sm font-bold text-slate-900">📦 เบอร์ 0</div>
                            <div class="text-[10px] text-slate-500 font-mono">11×17×6 ซม. (0.5 kg)</div>
                        </button>
                        <button type="button" class="box-preset-btn btn-bounce p-3 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-indigo-500 hover:shadow-sm transition text-left"
                                data-w="14" data-l="20" data-h="12" data-weight="1.5">
                            <div class="text-sm font-bold text-slate-900">📦 เบอร์ 2A</div>
                            <div class="text-[10px] text-slate-500 font-mono">14×20×12 ซม. (1.5 kg)</div>
                        </button>
                        <button type="button" class="box-preset-btn btn-bounce p-3 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-indigo-500 hover:shadow-sm transition text-left"
                                data-w="20" data-l="30" data-h="11" data-weight="2.5">
                            <div class="text-sm font-bold text-slate-900">📦 เบอร์ C</div>
                            <div class="text-[10px] text-slate-500 font-mono">20×30×11 ซม. (2.5 kg)</div>
                        </button>
                        <button type="button" class="box-preset-btn btn-bounce p-3 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-indigo-500 hover:shadow-sm transition text-left"
                                data-w="24" data-l="40" data-h="17" data-weight="5.0">
                            <div class="text-sm font-bold text-slate-900">📦 เบอร์ E</div>
                            <div class="text-[10px] text-slate-500 font-mono">24×40×17 ซม. (5.0 kg)</div>
                        </button>
                    </div>
                </div>

                <!-- Weight Slider + Number Input -->
                <div class="space-y-2 pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <label for="weightInput" class="text-sm font-bold text-slate-700">
                            น้ำหนักชั่งจริง (Actual Weight)
                        </label>
                        <div class="flex items-center gap-1 bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 focus-within:bg-white focus-within:border-indigo-600 focus-within:ring-2 focus-within:ring-indigo-100 transition shadow-inner">
                            <input type="number" id="weightInput" name="weight" step="0.1" min="0.1" max="100"
                                   value="<?= htmlspecialchars((string)$inputWeight) ?>"
                                   class="w-20 bg-transparent text-right font-mono font-bold text-slate-900 text-base focus:outline-none">
                            <span class="text-xs text-slate-500 font-mono font-semibold">kg</span>
                        </div>
                    </div>
                    <input type="range" id="weightSlider" min="0.1" max="30" step="0.1"
                           value="<?= htmlspecialchars((string)$inputWeight) ?>"
                           class="w-full">
                </div>

                <!-- Dimension Inputs -->
                <div class="space-y-2 pt-3 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        มิติกล่อง (กว้าง × ยาว × สูง)
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <span class="text-xs text-slate-500 block mb-1">กว้าง (Width)</span>
                            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus-within:bg-white focus-within:border-indigo-600 focus-within:ring-2 focus-within:ring-indigo-100 transition">
                                <input type="number" id="widthInput" name="width" step="0.5" min="1" max="250"
                                       value="<?= htmlspecialchars((string)$inputWidth) ?>"
                                       class="w-full bg-transparent text-right font-mono font-bold text-slate-900 text-sm focus:outline-none">
                                <span class="text-xs text-slate-400 font-mono ml-1">cm</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 block mb-1">ยาว (Length)</span>
                            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus-within:bg-white focus-within:border-indigo-600 focus-within:ring-2 focus-within:ring-indigo-100 transition">
                                <input type="number" id="lengthInput" name="length" step="0.5" min="1" max="250"
                                       value="<?= htmlspecialchars((string)$inputLength) ?>"
                                       class="w-full bg-transparent text-right font-mono font-bold text-slate-900 text-sm focus:outline-none">
                                <span class="text-xs text-slate-400 font-mono ml-1">cm</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 block mb-1">สูง (Height)</span>
                            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus-within:bg-white focus-within:border-indigo-600 focus-within:ring-2 focus-within:ring-indigo-100 transition">
                                <input type="number" id="heightInput" name="height" step="0.5" min="1" max="250"
                                       value="<?= htmlspecialchars((string)$inputHeight) ?>"
                                       class="w-full bg-transparent text-right font-mono font-bold text-slate-900 text-sm focus:outline-none">
                                <span class="text-xs text-slate-400 font-mono ml-1">cm</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chargeable Weight Summary Card -->
                <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-indigo-900 font-bold block text-sm">น้ำหนักคิดเงิน (Chargeable Weight):</span>
                        <span class="text-slate-500 text-[11px]">เทียบระหว่าง น้ำหนักชั่งจริง vs ปริมาตรกล่อง $(W \times L \times H) / 5000$</span>
                    </div>
                    <div class="text-right">
                        <span id="chargeableWeightDisplay" class="font-mono font-black text-xl text-indigo-700">
                            <?= number_format($parcel->getChargeableWeight(), 2) ?> kg
                        </span>
                        <span id="chargeableReasonDisplay" class="block text-[10px] text-indigo-500 font-bold">
                            (คิดตามน้ำหนักชั่งจริง)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Next Button to Step 2 (Confirmation Trigger) -->
            <button type="button" id="btnGoToStep2" class="w-full btn-bounce bg-gradient-to-r from-indigo-600 via-blue-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-bold py-4 px-6 rounded-2xl shadow-lg shadow-indigo-600/25 transition flex items-center justify-center gap-2 text-base">
                <span>ยืนยันข้อมูลพัสดุและเส้นทาง ➔ ไปเปรียบเทียบขนส่ง (ขั้นตอนที่ 2)</span>
            </button>
        </div>


        <!-- =================================================================== -->
        <!-- STEP 2: COMPARE & SELECT COURIER (เปรียบเทียบ 3 ขนส่งตามระยะทางจริง) -->
        <!-- =================================================================== -->
        <div id="stepSection2" class="step-section <?= ($currentStep !== 2) ? 'hidden' : '' ?> space-y-6">
            <div class="bg-white p-6 sm:p-7 rounded-3xl border border-slate-200/80 shadow-soft space-y-6">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="text-indigo-600">📊</span> เปรียบเทียบและเลือกบริษัทขนส่ง
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            คำนวณราคาจริงจากระยะทางขับรถและพิกัดน้ำหนักที่คุณระบุ
                        </p>
                    </div>
                    <button type="button" id="btnBackToStep1" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 underline self-start sm:self-auto">
                        ← แก้ไขขนาดและเส้นทาง
                    </button>
                </div>

                <!-- Verified Parcel & Route Badge -->
                <div class="flex flex-wrap items-center gap-2 p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-600">
                    <span class="bg-white px-3 py-1 rounded-xl font-semibold text-slate-800 border border-slate-200 shadow-sm flex items-center gap-1.5">
                        <span>📍</span>
                        <strong id="summaryRouteText">กรุงเทพมหานคร ➔ อุทัยธานี</strong>
                    </span>
                    <span class="bg-white px-3 py-1 rounded-xl font-mono font-semibold text-indigo-700 border border-indigo-200 shadow-sm">
                        🛣️ ระยะทาง: <strong id="summaryDistText">235 กม.</strong>
                    </span>
                    <span class="bg-white px-3 py-1 rounded-xl font-mono font-semibold text-slate-800 border border-slate-200 shadow-sm">
                        ⚖️ น้ำหนักคิดเงิน: <strong id="summaryWeightText">1.50 kg</strong>
                    </span>
                </div>

                <!-- 3 Courier Comparison Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" id="compareGrid">
                    
                    <!-- Kerry Card -->
                    <div class="courier-select-card p-5 rounded-3xl border-2 border-slate-200 bg-white hover:border-orange-500 hover:shadow-elevated transition flex flex-col justify-between cursor-pointer"
                         data-provider="kerry">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-3xl">📦</span>
                                <span class="kerry-best-badge hidden bg-emerald-600 text-white text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full shadow-sm">
                                    ★ ถูกที่สุด
                                </span>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base">Kerry Express</h3>
                            <p class="text-xs text-slate-500 mt-1">จัดส่งด่วนพิเศษ พรีเมียม</p>
                            <div class="mt-2 text-[11px] text-slate-400 font-mono">ระยะเวลา: 1-2 วันทำการ</div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100">
                            <div class="flex items-baseline justify-between mb-3">
                                <span class="text-xs text-slate-400">ราคารวมสุทธิ</span>
                                <span class="text-2xl font-black text-orange-600 font-display kerry-cost-text">฿0.00</span>
                            </div>
                            <button type="button" class="btn-select-courier w-full py-3 px-4 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-md shadow-orange-500/20 transition flex items-center justify-center gap-1"
                                    data-provider="kerry">
                                <span>เลือก Kerry Express</span>
                                <span>➔</span>
                            </button>
                        </div>
                    </div>

                    <!-- Flash Card -->
                    <div class="courier-select-card p-5 rounded-3xl border-2 border-slate-200 bg-white hover:border-amber-500 hover:shadow-elevated transition flex flex-col justify-between cursor-pointer"
                         data-provider="flash">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-3xl">⚡</span>
                                <span class="flash-best-badge hidden bg-emerald-600 text-white text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full shadow-sm">
                                    ★ ถูกที่สุด
                                </span>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base">Flash Express</h3>
                            <p class="text-xs text-slate-500 mt-1">ประหยัดสำหรับพ่อค้าแม่ค้าออนไลน์</p>
                            <div class="mt-2 text-[11px] text-slate-400 font-mono">ระยะเวลา: 1-3 วันทำการ</div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100">
                            <div class="flex items-baseline justify-between mb-3">
                                <span class="text-xs text-slate-400">ราคารวมสุทธิ</span>
                                <span class="text-2xl font-black text-amber-600 font-display flash-cost-text">฿0.00</span>
                            </div>
                            <button type="button" class="btn-select-courier w-full py-3 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition flex items-center justify-center gap-1"
                                    data-provider="flash">
                                <span>เลือก Flash Express</span>
                                <span>➔</span>
                            </button>
                        </div>
                    </div>

                    <!-- EMS Card -->
                    <div class="courier-select-card p-5 rounded-3xl border-2 border-slate-200 bg-white hover:border-red-500 hover:shadow-elevated transition flex flex-col justify-between cursor-pointer"
                         data-provider="ems">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-3xl">📮</span>
                                <span class="ems-best-badge hidden bg-emerald-600 text-white text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full shadow-sm">
                                    ★ ถูกที่สุด
                                </span>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base">ไปรษณีย์ไทย EMS</h3>
                            <p class="text-xs text-slate-500 mt-1">เข้าถึงทุกหมู่บ้านและเกาะห่างไกล</p>
                            <div class="mt-2 text-[11px] text-slate-400 font-mono">ระยะเวลา: 1-2 วันทำการ</div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100">
                            <div class="flex items-baseline justify-between mb-3">
                                <span class="text-xs text-slate-400">ราคารวมสุทธิ</span>
                                <span class="text-2xl font-black text-red-600 font-display ems-cost-text">฿0.00</span>
                            </div>
                            <button type="button" class="btn-select-courier w-full py-3 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-md shadow-red-600/20 transition flex items-center justify-center gap-1"
                                    data-provider="ems">
                                <span>เลือก ไปรษณีย์ไทย EMS</span>
                                <span>➔</span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>


        <!-- =================================================================== -->
        <!-- STEP 3: OFFICIAL ITEMIZED LOGISTICS INVOICE / RECEIPT (ใบแจกแจงค่าบริการ) -->
        <!-- =================================================================== -->
        <div id="stepSection3" class="step-section <?= ($currentStep !== 3) ? 'hidden' : '' ?> space-y-6">
            
            <div class="print-card bg-white p-6 sm:p-9 rounded-3xl border border-slate-200/90 shadow-receipt space-y-6 relative overflow-hidden">
                
                <!-- Background Watermark Decoration -->
                <div class="no-print absolute -right-10 -top-10 w-48 h-48 bg-indigo-100/50 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Invoice Official Header -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-slate-200">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ✓ ตรวจสอบและประเมินสำเร็จ
                            </span>
                            <span class="text-[11px] font-mono text-slate-400">
                                TAX ESTIMATE & BREAKDOWN
                            </span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 font-display flex items-center gap-2.5">
                            <span id="receiptProviderName">Kerry Express 📦</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            ใบแจ้งรายการและแจกแจงค่าบริการจัดส่งพัสดุมาตรฐานสากล (Itemized Logistics Invoice)
                        </p>
                    </div>

                    <div class="text-left sm:text-right text-xs space-y-1 bg-slate-50 sm:bg-transparent p-3 sm:p-0 rounded-xl border sm:border-0 border-slate-200 w-full sm:w-auto">
                        <div>
                            <span class="text-slate-400">เลขอ้างอิงการประเมิน: </span>
                            <strong class="font-mono text-slate-800" id="receiptRefNo">INV-2026-<?= strtoupper(substr(md5((string)time()), 0, 7)) ?></strong>
                        </div>
                        <div>
                            <span class="text-slate-400">วันที่ประเมิน: </span>
                            <span class="font-mono text-slate-700" id="receiptDate"><?= date('d/m/Y H:i:s') ?></span>
                        </div>
                        <div class="text-emerald-700 font-semibold text-[11px]">
                            สถานะ: พร้อมออกบิลขนส่ง
                        </div>
                    </div>
                </div>

                <!-- Route & Parcel Logistics Specs -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px]">เส้นทางจัดส่ง</span>
                        <strong class="text-slate-900 text-sm block truncate" id="receiptRoute">กรุงเทพฯ → อุทัยธานี</strong>
                        <span class="text-[10px] text-indigo-600 font-mono font-bold" id="receiptDistanceBadge">~235 กม.</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px]">มิติกล่อง (W×L×H)</span>
                        <strong class="text-slate-900 font-mono text-sm block" id="receiptDims">14×20×12 cm</strong>
                        <span class="text-[10px] text-slate-500 font-mono" id="receiptVolWeightBadge">Vol: 0.67 kg</span>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
                        <span class="text-slate-400 block text-[11px]">น้ำหนักชั่งจริง</span>
                        <strong class="text-slate-900 font-mono text-sm block" id="receiptActualWeight">1.50 kg</strong>
                        <span class="text-[10px] text-slate-400 font-mono">Actual Scale</span>
                    </div>

                    <div class="p-3.5 bg-indigo-50/80 rounded-2xl border border-indigo-200">
                        <span class="text-indigo-700 block text-[11px] font-bold">น้ำหนักคิดเงินจริง</span>
                        <strong class="text-indigo-950 font-mono font-black text-base block" id="receiptChargeableWeight">1.50 kg</strong>
                        <span class="text-[10px] text-indigo-600 font-semibold" id="receiptWeightReason">คิดตามน้ำหนักจริง</span>
                    </div>
                </div>

                <!-- Itemized Cost Breakdown Table -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                            ตารางแจกแจงค่าบริการอย่างละเอียด (Itemized Service Fees)
                        </h3>
                        <span class="text-[11px] text-slate-400 font-mono">สกุลเงิน: บาท (THB)</span>
                    </div>

                    <div class="border border-slate-200 rounded-2xl overflow-hidden divide-y divide-slate-100 shadow-sm" id="receiptItemsContainer">
                        <!-- Rendered by client JS / SSR -->
                        <?php if ($serverResult): ?>
                            <?php foreach ($serverResult['breakdown']->getItems() as $item): ?>
                                <div class="flex items-center justify-between p-3.5 bg-white text-xs hover:bg-slate-50 transition">
                                    <div>
                                        <span class="text-slate-800 font-semibold"><?= htmlspecialchars($item['label']) ?></span>
                                        <?php if (!empty($item['note'])): ?>
                                            <span class="block text-[10px] text-slate-400"><?= htmlspecialchars($item['note']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="font-mono font-bold text-slate-900 text-sm">฿<?= number_format($item['amount'], 2) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Subtotal, VAT 7% and Grand Total Financial Block -->
                <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white space-y-4 shadow-elevated">
                    <div class="space-y-2 text-xs border-b border-slate-700/80 pb-3">
                        <div class="flex items-center justify-between text-slate-300">
                            <span>ยอดรวมค่าบริการขนส่ง (Subtotal):</span>
                            <span class="font-mono font-bold text-white text-sm" id="receiptSubtotal">฿0.00</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>ภาษีมูลค่าเพิ่ม VAT 7%:</span>
                            <span class="font-mono font-medium text-slate-300" id="receiptVat">฿0.00</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div>
                            <span class="text-xs text-indigo-200 block uppercase tracking-wider font-bold">ยอดสุทธิที่ต้องชำระ (Grand Total)</span>
                            <span class="text-[11px] text-slate-400">รวมค่าขนส่งตามระยะทางจริงและภาษีมูลค่าเพิ่มเรียบร้อย</span>
                        </div>
                        <div class="text-right">
                            <span class="text-3xl sm:text-4xl font-black font-display text-emerald-400 tracking-tight" id="receiptTotalAmount">
                                ฿0.00
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Logistics Compliance Badge -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-[11px] text-slate-500 flex items-center gap-2">
                    <span class="text-base">🛡️</span>
                    <span>รับประกันการคำนวณมาตรฐาน: อัตราค่าบริการเป็นไปตามมาตรฐานการกำหนดราคาพัสดุแห่งประเทศไทย (TH Logistics Standards)</span>
                </div>

                <!-- Action Controls (No Print) -->
                <div class="no-print flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-slate-100">
                    <button type="button" id="btnPrintReceipt" class="w-full sm:w-1/3 py-3.5 px-4 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                        <span>🖨️ พิมพ์ใบเสร็จ (Print)</span>
                    </button>

                    <button type="button" id="btnCopySummary" class="w-full sm:w-1/3 py-3.5 px-4 rounded-2xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center justify-center gap-2">
                        <span>📋 คัดลอกสรุปรายการ</span>
                    </button>

                    <button type="button" id="btnBackToStep2" class="w-full sm:w-1/3 py-3.5 px-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                        <span>← เปลี่ยนบริษัทขนส่ง</span>
                    </button>
                </div>

                <div class="no-print text-center pt-2">
                    <button type="button" id="btnResetToStep1" class="text-xs text-slate-400 hover:text-slate-600 underline font-medium">
                        🔄 ต้องการคำนวณพัสดุชิ้นใหม่
                    </button>
                </div>

            </div>

        </div>

    </form>

</div>

<!-- ⚡ Client-side Logistics Engine with Real-world 77 Provinces & Strict Step Guard -->
<script>
// Load 77 Provinces Dataset from PHP backend
const PROVINCES = <?= json_encode($allProvinces, JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', () => {
    // Stepper elements
    const tabStep1 = document.getElementById('tabStep1');
    const tabStep2 = document.getElementById('tabStep2');
    const tabStep3 = document.getElementById('tabStep3');
    const stepSection1 = document.getElementById('stepSection1');
    const stepSection2 = document.getElementById('stepSection2');
    const stepSection3 = document.getElementById('stepSection3');

    // Autocomplete Province Combobox Elements
    const originInput = document.getElementById('originInput');
    const originCode  = document.getElementById('originCode');
    const originDropdownList = document.getElementById('originDropdownList');
    const originClearBtn = document.getElementById('originClearBtn');
    const originToggleBtn = document.getElementById('originToggleBtn');

    const destInput = document.getElementById('destInput');
    const destCode  = document.getElementById('destCode');
    const destDropdownList = document.getElementById('destDropdownList');
    const destClearBtn = document.getElementById('destClearBtn');
    const destToggleBtn = document.getElementById('destToggleBtn');

    const btnSwapRoute = document.getElementById('btnSwapRoute');
    const routePresetBtns = document.querySelectorAll('.route-preset-btn');
    const weightInput = document.getElementById('weightInput');
    const weightSlider = document.getElementById('weightSlider');
    const widthInput = document.getElementById('widthInput');
    const lengthInput = document.getElementById('lengthInput');
    const heightInput = document.getElementById('heightInput');
    const boxPresets = document.querySelectorAll('.box-preset-btn');
    const formProvider = document.getElementById('formProvider');
    const formStep = document.getElementById('formStep');

    // Aliases for popular Thai search terms & abbreviations
    const PROVINCE_ALIASES = {
        'กทม': 'TH-10',
        'กทม.': 'TH-10',
        'กรุงเทพ': 'TH-10',
        'บางกอก': 'TH-10',
        'โคราช': 'TH-30',
        'อยุธยา': 'TH-14',
        'แปดริ้ว': 'TH-24',
        'เมืองชล': 'TH-20',
        'หาดใหญ่': 'TH-90',
        'หัวหิน': 'TH-77',
        'พัทยา': 'TH-20',
    };

    // Reusable Autocomplete & Searchable Combobox Controller
    function setupSearchableProvinceCombobox({
        inputEl,
        hiddenCodeEl,
        dropdownEl,
        clearBtn,
        toggleBtn,
        onSelect
    }) {
        let activeIndex = -1;
        let filteredList = [];
        let isOpen = false;

        function getProvinceZoneBadge(zone) {
            if (zone === 'remote') {
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">🏝️ พิเศษ/เกาะ</span>';
            }
            if (zone === 'bkk') {
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">🏙️ ปริมณฑล</span>';
            }
            return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">🏞️ ตจว.</span>';
        }

        function highlightMatch(text, query) {
            if (!query) return text;
            const idx = text.toLowerCase().indexOf(query.toLowerCase());
            if (idx === -1) return text;
            return text.substring(0, idx) +
                   `<mark class="bg-amber-200 text-slate-900 font-extrabold px-0.5 rounded">${text.substring(idx, idx + query.length)}</mark>` +
                   text.substring(idx + query.length);
        }

        function renderDropdown(query = '') {
            dropdownEl.innerHTML = '';
            filteredList = [];

            const q = query.trim().toLowerCase();

            // Check aliases
            let aliasCode = null;
            if (q && PROVINCE_ALIASES[q]) {
                aliasCode = PROVINCE_ALIASES[q];
            }

            const entries = Object.entries(PROVINCES);
            
            if (aliasCode && PROVINCES[aliasCode]) {
                filteredList.push({ code: aliasCode, ...PROVINCES[aliasCode], isAlias: true });
            }

            entries.forEach(([code, p]) => {
                if (aliasCode === code) return;

                if (!q) {
                    filteredList.push({ code, ...p });
                } else {
                    const matchThai = p.name.toLowerCase().includes(q);
                    const matchEn   = p.name_en.toLowerCase().includes(q);
                    const matchCode = code.toLowerCase().includes(q);
                    if (matchThai || matchEn || matchCode) {
                        filteredList.push({ code, ...p });
                    }
                }
            });

            if (filteredList.length === 0) {
                dropdownEl.innerHTML = `
                    <div class="p-5 text-center text-xs text-slate-500">
                        <span class="text-base block mb-1">🔍</span>
                        <span class="font-bold text-slate-700">ไม่พบจังหวัดที่ค้นหา "${query}"</span>
                        <div class="text-[10px] text-slate-400 mt-1">ลองพิมพ์ชื่อย่อ เช่น กทม, โคราช หรือค้นหาด้วยภาษาอังกฤษ</div>
                    </div>
                `;
                return;
            }

            // Render items
            filteredList.forEach((item, index) => {
                const itemBtn = document.createElement('div');
                itemBtn.className = `w-full text-left px-4 py-2.5 flex items-center justify-between hover:bg-indigo-50/80 transition cursor-pointer text-xs select-none ${
                    index === activeIndex ? 'bg-indigo-50 border-l-4 border-indigo-600 font-bold' : ''
                } ${item.code === hiddenCodeEl.value ? 'bg-slate-50' : ''}`;

                const thaiHighlighted = highlightMatch(item.name, q);
                const enHighlighted   = highlightMatch(item.name_en, q);
                const isSelected = item.code === hiddenCodeEl.value;

                itemBtn.innerHTML = `
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">${item.zone === 'remote' ? '🏝️' : (item.zone === 'bkk' ? '🏙️' : '📍')}</span>
                        <div>
                            <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span>${thaiHighlighted}</span>
                                <span class="text-slate-400 font-normal text-[11px]">(${enHighlighted})</span>
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono">
                                รหัส: ${item.code} ${item.isAlias ? '• จากคำค้นย่อ' : ''}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        ${getProvinceZoneBadge(item.zone)}
                        ${isSelected ? '<span class="text-indigo-600 font-black text-sm">✓</span>' : ''}
                    </div>
                `;

                itemBtn.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectItem(item.code);
                });

                dropdownEl.appendChild(itemBtn);
            });
        }

        function openDropdown() {
            isOpen = true;
            activeIndex = -1;
            renderDropdown(inputEl.value);
            dropdownEl.classList.remove('hidden');
            
            setTimeout(() => {
                const selectedEl = dropdownEl.querySelector('.bg-slate-50');
                if (selectedEl) {
                    selectedEl.scrollIntoView({ block: 'nearest' });
                }
            }, 20);
        }

        function closeDropdown() {
            isOpen = false;
            dropdownEl.classList.add('hidden');
            const currentCode = hiddenCodeEl.value;
            if (PROVINCES[currentCode]) {
                inputEl.value = PROVINCES[currentCode].name;
            }
        }

        function selectItem(code) {
            if (!PROVINCES[code]) return;
            hiddenCodeEl.value = code;
            inputEl.value = PROVINCES[code].name;
            closeDropdown();
            if (typeof onSelect === 'function') {
                onSelect(code);
            }
        }

        // Event listeners
        inputEl.addEventListener('focus', () => {
            inputEl.select();
            openDropdown();
        });

        inputEl.addEventListener('input', () => {
            if (!isOpen) {
                dropdownEl.classList.remove('hidden');
                isOpen = true;
            }
            activeIndex = 0;
            renderDropdown(inputEl.value);
        });

        inputEl.addEventListener('keydown', (e) => {
            if (!isOpen) {
                if (e.key === 'ArrowDown' || e.key === 'Enter') {
                    openDropdown();
                    return;
                }
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (filteredList.length > 0) {
                    activeIndex = (activeIndex + 1) % filteredList.length;
                    renderDropdown(inputEl.value);
                    const items = dropdownEl.children;
                    if (items[activeIndex]) {
                        items[activeIndex].scrollIntoView({ block: 'nearest' });
                    }
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (filteredList.length > 0) {
                    activeIndex = (activeIndex - 1 + filteredList.length) % filteredList.length;
                    renderDropdown(inputEl.value);
                    const items = dropdownEl.children;
                    if (items[activeIndex]) {
                        items[activeIndex].scrollIntoView({ block: 'nearest' });
                    }
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (filteredList.length > 0) {
                    const chosen = activeIndex >= 0 ? filteredList[activeIndex] : filteredList[0];
                    if (chosen) {
                        selectItem(chosen.code);
                    }
                } else {
                    closeDropdown();
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        inputEl.addEventListener('blur', () => {
            setTimeout(() => {
                if (isOpen) closeDropdown();
            }, 200);
        });

        clearBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            inputEl.value = '';
            inputEl.focus();
            openDropdown();
        });

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (isOpen) {
                closeDropdown();
            } else {
                inputEl.focus();
                openDropdown();
            }
        });

        return {
            setProvince: (code) => {
                if (PROVINCES[code]) {
                    hiddenCodeEl.value = code;
                    inputEl.value = PROVINCES[code].name;
                }
            }
        };
    }

    // Toast element
    const toastAlert = document.getElementById('toastAlert');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');

    // Step 1 Route status banner displays
    const routeOriginNameDisplay = document.getElementById('routeOriginNameDisplay');
    const routeDestNameDisplay = document.getElementById('routeDestNameDisplay');
    const routeZoneBadge = document.getElementById('routeZoneBadge');
    const routeDistanceDisplay = document.getElementById('routeDistanceDisplay');
    const chargeableWeightDisplay = document.getElementById('chargeableWeightDisplay');
    const chargeableReasonDisplay = document.getElementById('chargeableReasonDisplay');

    // Step 2 elements
    const summaryRouteText = document.getElementById('summaryRouteText');
    const summaryDistText = document.getElementById('summaryDistText');
    const summaryWeightText = document.getElementById('summaryWeightText');
    const courierSelectButtons = document.querySelectorAll('.btn-select-courier');
    const courierCards = document.querySelectorAll('.courier-select-card');

    // Step 3 elements
    const receiptProviderName = document.getElementById('receiptProviderName');
    const receiptRoute = document.getElementById('receiptRoute');
    const receiptDistanceBadge = document.getElementById('receiptDistanceBadge');
    const receiptDims = document.getElementById('receiptDims');
    const receiptVolWeightBadge = document.getElementById('receiptVolWeightBadge');
    const receiptActualWeight = document.getElementById('receiptActualWeight');
    const receiptChargeableWeight = document.getElementById('receiptChargeableWeight');
    const receiptWeightReason = document.getElementById('receiptWeightReason');
    const receiptItemsContainer = document.getElementById('receiptItemsContainer');
    const receiptSubtotal = document.getElementById('receiptSubtotal');
    const receiptVat = document.getElementById('receiptVat');
    const receiptTotalAmount = document.getElementById('receiptTotalAmount');

    // Navigation Buttons
    const btnGoToStep2 = document.getElementById('btnGoToStep2');
    const btnBackToStep1 = document.getElementById('btnBackToStep1');
    const btnBackToStep2 = document.getElementById('btnBackToStep2');
    const btnResetToStep1 = document.getElementById('btnResetToStep1');
    const btnPrintReceipt = document.getElementById('btnPrintReceipt');
    const btnCopySummary = document.getElementById('btnCopySummary');

    // Strict Step Guard State
    let step1Validated = true;   // Step 1 is active
    let step2Unlocked  = false;  // Step 2 unlocks ONLY after confirming Step 1
    let step3Unlocked  = false;  // Step 3 unlocks ONLY after choosing courier in Step 2

    // Toast trigger helper
    let toastTimeout = null;
    function showToast(title, message) {
        toastTitle.textContent = title;
        toastMessage.textContent = message;
        toastAlert.classList.remove('hidden');
        setTimeout(() => {
            toastAlert.classList.remove('opacity-0', 'translate-y-2');
        }, 10);

        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toastAlert.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toastAlert.classList.add('hidden'), 300);
        }, 3000);
    }

    // Road distance calculation using Haversine formula + Thailand road winding factor (1.25)
    function calculateRoadDistance(originCode, destCode) {
        if (originCode === destCode) {
            return 25.0; // Same province delivery
        }

        const p1 = PROVINCES[originCode];
        const p2 = PROVINCES[destCode];
        if (!p1 || !p2) return 100.0;

        const R = 6371.0; // Earth radius in km
        const toRad = deg => (deg * Math.PI) / 180.0;
        const lat1 = toRad(p1.lat);
        const lon1 = toRad(p1.lon);
        const lat2 = toRad(p2.lat);
        const lon2 = toRad(p2.lon);

        const dLat = lat2 - lat1;
        const dLon = lon2 - lon1;

        const a = Math.sin(dLat / 2.0) * Math.sin(dLat / 2.0) +
                  Math.cos(lat1) * Math.cos(lat2) *
                  Math.sin(dLon / 2.0) * Math.sin(dLon / 2.0);

        const c = 2.0 * Math.asin(Math.sqrt(a));
        const straight = R * c;
        const road = straight * 1.25;

        return Math.round(Math.max(20.0, road));
    }

    // Carrier Fee Calculator Engines
    const carrierEngines = {
        kerry: {
            name: 'Kerry Express 📦',
            calculate: (w, dist, originCode, destCode) => {
                const base = 35.0;
                let weightFee = 0.0;
                let distFee = 0.0;
                let surcharge = 0.0;
                const items = [{
                    label: 'ค่าบริการพัสดุด่วนเริ่มต้น (พิกัด 1 kg แรก)',
                    amount: base,
                    note: 'ครอบคลุมบริการเข้ารับและส่งมอบ'
                }];

                // Weight fee
                if (w > 1.0 && w <= 3.0) {
                    weightFee = Math.ceil(w - 1.0) * 15.0;
                    items.push({
                        label: `ค่าน้ำหนักส่วนเกิน (${w.toFixed(2)} kg คิดเพิ่ม ฿15/kg)`,
                        amount: weightFee,
                        note: 'คิดจาก Chargeable Weight'
                    });
                } else if (w > 3.0 && w <= 5.0) {
                    weightFee = 30.0 + Math.ceil(w - 3.0) * 18.0;
                    items.push({
                        label: `ค่าน้ำหนักส่วนเกิน (${w.toFixed(2)} kg เรท 3-5 kg)`,
                        amount: weightFee,
                        note: 'คิดจาก Chargeable Weight'
                    });
                } else if (w > 5.0) {
                    weightFee = 66.0 + Math.ceil(w - 5.0) * 22.0;
                    items.push({
                        label: `ค่าน้ำหนักส่วนเกินระดับพัสดุใหญ่ (${w.toFixed(2)} kg)`,
                        amount: weightFee,
                        note: 'คิดจาก Chargeable Weight'
                    });
                }

                // Road Distance fee
                if (dist > 50.0) {
                    distFee = Math.ceil((dist - 50.0) / 100.0) * 10.0;
                    items.push({
                        label: `ค่าขนส่งตามระยะทางจริง (${dist} กม.)`,
                        amount: distFee,
                        note: `${PROVINCES[originCode].name} → ${PROVINCES[destCode].name}`
                    });
                }

                // Remote destination fee
                if (PROVINCES[destCode].zone === 'remote') {
                    surcharge = 50.0;
                    items.push({
                        label: 'ค่าธรรมเนียมพื้นที่ห่างไกล / ข้ามเกาะ / ชายแดนใต้',
                        amount: surcharge,
                        note: 'โซนบริการพิเศษ'
                    });
                }

                const subtotal = base + weightFee + distFee + surcharge;
                const vat = Math.round(subtotal * 0.07 * 100) / 100;
                const total = Math.round((subtotal + vat) * 100) / 100;

                return { base, weightFee, distFee, surcharge, subtotal, vat, total, items };
            }
        },
        flash: {
            name: 'Flash Express ⚡',
            calculate: (w, dist, originCode, destCode) => {
                const base = 28.0;
                let weightFee = 0.0;
                let distFee = 0.0;
                let surcharge = 0.0;
                const items = [{
                    label: 'ค่าบริการพัสดุราคาประหยัดเริ่มต้น (พิกัด 1 kg แรก)',
                    amount: base,
                    note: 'เรทพิเศษสำหรับร้านค้าออนไลน์'
                }];

                // Weight fee
                if (w > 1.0 && w <= 3.0) {
                    weightFee = Math.ceil(w - 1.0) * 12.0;
                    items.push({
                        label: `ค่าน้ำหนักส่วนเกิน (${w.toFixed(2)} kg คิดเพิ่ม ฿12/kg)`,
                        amount: weightFee,
                        note: 'คิดจาก Chargeable Weight'
                    });
                } else if (w > 3.0 && w <= 5.0) {
                    weightFee = 24.0 + Math.ceil(w - 3.0) * 15.0;
                    items.push({
                        label: `ค่าน้ำหนักส่วนเกิน (${w.toFixed(2)} kg เรท 3-5 kg)`,
                        amount: weightFee,
                        note: 'คิดจาก Chargeable Weight'
                    });
                } else if (w > 5.0) {
                    weightFee = 54.0 + Math.ceil(w - 5.0) * 18.0;
                    items.push({
                        label: `ค่าน้ำหนักส่วนเกินระดับพัสดุใหญ่ (${w.toFixed(2)} kg)`,
                        amount: weightFee,
                        note: 'คิดจาก Chargeable Weight'
                    });
                }

                // Road Distance fee
                if (dist > 50.0) {
                    distFee = Math.ceil((dist - 50.0) / 100.0) * 8.0;
                    items.push({
                        label: `ค่าขนส่งตามระยะทางจริง (${dist} กม.)`,
                        amount: distFee,
                        note: `${PROVINCES[originCode].name} → ${PROVINCES[destCode].name}`
                    });
                }

                // Remote destination fee
                if (PROVINCES[destCode].zone === 'remote') {
                    surcharge = 50.0;
                    items.push({
                        label: 'ค่าธรรมเนียมพื้นที่ห่างไกล / ข้ามเกาะ / ชายแดนใต้',
                        amount: surcharge,
                        note: 'โซนบริการพิเศษ'
                    });
                }

                const subtotal = base + weightFee + distFee + surcharge;
                const vat = Math.round(subtotal * 0.07 * 100) / 100;
                const total = Math.round((subtotal + vat) * 100) / 100;

                return { base, weightFee, distFee, surcharge, subtotal, vat, total, items };
            }
        },
        ems: {
            name: 'ไปรษณีย์ไทย EMS 📮',
            calculate: (w, dist, originCode, destCode) => {
                const base = 32.0;
                let weightFee = 0.0;
                let distFee = 0.0;
                let surcharge = 0.0;
                const items = [{
                    label: 'ค่าบริการ EMS ด่วนพิเศษเริ่มต้น (พิกัด 1 kg แรก)',
                    amount: base,
                    note: 'บริการมาตรฐานไปรษณีย์ไทย'
                }];

                // Weight fee
                if (w > 1.0 && w <= 2.0) {
                    weightFee = 10.0;
                    items.push({
                        label: `ค่าน้ำหนักส่วนเกิน (${w.toFixed(2)} kg เรท 1-2 kg)`,
                        amount: weightFee,
                        note: 'คิดจาก Chargeable Weight'
                    });
                } else if (w > 2.0 && w <= 5.0) {
                    weightFee = 10.0 + Math.ceil(w - 2.0) * 15.0;
                    items.push({
                        label: `ค่าน้ำหนักส่วนเกิน (${w.toFixed(2)} kg เรท 2-5 kg)`,
                        amount: weightFee,
                        note: 'คิดจาก Chargeable Weight'
                    });
                } else if (w > 5.0) {
                    weightFee = 55.0 + Math.ceil(w - 5.0) * 20.0;
                    items.push({
                        label: `ค่าน้ำหนักส่วนเกินระดับพัสดุใหญ่ (${w.toFixed(2)} kg)`,
                        amount: weightFee,
                        note: 'คิดจาก Chargeable Weight'
                    });
                }

                // Road Distance fee
                if (dist > 80.0) {
                    distFee = Math.ceil((dist - 80.0) / 150.0) * 6.0;
                    items.push({
                        label: `ค่าขนส่งตามระยะทางข้ามภูมิภาค (${dist} กม.)`,
                        amount: distFee,
                        note: `${PROVINCES[originCode].name} → ${PROVINCES[destCode].name}`
                    });
                }

                // Remote destination fee (friendly post rate)
                if (PROVINCES[destCode].zone === 'remote') {
                    surcharge = 20.0;
                    items.push({
                        label: 'ค่าบริการพื้นที่พิเศษ / เกาะ / ชายแดนใต้ (เรท ปณท. แห่งชาติ)',
                        amount: surcharge,
                        note: 'เข้าถึงครอบคลุมทุกรหัสไปรษณีย์'
                    });
                }

                const subtotal = base + weightFee + distFee + surcharge;
                const vat = Math.round(subtotal * 0.07 * 100) / 100;
                const total = Math.round((subtotal + vat) * 100) / 100;

                return { base, weightFee, distFee, surcharge, subtotal, vat, total, items };
            }
        }
    };

    // Strict Stepper Transitions
    function setStep(targetStep) {
        formStep.value = targetStep;

        // Show/hide sections
        [stepSection1, stepSection2, stepSection3].forEach((sec, idx) => {
            if (idx + 1 === targetStep) {
                sec.classList.remove('hidden');
            } else {
                sec.classList.add('hidden');
            }
        });

        // Update Tab visual statuses
        const tabs = [tabStep1, tabStep2, tabStep3];
        tabs.forEach((tab, idx) => {
            const stepNum = idx + 1;
            const badge = tab.querySelector('span');

            if (stepNum === targetStep) {
                tab.className = 'step-tab flex items-center justify-center gap-2 py-3 px-3 rounded-2xl transition-all bg-indigo-600 text-white shadow-md shadow-indigo-600/20';
                badge.className = 'w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-mono bg-white text-indigo-700 font-bold';
                badge.textContent = stepNum;
            } else if ((stepNum === 2 && step2Unlocked) || (stepNum === 3 && step3Unlocked) || stepNum === 1) {
                tab.className = 'step-tab flex items-center justify-center gap-2 py-3 px-3 rounded-2xl transition-all text-slate-700 hover:bg-slate-100 cursor-pointer';
                badge.className = 'w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-mono bg-slate-200 text-slate-700 font-bold';
                badge.textContent = stepNum;
            } else {
                tab.className = 'step-tab flex items-center justify-center gap-2 py-3 px-3 rounded-2xl transition-all step-tab-locked text-slate-400 bg-slate-50 cursor-not-allowed';
                badge.className = 'w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-mono bg-slate-200 text-slate-500';
                badge.textContent = '🔒';
            }
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Core Compute & Live UI Update
    function computeCurrentState() {
        const weight = Math.max(0.1, parseFloat(weightInput.value) || 0.1);
        const width  = Math.max(1.0, parseFloat(widthInput.value)  || 1.0);
        const length = Math.max(1.0, parseFloat(lengthInput.value) || 1.0);
        const height = Math.max(1.0, parseFloat(heightInput.value) || 1.0);
        const origin = originCode.value || 'TH-10';
        const dest   = destCode.value   || 'TH-61';

        const volumetric = Math.round(((width * length * height) / 5000.0) * 100) / 100;
        const chargeable = Math.max(weight, volumetric);
        const distanceKm = calculateRoadDistance(origin, dest);

        // Step 1 Route status banner update
        const pOrigin = PROVINCES[origin] || PROVINCES['TH-10'];
        const pDest   = PROVINCES[dest]   || PROVINCES['TH-61'];

        routeOriginNameDisplay.textContent = pOrigin.name;
        routeDestNameDisplay.textContent   = pDest.name;
        routeDistanceDisplay.textContent   = distanceKm;

        if (origin === dest) {
            routeZoneBadge.textContent = 'จัดส่งภายในจังหวัดเดียวกัน (Intra-province rate)';
            routeZoneBadge.className = 'text-emerald-700 font-semibold text-[11px] mt-0.5';
        } else if (pDest.zone === 'remote') {
            routeZoneBadge.textContent = '🏝️ ปลายทางเป็นพื้นที่พิเศษ/เกาะ/ชายแดนใต้ (Remote Surcharge)';
            routeZoneBadge.className = 'text-amber-700 font-bold text-[11px] mt-0.5';
        } else {
            routeZoneBadge.textContent = 'ขนส่งข้ามภูมิภาค • คิดเรทตามระยะทางจริง';
            routeZoneBadge.className = 'text-slate-500 text-[11px] mt-0.5';
        }

        // Chargeable weight display
        chargeableWeightDisplay.textContent = `${chargeable.toFixed(2)} kg`;
        if (volumetric > weight) {
            chargeableReasonDisplay.textContent = `(คิดตามขนาดกล่องใหญ่ ${volumetric.toFixed(2)} kg)`;
            chargeableReasonDisplay.className = "block text-[10px] text-amber-600 font-bold";
        } else {
            chargeableReasonDisplay.textContent = "(คิดตามน้ำหนักชั่งจริง)";
            chargeableReasonDisplay.className = "block text-[10px] text-indigo-600 font-bold";
        }

        // Compute carrier quotes
        const quotes = {
            kerry: carrierEngines.kerry.calculate(chargeable, distanceKm, origin, dest),
            flash: carrierEngines.flash.calculate(chargeable, distanceKm, origin, dest),
            ems:   carrierEngines.ems.calculate(chargeable, distanceKm, origin, dest),
        };

        // Determine lowest cost provider
        let minKey = 'kerry';
        Object.keys(quotes).forEach(k => {
            if (quotes[k].total < quotes[minKey].total) minKey = k;
        });

        // Update Step 2 Card prices & badges
        ['kerry', 'flash', 'ems'].forEach(k => {
            const costEl  = document.querySelector(`.${k}-cost-text`);
            const badgeEl = document.querySelector(`.${k}-best-badge`);
            if (costEl) costEl.textContent = `฿${quotes[k].total.toFixed(2)}`;
            if (badgeEl) {
                if (k === minKey) badgeEl.classList.remove('hidden');
                else badgeEl.classList.add('hidden');
            }
        });

        // Step 2 summary badge
        summaryRouteText.textContent  = `${pOrigin.name} ➔ ${pDest.name}`;
        summaryDistText.textContent   = `${distanceKm} กม.`;
        summaryWeightText.textContent = `${chargeable.toFixed(2)} kg`;

        return {
            weight, width, length, height, origin, dest,
            volumetric, chargeable, distanceKm, quotes,
            pOrigin, pDest
        };
    }

    // Render Official Logistics Receipt (Step 3)
    function renderReceipt(providerKey) {
        const data = computeCurrentState();
        const quote = data.quotes[providerKey] || data.quotes['kerry'];
        formProvider.value = providerKey;

        // Header info
        receiptProviderName.textContent = carrierEngines[providerKey].name;
        receiptRoute.textContent = `${data.pOrigin.name} ➔ ${data.pDest.name}`;
        receiptDistanceBadge.textContent = `~${data.distanceKm} กม.`;
        receiptDims.textContent = `${data.width}×${data.length}×${data.height} cm`;
        receiptVolWeightBadge.textContent = `Vol: ${data.volumetric.toFixed(2)} kg`;
        receiptActualWeight.textContent = `${data.weight.toFixed(2)} kg`;
        receiptChargeableWeight.textContent = `${data.chargeable.toFixed(2)} kg`;
        receiptWeightReason.textContent = (data.volumetric > data.weight) ? 'คิดตามขนาดกล่อง' : 'คิดตามน้ำหนักจริง';

        // Render line items
        receiptItemsContainer.innerHTML = '';
        quote.items.forEach(item => {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between p-3.5 bg-white text-xs hover:bg-slate-50 transition';
            row.innerHTML = `
                <div>
                    <span class="text-slate-800 font-semibold">${item.label}</span>
                    ${item.note ? `<span class="block text-[10px] text-slate-400 mt-0.5">${item.note}</span>` : ''}
                </div>
                <span class="font-mono font-bold text-slate-900 text-sm">฿${item.amount.toFixed(2)}</span>
            `;
            receiptItemsContainer.appendChild(row);
        });

        // Financial totals
        receiptSubtotal.textContent = `฿${quote.subtotal.toFixed(2)}`;
        receiptVat.textContent = `฿${quote.vat.toFixed(2)}`;
        receiptTotalAmount.textContent = `฿${quote.total.toFixed(2)}`;
    }

    // ==========================================
    // STRICT STEP GUARD NAVIGATION LISTENERS
    // ==========================================

    // Step 1 ➔ Step 2 Trigger
    btnGoToStep2.addEventListener('click', () => {
        computeCurrentState();
        step2Unlocked = true; // Unlock Step 2
        setStep(2);
    });

    // Step 2 ➔ Step 3 Trigger (Select Courier)
    courierSelectButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const provider = btn.getAttribute('data-provider');
            step3Unlocked = true; // Unlock Step 3
            renderReceipt(provider);
            setStep(3);
            if (typeof confetti === 'function') {
                confetti({ particleCount: 35, spread: 60, origin: { y: 0.7 } });
            }
        });
    });

    courierCards.forEach(card => {
        card.addEventListener('click', () => {
            const provider = card.getAttribute('data-provider');
            step3Unlocked = true; // Unlock Step 3
            renderReceipt(provider);
            setStep(3);
            if (typeof confetti === 'function') {
                confetti({ particleCount: 35, spread: 60, origin: { y: 0.7 } });
            }
        });
    });

    // Backward Navigation
    btnBackToStep1.addEventListener('click', () => setStep(1));
    btnBackToStep2.addEventListener('click', () => setStep(2));
    btnResetToStep1.addEventListener('click', () => setStep(1));

    // Tab Clicks with Strict Guard Protection
    tabStep1.addEventListener('click', () => setStep(1));

    tabStep2.addEventListener('click', () => {
        if (!step2Unlocked) {
            tabStep2.classList.add('animate-shake');
            setTimeout(() => tabStep2.classList.remove('animate-shake'), 400);
            showToast('ขั้นตอนที่ 2 ยังไม่เปิดใช้งาน 🔒', 'กรุณากดปุ่ม "ยืนยันข้อมูลพัสดุและเส้นทาง" ในขั้นตอนที่ 1 ก่อนครับ');
            return;
        }
        computeCurrentState();
        setStep(2);
    });

    tabStep3.addEventListener('click', () => {
        if (!step3Unlocked) {
            tabStep3.classList.add('animate-shake');
            setTimeout(() => tabStep3.classList.remove('animate-shake'), 400);
            showToast('ขั้นตอนที่ 3 ยังไม่เปิดใช้งาน 🔒', 'กรุณาเลือกบริษัทขนส่งที่คุณต้องการในขั้นตอนที่ 2 ก่อนครับ');
            return;
        }
        renderReceipt(formProvider.value || 'kerry');
        setStep(3);
    });

    // Initialize Searchable Province Combobox Controllers
    const originCombobox = setupSearchableProvinceCombobox({
        inputEl: originInput,
        hiddenCodeEl: originCode,
        dropdownEl: originDropdownList,
        clearBtn: originClearBtn,
        toggleBtn: originToggleBtn,
        onSelect: (code) => {
            computeCurrentState();
        }
    });

    const destCombobox = setupSearchableProvinceCombobox({
        inputEl: destInput,
        hiddenCodeEl: destCode,
        dropdownEl: destDropdownList,
        clearBtn: destClearBtn,
        toggleBtn: destToggleBtn,
        onSelect: (code) => {
            computeCurrentState();
        }
    });

    // Swap Origin & Destination
    btnSwapRoute.addEventListener('click', () => {
        const currentOrigin = originCode.value;
        const currentDest   = destCode.value;
        originCombobox.setProvince(currentDest);
        destCombobox.setProvince(currentOrigin);
        computeCurrentState();
        showToast('สลับเส้นทางสำเร็จ ⇄', `${PROVINCES[destCode.value].name} ➔ ${PROVINCES[originCode.value].name}`);
    });

    // Route Preset Buttons
    routePresetBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const o = btn.getAttribute('data-origin');
            const d = btn.getAttribute('data-dest');
            originCombobox.setProvince(o);
            destCombobox.setProvince(d);
            computeCurrentState();
            showToast('เลือกเส้นทางยอดนิยม 🚀', `${PROVINCES[o].name} ➔ ${PROVINCES[d].name}`);
        });
    });

    // Print Receipt
    btnPrintReceipt.addEventListener('click', () => {
        window.print();
    });

    // Copy Summary to Clipboard
    btnCopySummary.addEventListener('click', () => {
        const state = computeCurrentState();
        const prov = carrierEngines[formProvider.value || 'kerry'].name;
        const quote = state.quotes[formProvider.value || 'kerry'];
        const summaryText = `📦 สรุปค่าจัดส่งพัสดุ (${prov})\n` +
            `📍 เส้นทาง: ${state.pOrigin.name} ➔ ${state.pDest.name} (~${state.distanceKm} กม.)\n` +
            `📐 ขนาด: ${state.width}x${state.length}x${state.height} ซม. (Chargeable: ${state.chargeable.toFixed(2)} kg)\n` +
            `💰 ยอดรวมก่อนภาษี: ฿${quote.subtotal.toFixed(2)}\n` +
            `🧾 ภาษี VAT 7%: ฿${quote.vat.toFixed(2)}\n` +
            `💳 ยอดสุทธิทั้งสิ้น: ฿${quote.total.toFixed(2)}\n` +
            `🗓️ วันที่: ${document.getElementById('receiptDate').textContent}`;

        navigator.clipboard.writeText(summaryText).then(() => {
            showToast('คัดลอกสำเร็จ 📋', 'คัดลอกสรุปรายการจัดส่งไปยังคลิปบอร์ดแล้ว');
        }).catch(() => {
            showToast('คัดลอกไม่สำเร็จ', 'เบราว์เซอร์ไม่อนุญาตการเข้าถึงคลิปบอร์ด');
        });
    });

    // Weight slider sync
    weightSlider.addEventListener('input', () => {
        weightInput.value = weightSlider.value;
        computeCurrentState();
    });
    weightInput.addEventListener('input', () => {
        weightSlider.value = weightInput.value;
        computeCurrentState();
    });

    // Dimension inputs change
    [widthInput, lengthInput, heightInput].forEach(el => {
        el.addEventListener('input', computeCurrentState);
        el.addEventListener('change', computeCurrentState);
    });

    // Box Presets
    boxPresets.forEach(btn => {
        btn.addEventListener('click', () => {
            widthInput.value   = btn.getAttribute('data-w');
            lengthInput.value  = btn.getAttribute('data-l');
            heightInput.value  = btn.getAttribute('data-h');
            weightInput.value  = btn.getAttribute('data-weight');
            weightSlider.value = btn.getAttribute('data-weight');
            computeCurrentState();
        });
    });

    // Initial Calculation
    computeCurrentState();
    <?php if ($serverResult): ?>
        step2Unlocked = true;
        step3Unlocked = true;
        renderReceipt('<?= $selectedProvider ?>');
        setStep(3);
    <?php endif; ?>
});
</script>

</body>
</html>