let currentSpecies = 'dog';

const foodCatalog = {
  dog: [
    { id: 'rc_med', name: 'Royal Canin Medium Adult', type: 'dry', kcal: 380 },
    { id: 'rc_mini', name: 'Royal Canin Mini Adult', type: 'dry', kcal: 384 },
    { id: 'pedigree', name: 'Pedigree Adult Chicken & Veg', type: 'dry', kcal: 340 },
    { id: 'smartheart', name: 'SmartHeart Gold Roast Beef', type: 'dry', kcal: 365 },
    { id: 'cesar', name: 'Cesar Classic Chicken Tray', type: 'wet', kcal: 95 },
    { id: 'custom', name: '✏️ ระบุยี่ห้ออื่น / กรอกเอง (Custom)', type: 'dry', kcal: 380 }
  ],
  cat: [
    { id: 'rc_in27', name: 'Royal Canin Indoor 27', type: 'dry', kcal: 375 },
    { id: 'rc_kitten', name: 'Royal Canin Kitten', type: 'dry', kcal: 409 },
    { id: 'me_o', name: 'Me-O Adult Tuna', type: 'dry', kcal: 360 },
    { id: 'whiskas_pouch', name: 'Whiskas Ocean Fish Pouch', type: 'wet', kcal: 85 },
    { id: 'sheba', name: 'Sheba Tuna Deluxe Wet Can', type: 'wet', kcal: 90 },
    { id: 'custom', name: '✏️ ระบุยี่ห้ออื่น / กรอกเอง (Custom)', type: 'wet', kcal: 85 }
  ]
};

// ── 1. ก้อนสัตว์เลี้ยง (Pet, Dog, Cat) ──
// ── UI CONTROLLER & EVENT LISTENERS ──

function setSpecies(species) {
  currentSpecies = species;
  const dogTab = document.getElementById('tab-dog');
  const catTab = document.getElementById('tab-cat');
  const dogField = document.getElementById('dog-specific-field');
  const catField = document.getElementById('cat-specific-field');
  const nameInput = document.getElementById('pet-name');
  const weightInput = document.getElementById('pet-weight');

  if (species === 'dog') {
    dogTab.classList.add('active');
    catTab.classList.remove('active');
    dogField.style.display = 'block';
    catField.style.display = 'none';
    if (nameInput.value === 'มิ้ว') nameInput.value = 'โอเว่น';
    if (parseFloat(weightInput.value) < 4) weightInput.value = '20.0';
  } else {
    catTab.classList.add('active');
    dogTab.classList.remove('active');
    catField.style.display = 'block';
    dogField.style.display = 'none';
    if (nameInput.value === 'โอเว่น') nameInput.value = 'มิ้ว';
    if (parseFloat(weightInput.value) > 10) weightInput.value = '4.0';
  }
  populateFoodCatalog();
  runCalculation(false);
}

function stepWeight(delta) {
  const input = document.getElementById('pet-weight');
  let val = Math.max(0.5, Math.round((parseFloat(input.value || 5) + delta) * 10) / 10);
  input.value = val.toFixed(1);
  runCalculation(false);
}

function populateFoodCatalog() {
  const select = document.getElementById('food-catalog-select');
  const items = foodCatalog[currentSpecies];
  select.innerHTML = items.map(item => `
    <option value="${item.id}" data-type="${item.type}" data-kcal="${item.kcal}">${item.name} (${item.kcal} kcal/100g)</option>
  `).join('');
  onFoodCatalogChange();
}

function onFoodCatalogChange() {
  const select = document.getElementById('food-catalog-select');
  const selectedOption = select.options[select.selectedIndex];
  if (!selectedOption) return;

  const kcal = selectedOption.getAttribute('data-kcal');
  document.getElementById('food-calories').value = kcal;
  runCalculation(false);
}

