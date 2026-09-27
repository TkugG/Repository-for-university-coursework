let currentSpecies = 'dog';
let currentEditingPetId = null;

document.addEventListener('DOMContentLoaded', () => {
  checkSession();
  setSpecies('dog');

  // เพิ่ม Event Listener สลับชนิดอาหารและไฮไลต์ปุ่ม Preset เมื่อกด Radio ด้านบน
  const foodDry = document.querySelector('input[name="food-type"][value="dry"]');
  const foodWet = document.querySelector('input[name="food-type"][value="wet"]');

  if (foodDry) {
    foodDry.addEventListener('change', function() {
      if (this.checked) setKcal(360, 'dry');
    });
  }

  if (foodWet) {
    foodWet.addEventListener('change', function() {
      if (this.checked) setKcal(85, 'wet');
    });
  }
});

function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.style.display = 'flex';
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.style.display = 'none';
}

window.onclick = function(event) {
  if (event.target.classList.contains('modal-overlay')) {
    event.target.style.display = 'none';
  }
};

async function checkSession() {
  try {
    const res = await fetch('api.php?action=check_session');
    const data = await res.json();
    
    const guestTools = document.getElementById('guest-tools');
    const userTools = document.getElementById('user-tools');
    const userDisplayName = document.getElementById('user-display-name');

    if (data.logged_in) {
      if (guestTools) guestTools.style.display = 'none';
      if (userTools) userTools.style.display = 'flex';
      if (userDisplayName) userDisplayName.textContent = `คุณ ${data.user_name}`;
    } else {
      if (guestTools) guestTools.style.display = 'flex';
      if (userTools) userTools.style.display = 'none';
    }
  } catch (error) {
    console.error('Error checking session:', error);
  }
}

