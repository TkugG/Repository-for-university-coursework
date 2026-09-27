<?php
session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบคำนวณโภชนาการสัตว์เลี้ยง (Pet Nutrition Studio)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css?v=1.4">
</head>
<body>

    <!-- Header / Navbar -->
    <header class="app-header">
        <div class="header-container">
            <div class="header-brand">
                <div class="brand-logo-box">🐾</div>
                <div>
                    <div class="brand-title">Pet Nutrition Studio</div>
                    <div class="brand-subtitle">คำนวณอาหารและโภชนาการสัตว์เลี้ยง</div>
                </div>
            </div>

            <div class="user-profile-menu">
                <div id="guest-tools" style="display: flex; gap: 8px;">
                    <button type="button" class="btn-preset" onclick="openModal('login-modal')">เข้าสู่ระบบ</button>
                    <button type="button" class="btn-preset" onclick="openModal('register-modal')">สมัครสมาชิก</button>
                </div>
                <div id="user-tools" style="display: none; align-items: center; gap: 12px;">
                    <span id="user-display-name" class="user-name-tag"></span>
                    <button type="button" class="btn-preset" onclick="openMyPetsModal()">📋 รายการของฉัน</button>
                    <button type="button" class="btn-logout" onclick="logout()">ออกจากระบบ</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="main-container">
        <div class="studio-grid">
            
            <!-- Left Side: Input Form Card -->
            <div class="card-box">
                <div class="card-header">
                    <span class="card-title">📝 ข้อมูลสัตว์เลี้ยง</span>
                    <span class="badge-pink" id="badge-pet-info">-</span>
                </div>
                <div class="card-body">
                    <form id="petForm" oninput="runCalc()" onchange="runCalc()">
                        
                        <!-- 1. ข้อมูลสัตว์เลี้ยง -->
                        <div class="section-group">
                            <div class="section-title">1. ข้อมูลสัตว์เลี้ยง</div>
                            <div class="species-grid" style="margin-top: 8px;">
                                <label class="species-card active" id="tab-dog" onclick="setSpecies('dog')">
                                    <span class="species-icon">🐶</span>
                                    <div>
                                        <div class="species-name">สุนัข</div>
                                        <div class="species-desc">ทุกสายพันธุ์</div>
                                    </div>
                                </label>
                                <label class="species-card" id="tab-cat" onclick="setSpecies('cat')">
                                    <span class="species-icon">🐱</span>
                                    <div>
                                        <div class="species-name">แมว</div>
                                        <div class="species-desc">ทุกสายพันธุ์</div>
                                    </div>
                                </label>
                            </div>

                            <div class="form-row-2">
                                <div class="form-field">
                                    <label for="pet-name">ชื่อสัตว์เลี้ยง</label>
                                    <input type="text" id="pet-name" class="form-control" placeholder="ระบุชื่อ">
                                </div>
                                <div class="form-field">
                                    <label for="pet-weight">น้ำหนัก (kg)</label>
                                    <div class="number-input-group">
                                        <button type="button" onclick="stepWeight(-0.5)">-</button>
                                        <input type="number" step="0.1" id="pet-weight" placeholder="0.0" min="0.1">
                                        <span class="unit-text">กก.</span>
                                        <button type="button" onclick="stepWeight(0.5)">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-field">
                                <label>ช่วงวัย</label>
                                <div class="segmented-pill-group">
                                    <label class="pill-btn">
                                        <input type="radio" name="age-stage" value="pup">
                                        <span class="pill-box">ลูกสัตว์</span>
                                    </label>
                                    <label class="pill-btn">
                                        <input type="radio" name="age-stage" value="adult">
                                        <span class="pill-box">โตเต็มวัย</span>
                                    </label>
                                    <label class="pill-btn">
                                        <input type="radio" name="age-stage" value="senior">
                                        <span class="pill-box">สูงวัย</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-field">
                                <label>ระดับกิจกรรมประจำวัน</label>
                                <div class="segmented-pill-group">
                                    <label class="pill-btn">
                                        <input type="radio" name="activity-level" value="low">
                                        <span class="pill-box">กิจกรรมน้อย</span>
                                    </label>
                                    <label class="pill-btn">
                                        <input type="radio" name="activity-level" value="normal">
                                        <span class="pill-box">ปกติ</span>
                                    </label>
                                    <label class="pill-btn">
                                        <input type="radio" name="activity-level" value="high">
                                        <span class="pill-box">คึกคักมาก</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-row-2" style="align-items: center; margin-top: 10px;">
                                <div class="form-field" style="margin-bottom:0;">
                                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                        <input type="checkbox" id="is-neutered" value="1" style="width:16px; height:16px; accent-color:var(--primary-pink-dark);">
                                        <span>ทำหมันแล้ว</span>
                                    </label>
                                </div>
                                <div class="form-field" id="breed-size-container" style="margin-bottom:0;">
                                    <label for="breed-size">ขนาดสายพันธุ์</label>
                                    <select id="breed-size" class="form-control">
                                        <option value="">-- เลือกขนาด --</option>
                                        <option value="small">พันธุ์เล็ก (&lt; 10 kg)</option>
                                        <option value="medium">พันธุ์กลาง (10-25 kg)</option>
                                        <option value="large">พันธุ์ใหญ่ (&gt; 25 kg)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 2. ข้อมูลอาหาร -->
                        <div class="section-group">
                            <div class="section-title">2. ข้อมูลอาหาร</div>
                            <div class="section-sub">ระบุประเภทอาหารและพลังงานต่อ 100 กรัมตามฉลากจริง</div>

                            <div class="form-row-2">
                                <div class="form-field">
                                    <label>ประเภทอาหาร</label>
                                    <div class="segmented-pill-group">
                                        <label class="pill-btn">
                                            <input type="radio" name="food-type" value="dry">
                                            <span class="pill-box">อาหารเม็ด (Dry)</span>
                                        </label>
                                        <label class="pill-btn">
                                            <input type="radio" name="food-type" value="wet">
                                            <span class="pill-box">อาหารเปียก (Wet)</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label for="food-cal">พลังงาน (kcal / 100g)</label>
                                    <input type="number" id="food-cal" class="form-control" placeholder="0">
                                </div>
                            </div>

                            <!-- ปุ่ม Preset ค่าตามท้องตลาด พร้อมส่ง `this` ไปตั้งค่าสี Active -->
                            <div class="quick-presets-row" style="margin-top: 10px;">
                                <span class="preset-label">ค่าตามท้องตลาด:</span>
                                <button type="button" class="btn-chip" onclick="setKcal(360, 'dry', this)">เม็ดมาตรฐาน (360)</button>
                                <button type="button" class="btn-chip" onclick="setKcal(385, 'dry', this)">เม็ดลูกสัตว์ (385)</button>
                                <button type="button" class="btn-chip" onclick="setKcal(85, 'wet', this)">เปียกเพาช์ (85)</button>
                                <button type="button" class="btn-chip" onclick="setKcal(95, 'wet', this)">เปียกกระป๋อง (95)</button>
                            </div>
                        </div>

                        <!-- 3. จัดมื้ออาหาร -->
                        <div class="section-group">
                            <div class="section-title">3. จัดมื้ออาหาร</div>

                            <div class="form-row-2">
                                <div class="form-field">
                                    <label>จำนวนมื้อต่อวัน</label>
                                    <div class="segmented-pill-group">
                                        <label class="pill-btn">
                                            <input type="radio" name="meals-count" value="1">
                                            <span class="pill-box">1 มื้อ</span>
                                        </label>
                                        <label class="pill-btn">
                                            <input type="radio" name="meals-count" value="2">
                                            <span class="pill-box">2 มื้อ ⭐</span>
                                        </label>
                                        <label class="pill-btn">
                                            <input type="radio" name="meals-count" value="3">
                                            <span class="pill-box">3 มื้อ</span>
                                        </label>
                                        <label class="pill-btn">
                                            <input type="radio" name="meals-count" value="4">
                                            <span class="pill-box">4 มื้อ</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label for="start-time">มื้อแรกเวลา</label>
                                    <input type="time" id="start-time" class="form-control" value="08:00">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-button-group">
                            <button type="button" onclick="savePetData()" class="btn-submit-green">
                                💾 บันทึกข้อมูล
                            </button>
                            <button type="button" onclick="resetPetForm()" class="btn-secondary-action">
                                ➖ ล้างฟอร์ม
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Right Side: Dashboard Summary -->
            <div>
                <div class="card-box">
                    <div class="card-header">
                        <span class="card-title">📊 สรุปผลการคำนวณโภชนาการ</span>
                    </div>
                    <div class="card-body">
                        
                        <div class="metrics-grid">
                            <div class="metric-card pink">
                                <div class="metric-label">ปริมาณอาหารที่ต้องได้รับ</div>
                                <div class="metric-value" id="res-daily-grams">0</div>
                                <div class="metric-sub">กรัม / วัน</div>
                            </div>
                            <div class="metric-card mint">
                                <div class="metric-label">น้ำดื่มขั้นต่ำที่ต้องการ (<span id="res-water-name">-</span>)</div>
                                <div class="metric-value" id="res-water-val">0</div>
                                <div class="metric-sub">มล. / วัน</div>
                            </div>
                        </div>

                        <div class="timeline-box">
                            <div class="timeline-header">
                                <span>⏰ ตารางการให้อาหารประจำวัน</span>
                                <span id="res-meal-sub" style="font-weight:normal;">กรัม / มื้อ</span>
                            </div>
                            <div id="timeline-list"></div>
                        </div>

                        <div class="energy-box">
                            <div class="energy-title">⚡ การวิเคราะห์พลังงานแคลอรี (kcal)</div>
                            <div class="energy-grid">
                                <div class="energy-item">
                                    <div class="lbl">พลังงานพักผ่อน (RER)</div>
                                    <div class="val" id="res-rer">0</div>
                                </div>
                                <div class="energy-item">
                                    <div class="lbl">ตัวคูณกิจกรรม (Factor)</div>
                                    <div class="val" id="res-factor">0x</div>
                                </div>
                                <div class="energy-item highlight">
                                    <div class="lbl">พลังงานสุทธิ (DER)</div>
                                    <div class="val" id="res-der">0</div>
                                </div>
                            </div>
                        </div>

                        <div class="advice-banner">
                            💡 <strong>คำแนะนำ:</strong> ค่าที่คำนวณได้อ้างอิงตามมาตรฐาน AAFCO/NRC ควรคอยสังเกตน้ำหนักและรูปร่างสัตว์เลี้ยงทุก 2-4 สัปดาห์เพื่อปรับปริมาณอาหารตามความเหมาะสม
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Modals -->
    <div class="modal-overlay" id="login-modal">
        <div class="modal-card">
            <h3>เข้าสู่ระบบ</h3>
            <form onsubmit="handleLogin(event)">
                <div class="form-field">
                    <label for="login-email">อีเมล</label>
                    <input type="email" id="login-email" class="form-control" placeholder="name@example.com" required>
                </div>
                <div class="form-field">
                    <label for="login-password">รหัสผ่าน</label>
                    <input type="password" id="login-password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-submit-green" style="width:100%;">เข้าสู่ระบบ</button>
                <button type="button" class="btn-cancel" onclick="closeModal('login-modal')">ยกเลิก</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="register-modal">
        <div class="modal-card">
            <h3>สมัครสมาชิก</h3>
            <form onsubmit="handleRegister(event)">
                <div class="form-field">
                    <label for="reg-name">ชื่อ-นามสกุล</label>
                    <input type="text" id="reg-name" class="form-control" placeholder="ระบุชื่อของคุณ" required>
                </div>
                <div class="form-field">
                    <label for="reg-email">อีเมล</label>
                    <input type="email" id="reg-email" class="form-control" placeholder="name@example.com" required>
                </div>
                <div class="form-field">
                    <label for="reg-password">รหัสผ่าน</label>
                    <input type="password" id="reg-password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-submit-green" style="width:100%;">สมัครสมาชิก</button>
                <button type="button" class="btn-cancel" onclick="closeModal('register-modal')">ยกเลิก</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="mypets-modal">
        <div class="modal-card wide">
            <h3>🐾 รายการสัตว์เลี้ยงของฉัน</h3>
            <div id="pets-list-container" class="pets-grid-list"></div>
            <button type="button" class="btn-cancel" onclick="closeModal('mypets-modal')">ปิดหน้าต่าง</button>
        </div>
    </div>

    <footer class="app-footer">
        Pet Nutrition Studio &copy; 2026 - ระบบคำนวณโภชนาการสัตว์เลี้ยงตามหลักสัตวแพทย์
    </footer>

    <script src="assets/js/app.js?v=1.4"></script>
</body>
</html>