function loadPresetSample(preset) {
  if (preset === 'dog_ow') {
    setSpecies('dog');
    document.getElementById('pet-name').value = 'โอเว่น';
    document.getElementById('pet-weight').value = '20.0';
    document.getElementById('pet-bcs').value = '6'; // 6/9 เกินเกณฑ์ 10%
    document.querySelector('input[name="age-stage"][value="adult"]').checked = true;
    document.querySelector('input[name="activity-level"][value="normal"]').checked = true;
    document.getElementById('is-neutered').checked = true;
    document.getElementById('dog-breed-size').value = 'medium';
    document.getElementById('food-catalog-select').selectedIndex = 0;
    onFoodCatalogChange();
    document.getElementById('current-daily-kcal').value = '800';
    document.querySelector('input[name="meals-count"][value="2"]').checked = true;
    document.getElementById('first-meal-time').value = '08:00';
  } else if (preset === 'dog_normal') {
    setSpecies('dog');
    document.getElementById('pet-name').value = 'บัดดี้';
    document.getElementById('pet-weight').value = '10.0';
    document.getElementById('pet-bcs').value = '5'; // สมส่วน
    document.querySelector('input[name="age-stage"][value="adult"]').checked = true;
    document.querySelector('input[name="activity-level"][value="normal"]').checked = true;
    document.getElementById('is-neutered').checked = true;
    document.getElementById('dog-breed-size').value = 'medium';
    document.getElementById('food-catalog-select').selectedIndex = 0;
    onFoodCatalogChange();
    document.getElementById('current-daily-kcal').value = '630';
    document.querySelector('input[name="meals-count"][value="2"]').checked = true;
    document.getElementById('first-meal-time').value = '08:00';
  } else if (preset === 'cat_normal') {
    setSpecies('cat');
    document.getElementById('pet-name').value = 'มิ้ว';
    document.getElementById('pet-weight').value = '4.0';
    document.getElementById('pet-bcs').value = '5';
    document.querySelector('input[name="age-stage"][value="adult"]').checked = true;
    document.querySelector('input[name="activity-level"][value="normal"]').checked = true;
    document.getElementById('is-neutered').checked = true;
    document.getElementById('cat-living-habit').value = 'indoor';
    document.getElementById('food-catalog-select').selectedIndex = 0;
    onFoodCatalogChange();
    document.getElementById('current-daily-kcal').value = '200';
    document.querySelector('input[name="meals-count"][value="2"]').checked = true;
    document.getElementById('first-meal-time').value = '07:30';
  }
  runCalculation(true);
}