async function handleLogin(e) {
  e.preventDefault();
  const formData = new FormData();
  formData.append('email', document.getElementById('login-email').value);
  formData.append('password', document.getElementById('login-password').value);

  try {
    const res = await fetch('api.php?action=login', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      closeModal('login-modal');
      checkSession();
      alert('เข้าสู่ระบบสำเร็จ!');
    } else {
      alert(data.message || 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ');
    }
  } catch (error) {
    alert('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
  }
}

async function handleRegister(e) {
  e.preventDefault();
  const formData = new FormData();
  formData.append('fullname', document.getElementById('reg-name').value);
  formData.append('email', document.getElementById('reg-email').value);
  formData.append('password', document.getElementById('reg-password').value);

  try {
    const res = await fetch('api.php?action=register', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      closeModal('register-modal');
      checkSession();
      alert('สมัครสมาชิกสำเร็จ!');
    } else {
      alert(data.message || 'เกิดข้อผิดพลาดในการสมัครสมาชิก');
    }
  } catch (error) {
    alert('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
  }
}

async function logout() {
  await fetch('api.php?action=logout');
  checkSession();
  currentEditingPetId = null;
  alert('ออกจากระบบเรียบร้อยแล้ว');
}

function resetPetForm() {
  currentEditingPetId = null;

  if (document.getElementById('pet-name')) document.getElementById('pet-name').value = '';
  if (document.getElementById('pet-weight')) document.getElementById('pet-weight').value = '';
  if (document.getElementById('food-cal')) document.getElementById('food-cal').value = '';

  document.querySelectorAll('#petForm input[type="radio"]').forEach(radio => {
    radio.checked = false;
  });

  document.querySelectorAll('.btn-chip').forEach(btn => btn.classList.remove('active'));

  if (document.getElementById('is-neutered')) document.getElementById('is-neutered').checked = false;
  if (document.getElementById('breed-size')) document.getElementById('breed-size').value = '';
  if (document.getElementById('start-time')) document.getElementById('start-time').value = '08:00';

  if (document.getElementById('badge-pet-info')) document.getElementById('badge-pet-info').textContent = '-';
  if (document.getElementById('res-daily-grams')) document.getElementById('res-daily-grams').textContent = '0';
  if (document.getElementById('res-meal-grams')) document.getElementById('res-meal-grams').textContent = '0';
  if (document.getElementById('res-meal-sub')) document.getElementById('res-meal-sub').textContent = 'กรัม / มื้อ';
  if (document.getElementById('res-rer')) document.getElementById('res-rer').textContent = '0';
  if (document.getElementById('res-factor')) document.getElementById('res-factor').textContent = '0x';
  if (document.getElementById('res-der')) document.getElementById('res-der').textContent = '0';
  if (document.getElementById('res-water-name')) document.getElementById('res-water-name').textContent = '-';
  if (document.getElementById('res-water-val')) document.getElementById('res-water-val').textContent = '0';
  if (document.getElementById('timeline-list')) document.getElementById('timeline-list').innerHTML = '';

  setSpecies('dog');
}

async function savePetData() {
  const name = document.getElementById('pet-name').value;
  const weight = document.getElementById('pet-weight').value;

  if (!name || !weight) {
    alert('กรุณากรอกชื่อและน้ำหนักสัตว์เลี้ยงก่อนทำการบันทึก');
    return;
  }

  const ageStage = document.querySelector('input[name="age-stage"]:checked')?.value || 'adult';
  const activityLevel = document.querySelector('input[name="activity-level"]:checked')?.value || 'normal';
  const isNeutered = document.getElementById('is-neutered')?.checked ? 'true' : 'false';
  const breedSize = document.getElementById('breed-size')?.value || 'medium';
  const foodType = document.querySelector('input[name="food-type"]:checked')?.value || 'dry';
  const foodCal = document.getElementById('food-cal').value || '360';
  const mealsCount = document.querySelector('input[name="meals-count"]:checked')?.value || '2';
  const startTime = document.getElementById('start-time').value || '08:00';

  const formData = new FormData();
  if (currentEditingPetId) {
    formData.append('pet_id', currentEditingPetId);
  }
  formData.append('name', name);
  formData.append('species', currentSpecies);
  formData.append('weight', weight);
  formData.append('age_stage', ageStage);
  formData.append('activity_level', activityLevel);
  formData.append('is_neutered', isNeutered);
  formData.append('breed_size', breedSize);
  formData.append('food_type', foodType);
  formData.append('calories_per_100g', foodCal);
  formData.append('meals_per_day', mealsCount);
  formData.append('first_meal_time', startTime);

  try {
    const res = await fetch('api.php?action=save_pet', { method: 'POST', body: formData });
    const data = await res.json();

    if (data.success) {
      currentEditingPetId = data.pet_id;
      alert(data.message);
    } else {
      alert(data.message || 'กรุณาล็อกอินก่อนใช้งาน');
      if (!data.logged_in) openModal('login-modal');
    }
  } catch (error) {
    alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
  }
}

function escapeHtml(text) {
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

async function openMyPetsModal() {
  try {
    const res = await fetch('api.php?action=get_pets');
    const data = await res.json();
    const container = document.getElementById('pets-list-container');
    container.innerHTML = '';

    if (!data.success || !data.pets || data.pets.length === 0) {
      container.innerHTML = '<p style="text-align:center; padding:20px; color:#64748b;">ยังไม่มีข้อมูลสัตว์เลี้ยงที่บันทึกไว้</p>';
    } else {
      data.pets.forEach(pet => {
        const icon = pet.species === 'dog' ? '🐶' : '🐱';
        const speciesText = pet.species === 'dog' ? 'สุนัข' : 'แมว';
        const petJsonStr = JSON.stringify(pet).replace(/'/g, "&apos;").replace(/"/g, "&quot;");
        
        const cardHtml = `
          <div class="pet-item-card">
            <div style="font-weight: bold; color: var(--primary-pink-dark);">
              ${icon} ${escapeHtml(pet.name)} (${pet.weight} kg) - <span style="font-size:0.8rem; color:#64748b;">${speciesText}</span>
            </div>
            <div style="font-size: 0.8rem; color: #475569; margin-top: 4px;">
              ช่วงวัย: ${pet.age_stage} | กิจกรรม: ${pet.activity_level} | อาหาร: ${pet.food_type} (${pet.calories_per_100g} kcal/100g)
            </div>
            <div class="pet-card-actions">
              <button type="button" data-pet='${petJsonStr}' onclick='loadPetFromBtn(this)'>📝 โหลด/แก้ไข</button>
              <button type="button" onclick='deletePet(${pet.id})' class="btn-del">🗑️ ลบ</button>
            </div>
          </div>
        `;
        container.innerHTML += cardHtml;
      });
    }
    openModal('mypets-modal');
  } catch (error) {
    alert('ไม่สามารถโหลดรายการสัตว์เลี้ยงได้');
  }
}

function loadPetFromBtn(btnElement) {
  const pet = JSON.parse(btnElement.dataset.pet);
  loadPetToForm(pet);
}

function loadPetToForm(pet) {
  currentEditingPetId = pet.id;
  
  setSpecies(pet.species);
  document.getElementById('pet-name').value = pet.name;
  document.getElementById('pet-weight').value = pet.weight;
  
  const ageRadio = document.querySelector(`input[name="age-stage"][value="${pet.age_stage}"]`);
  if (ageRadio) ageRadio.checked = true;

  const actRadio = document.querySelector(`input[name="activity-level"][value="${pet.activity_level}"]`);
  if (actRadio) actRadio.checked = true;

  if (document.getElementById('is-neutered')) {
    document.getElementById('is-neutered').checked = (parseInt(pet.is_neutered) === 1);
  }

  if (document.getElementById('breed-size') && pet.breed_size) {
    document.getElementById('breed-size').value = pet.breed_size;
  }

  const foodRadio = document.querySelector(`input[name="food-type"][value="${pet.food_type}"]`);
  if (foodRadio) foodRadio.checked = true;

  const mealsRadio = document.querySelector(`input[name="meals-count"][value="${pet.meals_per_day}"]`);
  if (mealsRadio) mealsRadio.checked = true;

  document.getElementById('start-time').value = pet.first_meal_time;

  closeModal('mypets-modal');
  setKcal(pet.calories_per_100g, pet.food_type);
}

async function deletePet(id) {
  if (!confirm('คุณต้องการลบข้อมูลสัตว์เลี้ยงนี้ใช่หรือไม่?')) return;

  const formData = new FormData();
  formData.append('pet_id', id);

  try {
    const res = await fetch('api.php?action=delete_pet', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      if (currentEditingPetId === id) currentEditingPetId = null;
      openMyPetsModal();
    } else {
      alert(data.message || 'ลบข้อมูลไม่สำเร็จ');
    }
  } catch (error) {
    alert('เกิดข้อผิดพลาดในการลบ');
  }
}

function setSpecies(species) {
  currentSpecies = species;
  const tabDog = document.getElementById('tab-dog');
  const tabCat = document.getElementById('tab-cat');
  const breedSizeContainer = document.getElementById('breed-size-container');

  if (species === 'dog') {
    if (tabDog) tabDog.classList.add('active');
    if (tabCat) tabCat.classList.remove('active');
    if (breedSizeContainer) breedSizeContainer.style.display = 'block';
  } else {
    if (tabCat) tabCat.classList.add('active');
    if (tabDog) tabDog.classList.remove('active');
    if (breedSizeContainer) breedSizeContainer.style.display = 'none';
  }
  runCalc();
}

function stepWeight(delta) {
  const input = document.getElementById('pet-weight');
  let val = parseFloat(input.value) || 0;
  val = Math.max(0.1, Math.min(200.0, val + delta));
  input.value = val.toFixed(1);
  runCalc();
}

// ฟังก์ชั่นตั้งค่าแคลอรี + สลับ Radio + ไฮไลต์ปุ่ม Preset สีเทา
function setKcal(val, type = null, element = null) {
  document.getElementById('food-cal').value = val;

  if (type === 'dry') {
    const foodDry = document.querySelector('input[name="food-type"][value="dry"]');
    if (foodDry) foodDry.checked = true;
  } else if (type === 'wet') {
    const foodWet = document.querySelector('input[name="food-type"][value="wet"]');
    if (foodWet) foodWet.checked = true;
  }

  // เคลียร์คลาส active ทั้งหมดก่อน
  document.querySelectorAll('.btn-chip').forEach(btn => btn.classList.remove('active'));

  if (element) {
    element.classList.add('active');
  } else {
    const matchedBtn = Array.from(document.querySelectorAll('.btn-chip')).find(btn => {
      return btn.getAttribute('onclick')?.includes(`setKcal(${val}`);
    });
    if (matchedBtn) matchedBtn.classList.add('active');
  }

  runCalc();
}

async function runCalc() {
  const name = document.getElementById('pet-name')?.value || '';
  const weightInput = document.getElementById('pet-weight');
  const foodCalInput = document.getElementById('food-cal');

  let weight = parseFloat(weightInput?.value);
  let foodCal = parseFloat(foodCalInput?.value);

  if (isNaN(weight) || weight <= 0 || isNaN(foodCal) || foodCal <= 0) {
    return;
  }

  if (weight > 200) { weight = 200; if (weightInput) weightInput.value = 200; }
  if (foodCal > 1000) { foodCal = 1000; if (foodCalInput) foodCalInput.value = 1000; }

  const formData = new FormData();
  formData.append('species', currentSpecies);
  formData.append('name', name || 'น้อง');
  formData.append('weight', weight);
  formData.append('age_stage', document.querySelector('input[name="age-stage"]:checked')?.value || 'adult');
  formData.append('activity_level', document.querySelector('input[name="activity-level"]:checked')?.value || 'normal');
  formData.append('is_neutered', document.getElementById('is-neutered')?.checked ? 'true' : 'false');
  formData.append('breed_size', document.getElementById('breed-size')?.value || 'medium');
  formData.append('food_type', document.querySelector('input[name="food-type"]:checked')?.value || 'dry');
  formData.append('calories_per_100g', foodCal);
  formData.append('meals_count', document.querySelector('input[name="meals-count"]:checked')?.value || 2);
  formData.append('first_meal_time', document.getElementById('start-time')?.value || '08:00');

  try {
    const res = await fetch('api.php?action=calculate', { method: 'POST', body: formData });
    const result = await res.json();

    if (result.success) {
      const d = result.data;
      const displayName = name ? name : 'น้อง';
      if (document.getElementById('badge-pet-info')) document.getElementById('badge-pet-info').textContent = `${escapeHtml(displayName)} (${weight.toFixed(1)} kg)`;
      if (document.getElementById('res-daily-grams')) document.getElementById('res-daily-grams').textContent = Math.round(d.dailyGrams).toLocaleString();
      if (document.getElementById('res-meal-grams')) document.getElementById('res-meal-grams').textContent = (Math.round(d.mealGrams * 10) / 10).toFixed(1);
      if (document.getElementById('res-meal-sub')) document.getElementById('res-meal-sub').textContent = `กรัม / มื้อ (${d.slots.length} มื้อ/วัน)`;
      
      if (document.getElementById('res-rer')) document.getElementById('res-rer').textContent = Math.round(d.rer).toLocaleString();
      if (document.getElementById('res-factor')) document.getElementById('res-factor').textContent = `${d.factor.toFixed(1)}x`;
      if (document.getElementById('res-der')) document.getElementById('res-der').textContent = Math.round(d.der).toLocaleString();

      if (document.getElementById('res-water-name')) document.getElementById('res-water-name').textContent = escapeHtml(displayName);
      if (document.getElementById('res-water-val')) document.getElementById('res-water-val').textContent = Math.round(d.water).toLocaleString();

      renderTimeline(d.slots);
    }
  } catch (error) {
    console.error('Calculation error:', error);
  }
}

function renderTimeline(slots) {
  const container = document.getElementById('timeline-list');
  if (!container) return;
  container.innerHTML = '';

  slots.forEach(slot => {
    const itemHtml = `
      <div class="timeline-item">
        <div class="timeline-time">${slot.time}</div>
        <div class="timeline-label">${slot.label}</div>
        <div class="timeline-value">${slot.grams.toFixed(1)} กรัม</div>
      </div>
    `;
    container.innerHTML += itemHtml;
  });
}