// ── CORE PNA CALCULATION & WORKSHEET SYNC ──
function runCalculation(triggerFeedback = true) {
  // 1. Gather Inputs
  const name = document.getElementById('pet-name').value || 'สัตว์เลี้ยง';
  const weight = Math.max(0.1, parseFloat(document.getElementById('pet-weight').value) || 20.0);
  const bcs = parseInt(document.getElementById('pet-bcs').value) || 6;
  const ageStage = document.querySelector('input[name="age-stage"]:checked')?.value || 'adult';
  const activityLevel = document.querySelector('input[name="activity-level"]:checked')?.value || 'normal';
  const isNeutered = document.getElementById('is-neutered').checked;

  let pet;
  if (currentSpecies === 'cat') {
    const habit = document.getElementById('cat-living-habit').value;
    pet = new Cat(name, weight, ageStage, activityLevel, isNeutered, habit);
  } else {
    const size = document.getElementById('dog-breed-size').value;
    pet = new Dog(name, weight, ageStage, activityLevel, isNeutered, size);
  }

  const catalogSelect = document.getElementById('food-catalog-select');
  const foodName = catalogSelect.options[catalogSelect.selectedIndex]?.text.split('(')[0].trim() || 'อาหารสัตว์เลี้ยง';
  const calories = Math.max(1, parseFloat(document.getElementById('food-calories').value) || 380);
  const food = new Food(foodName, calories > 150 ? 'dry' : 'wet', calories);

  const currentDailyKcal = parseFloat(document.getElementById('current-daily-kcal').value) || 0;
  const mealsCount = parseInt(document.querySelector('input[name="meals-count"]:checked')?.value || '2');
  const firstMealTime = document.getElementById('first-meal-time').value || '08:00';
  const schedule = new FeedingSchedule(mealsCount, firstMealTime);

  // 2. Compute PNA Standards: Ideal Weight & Recommended Kcal
  // BCS Scale 1-9: 5 is Ideal. Each point above 5 is roughly +10% over ideal weight.
  let idealWeight = weight;
  let weightStatusText = 'น้ำหนักสมส่วน';
  let badgeColorClass = 'green';
  let calorieAdviceText = 'รักษาระดับแคลอรี่ตามเกณฑ์แนะนำ';
  let recKcalFactor = 1.0;

  if (bcs === 6) {
    idealWeight = weight / 1.10;
    weightStatusText = 'น้ำหนักเกินเกณฑ์ 10%';
    badgeColorClass = 'yellow';
    calorieAdviceText = 'เราแนะนำให้ลดปริมาณแคลอรี่ลง 10%';
    recKcalFactor = 0.90;
  } else if (bcs === 7) {
    idealWeight = weight / 1.20;
    weightStatusText = 'น้ำหนักเกินเกณฑ์ 20% (ภาวะอ้วน)';
    badgeColorClass = 'yellow';
    calorieAdviceText = 'เราแนะนำให้ลดปริมาณแคลอรี่ลง 20%';
    recKcalFactor = 0.80;
  } else if (bcs >= 8) {
    idealWeight = weight / 1.30;
    weightStatusText = 'น้ำหนักเกินเกณฑ์ 30% (อ้วนมาก)';
    badgeColorClass = 'yellow';
    calorieAdviceText = 'เราแนะนำให้ลดปริมาณแคลอรี่ลง 20% และพบสัตวแพทย์';
    recKcalFactor = 0.80;
  } else if (bcs <= 4) {
    idealWeight = weight / (1 - ((5 - bcs) * 0.05));
    weightStatusText = 'น้ำหนักต่ำกว่าเกณฑ์';
    badgeColorClass = 'gray';
    calorieAdviceText = 'เราแนะนำให้เพิ่มปริมาณอาหาร 10-15%';
    recKcalFactor = 1.10;
  }

  // Energy: RER based on ideal weight (PNA Standard)
  const rerIdeal = FeedingCalculator.calculateRER(idealWeight);
  const derStandard = FeedingCalculator.calculateDER(pet);
  
  // PNA Standard: If currentDailyKcal is provided, recommended kcal modifies that baseline!
  // If not provided (or <= 0), it derives from DER standard of ideal weight!
  let recommendedKcal;
  if (currentDailyKcal > 0) {
    recommendedKcal = Math.round(currentDailyKcal * recKcalFactor);
  } else {
    recommendedKcal = Math.round(derStandard * recKcalFactor);
  }

  // Check 60% RER Safety Threshold (Like Image 1!)
  // If current kcal is entered and < 60% of RER ideal
  const isDangerouslyLow = currentDailyKcal > 0 && currentDailyKcal < (0.60 * rerIdeal);

  // Daily Grams Calculation
  const dailyGrams = isDangerouslyLow ? 0 : FeedingCalculator.calculateDailyPortion(pet, food, recommendedKcal);
  const portionPerMeal = schedule.calculatePortionPerMeal(dailyGrams);
  const slots = schedule.getMealSlots(dailyGrams);
  const cups = (dailyGrams / 90).toFixed(1);

  // 3. Update Screen UI
  document.getElementById('screen-pet-badge').textContent = `${pet.name} (${pet.getSpeciesLabel()} • ${weight} kg)`;
  document.getElementById('screen-val-bcs').textContent = `${bcs} จาก 9`;
  document.getElementById('screen-weight-curr').textContent = `${weight} กก.`;
  document.getElementById('screen-weight-ideal').textContent = `${idealWeight.toFixed(1)} กก.`;

  const screenStatusBadge = document.getElementById('screen-weight-status-badge');
  screenStatusBadge.textContent = weightStatusText;
  screenStatusBadge.className = `pna-badge ${badgeColorClass}`;

  document.getElementById('screen-kcal-curr').textContent = currentDailyKcal > 0 ? Math.round(currentDailyKcal) : '800';
  document.getElementById('screen-kcal-rec').textContent = isDangerouslyLow ? '--' : recommendedKcal;
  document.getElementById('screen-kcal-advice-badge').textContent = isDangerouslyLow ? 'แคลอรี่ต่ำเกินไป ไม่แนะนำ' : calorieAdviceText;

  if (isDangerouslyLow) {
    document.getElementById('screen-feeding-rec').innerHTML = `<span style="color:#C53030;">-- (${cups} ถ้วย)</span>`;
    document.getElementById('screen-warning-banner').style.display = 'block';
  } else {
    document.getElementById('screen-feeding-rec').innerHTML = `${dailyGrams.toFixed(1)} กรัมต่อวัน <span style="font-size:1.1rem;color:var(--text-secondary);font-weight:500;">(${cups} ถ้วย)</span>`;
    document.getElementById('screen-warning-banner').style.display = 'none';
  }
  document.getElementById('screen-food-name-label').textContent = `${food.name} (${food.caloriesPer100g} kcal/100g)`;

  // Timeline slots (Screen)
  const screenTimeline = document.getElementById('screen-timeline-list');
  screenTimeline.innerHTML = slots.map(slot => `
    <div class="timeline-mini-item">
      <span><strong>⏰ ${slot.time} น.</strong> — ${slot.mealName}</span>
      <span style="font-family:'Outfit';font-weight:700;color:var(--green-health);">${slot.portionGrams} กรัม</span>
    </div>
  `).join('');

  // 4. Sync to Dedicated Printable Worksheet (Matches Image 2 100%!)
  document.getElementById('print-pet-name').textContent = pet.name;
  
  // Format Thai Date e.g. 18 กันยายน 2026
  const thaiMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
  const now = new Date();
  const dateStr = `${now.getDate()} ${thaiMonths[now.getMonth()]} ${now.getFullYear()}`;
  document.getElementById('print-today-date').textContent = dateStr;

  document.getElementById('print-bcs-val').textContent = `${bcs} จาก 9`;
  document.getElementById('print-weight-curr').textContent = `${weight} กก.`;
  document.getElementById('print-weight-ideal').textContent = `${idealWeight.toFixed(1)} กก.`;
  document.getElementById('print-weight-status-badge').textContent = weightStatusText;

  document.getElementById('print-kcal-curr').textContent = currentDailyKcal > 0 ? Math.round(currentDailyKcal) : '800';
  document.getElementById('print-kcal-rec').textContent = isDangerouslyLow ? '--' : recommendedKcal;
  document.getElementById('print-kcal-advice-text').textContent = isDangerouslyLow ? 'แคลอรี่ต่ำเกินไป ไม่แนะนำ' : calorieAdviceText;

  if (isDangerouslyLow) {
    document.getElementById('print-feeding-rec-text').textContent = `-- (${cups} ถ้วย)`;
    document.getElementById('print-warning-banner').style.display = 'block';
  } else {
    document.getElementById('print-feeding-rec-text').textContent = `${dailyGrams.toFixed(1)} กรัมต่อวัน (${cups} ถ้วยต่อวัน)`;
    document.getElementById('print-warning-banner').style.display = 'none';
  }
  document.getElementById('print-food-name-text').textContent = `สูตรอาหาร: ${food.name} (${food.caloriesPer100g} kcal/100g)`;

  // Timeline slots (Print)
  const printTable = document.getElementById('print-schedule-table');
  printTable.innerHTML = slots.map(slot => `
    <tr>
      <td style="width:30%;font-weight:700;">⏰ ${slot.time} น.</td>
      <td style="width:35%;">${slot.mealName}</td>
      <td style="width:35%;text-align:right;font-family:'Outfit';font-weight:700;color:#15803D;">${slot.portionGrams} กรัม</td>
    </tr>
  `).join('');

  // Also sync modal preview content if modal is open
  const modalContent = document.getElementById('modal-report-content');
  if (modalContent && document.getElementById('report-modal').style.display === 'flex') {
    modalContent.innerHTML = document.getElementById('printable-worksheet').innerHTML;
  }

  if (triggerFeedback) {
    const btn = document.getElementById('btn-submit');
    btn.style.opacity = '0.85';
    setTimeout(() => { btn.style.opacity = '1'; }, 200);
  }
}

// ── REPORT MODAL HANDLERS ──
function openReportModal() {
  runCalculation(false);
  const worksheet = document.getElementById('printable-worksheet');
  const modalContent = document.getElementById('modal-report-content');
  modalContent.innerHTML = worksheet.innerHTML;
  document.getElementById('report-modal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeReportModal() {
  document.getElementById('report-modal').style.display = 'none';
  document.body.style.overflow = '';
}

function handleModalBackdropClick(event) {
  if (event.target.id === 'report-modal') {
    closeReportModal();
  }
}

// ── PRINT ACTION HANDLERS ──
function executePrint() {
  // Ensure calculation is 100% fresh and synced to print worksheet
  runCalculation(false);
  
  // Sync to modal if open
  const worksheet = document.getElementById('printable-worksheet');
  const modalContent = document.getElementById('modal-report-content');
  if (modalContent) {
    modalContent.innerHTML = worksheet.innerHTML;
  }
  
  // Trigger standard print dialog
  window.print();
}

function printViaDedicatedWindow() {
  runCalculation(false);
  const worksheetHtml = document.getElementById('printable-worksheet').innerHTML;
  const printWindow = window.open('', '_blank', 'width=900,height=950');
  if (!printWindow) {
    window.print();
    return;
  }
  printWindow.document.write(`<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Pet Nutrition Alliance - ${document.getElementById('print-pet-name').textContent}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Prompt:wght@400;500;600;700&family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Sarabun', -apple-system, sans-serif;
      color: #000;
      background: #fff;
      padding: 10mm 14mm;
      font-size: 10pt;
      line-height: 1.4;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    @page {
      size: A4 portrait;
      margin: 8mm 12mm;
    }
    .print-header-row { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
    .print-title { font-family: 'Prompt', sans-serif; font-size: 15pt; font-weight: 700; color: #000; }
    .print-date { font-size: 9.5pt; color: #444; margin-top: 3px; }
    .print-brand { text-align: right; }
    .print-brand-name { font-family: 'Outfit', sans-serif; font-size: 12.5pt; font-weight: 800; color: #000; }
    .print-brand-sub { font-size: 8pt; color: #555; }
    .print-alert-box { background: #FEE2E2 !important; border: 1.5px solid #DC2626 !important; color: #991B1B !important; padding: 10px 14px; border-radius: 6px; font-size: 9.5pt; line-height: 1.45; margin-bottom: 12px; }
    .print-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .print-table td { border: 1px solid #B0B0B0; padding: 8px 12px; vertical-align: middle; }
    .print-th-col { width: 28%; background: #F8F8F8 !important; font-family: 'Prompt', sans-serif; font-weight: 600; font-size: 9.5pt; color: #000; }
    .print-data-center { text-align: center; }
    .print-metric-sub { font-size: 8pt; color: #666; margin-bottom: 2px; }
    .print-metric-large { font-family: 'Outfit', sans-serif; font-size: 16pt; font-weight: 700; color: #000; line-height: 1.1; }
    .print-metric-large.green { color: #15803D !important; }
    .print-badge-pill { display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 8.5pt; font-weight: 700; margin-top: 3px; background: #FEF08A !important; color: #854D0E !important; border: 1px solid #CA8A04 !important; }
    .print-timeline-table { width: 100%; margin-top: 4px; border-collapse: collapse; }
    .print-timeline-table td { border: none; padding: 3px 6px; font-size: 9pt; }
    .print-disclaimer { font-size: 7.5pt; line-height: 1.45; color: #444; border-top: 1px solid #ccc; padding-top: 8px; margin-top: 10px; }
    .print-footer-url { margin-top: 6px; font-size: 7.2pt; color: #777; display: flex; justify-content: space-between; }
  </style>
</head>
<body>
  ${worksheetHtml}
  <script>
    window.onload = function() {
      setTimeout(function() {
        window.print();
      }, 250);
    };
  <\/script>
</body>
</html>`);
  printWindow.document.close();
}

// ── INIT ON LOAD ──
document.addEventListener('DOMContentLoaded', () => {
  populateFoodCatalog();
  runCalculation(false);
});
