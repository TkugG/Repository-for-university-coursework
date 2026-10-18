/**
 * Frontend Controller (Full Interactive)
 * รองรับ: แสดงผลการ์ด 5 สี, ติ๊กถูกขีดฆ่าได้ทุกใบ, สร้างใหม่เด้งทันที, แก้ไขโน๊ต, ลบโน๊ต
 */

const API_URL = 'api/notes.php';

let currentCatId = '';
let currentNoteType = 'all';
let searchQuery = '';
let allNotes = [];

document.addEventListener('DOMContentLoaded', () => {
  fetchNotes();
  setupSearch();
  checkAutoReminders();

  const btnTest = document.getElementById('btnTestReminder');
  if (btnTest) {
    btnTest.addEventListener('click', testNotification);
  }
});

// 1. ดึงโน๊ต (ถ้าไม่มี API จะใช้ Mock Data แสดงผลทันที)
async function fetchNotes() {
  try {
    let url = `${API_URL}?`;
    if (currentCatId) url += `category_id=${encodeURIComponent(currentCatId)}&`;
    if (searchQuery) url += `search=${encodeURIComponent(searchQuery)}&`;

    const res = await fetch(url);
    const result = await res.json();
    if (result.success && result.data) {
      allNotes = result.data;
      renderNotes(allNotes);
      return;
    }
  } catch (err) {}

  if (allNotes.length === 0) {
    allNotes = getMockData();
  }
  renderNotes(allNotes);
}

// 2. วาดการ์ดลงหน้าจอ
function renderNotes(notes) {
  const grid = document.getElementById('notesGrid');
  const countTag = document.getElementById('noteCountTag');

  let filtered = notes;
  if (currentNoteType !== 'all') {
    filtered = filtered.filter(n => n.type === currentNoteType);
  }
  if (currentCatId) {
    filtered = filtered.filter(n => String(n.category_id) === String(currentCatId));
  }

  if (countTag) countTag.textContent = `${filtered.length} บันทึก`;

  if (filtered.length === 0) {
    grid.innerHTML = `
      <div class="empty-state">
        <i class="fa-regular fa-note-sticky" style="font-size: 2.5rem; color:#94a3b8; margin-bottom:12px;"></i>
        <h3>ยังไม่มีโน๊ตในหมวดหมู่นี้</h3>
        <p style="color:#64748b;">กดปุ่ม "สร้างโน๊ตใหม่" ได้เลย</p>
      </div>
    `;
    return;
  }

  grid.innerHTML = filtered.map(note => {
    const cardColor = note.color || '#fef08a';
    const catName = note.category_name || getCatName(note.category_id);

    // ส่วนเนื้อหา
    let bodyHtml = '';
    if (note.type === 'text') {
      const img = note.image ? `<img src="uploads/${note.image}" class="card-image-preview">` : '';
      bodyHtml = `${img}<div class="card-text">${escapeHtml(note.content || '')}</div>`;
    } else {
      // รายการ To-Do (มี Checkbox + ขีดฆ่าเมื่อติ๊กถูก)
      const todos = (note.todo_items || []).map(item => `
        <li class="todo-row ${item.is_done == 1 ? 'done' : ''}">
          <input type="checkbox" ${item.is_done == 1 ? 'checked' : ''} onchange="toggleTodo(${item.id}, this.checked)">
          <span>${escapeHtml(item.item_text)}</span>
        </li>
      `).join('');
      bodyHtml = `<ul class="card-todos">${todos}</ul>`;
    }

    const timeLabel = note.reminder_at 
      ? `<span><i class="fa-regular fa-bell"></i> เตือน: ${note.reminder_at.slice(0, 16)}</span>`
      : `<span><i class="fa-regular fa-calendar"></i> บันทึกแล้ว</span>`;

    return `
      <div class="note-card" style="background-color: ${cardColor};">
        <div>
          <div class="card-top">
            <span class="card-pill">${escapeHtml(catName)}</span>
            <div class="card-actions">
              <button class="btn-icon-action" onclick="openEditModal(${note.id})" title="แก้ไข">
                <i class="fa-regular fa-pen-to-square"></i>
              </button>
              <button class="btn-icon-action btn-icon-trash" onclick="deleteNote(${note.id})" title="ลบ">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </div>
          </div>
          <h4 class="card-title">${escapeHtml(note.title)}</h4>
          ${bodyHtml}
        </div>
        <div class="card-bottom">${timeLabel}</div>
      </div>
    `;
  }).join('');
}

// 3. ติ๊กถูกขีดฆ่า To-Do Item (ทำงานแบบเรียลไทม์ได้ทุกใบ)
function toggleTodo(itemId, isDone) {
  // สลับสถานะในหน้าจอทันที
  allNotes.forEach(note => {
    (note.todo_items || []).forEach(item => {
      if (item.id === itemId) {
        item.is_done = isDone ? 1 : 0; // เปลี่ยนสถานะเป็น 1 หรือ 0
      }
    });
  });

  renderNotes(allNotes); // วาดเส้นขีดฆ่าใหม่ทันที

  // ยิงบอก Backend
  fetch(`${API_URL}?action=toggle_todo`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ item_id: itemId, is_done: isDone ? 1 : 0 })
  }).catch(()=>{});
}

// 4. บันทึกโน๊ต (ทั้งสร้างใหม่ และ แก้ไข)
async function saveNote(e) {
  e.preventDefault();

  const form = document.getElementById('noteForm');
  const formData = new FormData(form);
  const editId = document.getElementById('editNoteId').value;
  const type = document.getElementById('noteType').value;
  const title = formData.get('title');
  const catId = formData.get('category_id');
  const color = formData.get('color') || '#fef08a';
  const content = formData.get('content') || '';
  const reminder = formData.get('reminder_at') || '';

  let todoItems = [];
  if (type === 'todo') {
    const raw = document.getElementById('todoInput').value;
    const items = raw.split(',').map(s => s.trim()).filter(s => s !== '');
    formData.append('todo_items', JSON.stringify(items));
    todoItems = items.map((t, idx) => ({ id: Date.now() + idx, item_text: t, is_done: 0 }));
  }

  // ส่งบันทึกไปยังหลังบ้าน (PHP API)
  const targetUrl = editId ? `${API_URL}?action=update` : API_URL;
  try {
    const res = await fetch(targetUrl, { method: 'POST', body: formData });
    const result = await res.json();
    if (result.success) {
      closeModal();
      form.reset();
      selectType('text');
      await fetchNotes(); // โหลดข้อมูลจริงล่าสุดจากฐานข้อมูล (รูปภาพและ ID จะตรง 100%)
      return;
    }
  } catch(err) {
    console.warn('API error, falling back to local state:', err);
  }

  // กรณี Offline หรือ API ไม่ตอบสนอง (Fallback เดิม)
  if (editId) {
    const idx = allNotes.findIndex(n => n.id == editId);
    if (idx !== -1) {
      allNotes[idx].title = title;
      allNotes[idx].category_id = catId;
      allNotes[idx].category_name = getCatName(catId);
      allNotes[idx].color = color;
      allNotes[idx].type = type;
      if (type === 'text') {
        allNotes[idx].content = content;
      } else {
        allNotes[idx].reminder_at = reminder;
        if (todoItems.length > 0) allNotes[idx].todo_items = todoItems;
      }
    }
  } else {
    const newCard = {
      id: Date.now(),
      category_id: catId,
      category_name: getCatName(catId),
      title: title,
      type: type,
      content: content,
      color: color,
      reminder_at: reminder,
      todo_items: todoItems,
      created_at: new Date().toISOString()
    };
    allNotes.unshift(newCard);
  }

  closeModal();
  form.reset();
  selectType('text');
  renderNotes(allNotes);
}

// 5. เปิด Modal สำหรับ "สร้างโน๊ตใหม่"
function openCreateModal() {
  document.getElementById('noteForm').reset();
  document.getElementById('editNoteId').value = '';
  document.getElementById('modalHeading').innerHTML = '<i class="fa-solid fa-pen-nib"></i> เขียนโน๊ตใหม่';
  document.getElementById('btnSaveSubmit').innerHTML = '<i class="fa-solid fa-check"></i> บันทึกโน๊ต';
  selectType('text');
  openModal();
}

// 6. เปิด Modal สำหรับ "แก้ไขโน๊ตเดิม"
function openEditModal(id) {
  const note = allNotes.find(n => n.id === id);
  if (!note) return;

  document.getElementById('modalHeading').innerHTML = '<i class="fa-solid fa-pen-to-square"></i> แก้ไขโน๊ต';
  document.getElementById('btnSaveSubmit').innerHTML = '<i class="fa-solid fa-check"></i> บันทึกการแก้ไข';

  document.getElementById('editNoteId').value = note.id;
  document.getElementById('noteTitleInput').value = note.title;
  document.getElementById('noteCatSelect').value = note.category_id || '1';

  selectType(note.type);

  if (note.type === 'text') {
    document.getElementById('noteContentInput').value = note.content || '';
  } else {
    document.getElementById('noteReminderInput').value = note.reminder_at ? note.reminder_at.slice(0, 16) : '';
    const textList = (note.todo_items || []).map(t => t.item_text).join(', ');
    document.getElementById('todoInput').value = textList;
  }

  const radio = document.querySelector(`input[name="color"][value="${note.color}"]`);
  if (radio) radio.checked = true;

  openModal();
}

// 7. ลบโน๊ต
function deleteNote(id) {
  if (!confirm('ต้องการลบโน๊ตนี้ใช่ไหม?')) return;
  fetch(`${API_URL}?action=delete&id=${id}`, { method: 'POST' }).catch(()=>{});
  allNotes = allNotes.filter(n => n.id !== id);
  renderNotes(allNotes);
}

// 8. กรองหมวดหมู่ & ค้นหา
function filterCategory(catId, btn) {
  currentCatId = catId;
  document.querySelectorAll('.sidebar-group:first-child .nav-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderNotes(allNotes);
}

function filterType(type, btn) {
  currentNoteType = type;
  document.querySelectorAll('.sidebar-group:last-child .nav-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderNotes(allNotes);
}

function setupSearch() {
  const input = document.getElementById('searchInput');
  if (!input) return;
  input.addEventListener('input', (e) => {
    const val = e.target.value.toLowerCase().trim();
    if (!val) {
      renderNotes(allNotes);
      return;
    }
    const filtered = allNotes.filter(n => 
      (n.title && n.title.toLowerCase().includes(val)) ||
      (n.content && n.content.toLowerCase().includes(val))
    );
    renderNotes(filtered);
  });
}

function selectType(type) {
  document.getElementById('noteType').value = type;
  document.getElementById('btnTypeText').classList.toggle('active', type === 'text');
  document.getElementById('btnTypeTodo').classList.toggle('active', type === 'todo');
  document.getElementById('textSection').style.display = (type === 'text') ? 'block' : 'none';
  document.getElementById('todoSection').style.display = (type === 'todo') ? 'block' : 'none';
}

function openModal() { document.getElementById('noteModal').classList.add('active'); }
function closeModal() { document.getElementById('noteModal').classList.remove('active'); }

function getCatName(id) {
  const map = { 1: 'ทั่วไป', 2: 'การเรียน', 3: 'งาน', 4: 'ส่วนตัว' };
  return map[id] || 'ทั่วไป';
}

function escapeHtml(text) {
  if (!text) return '';
  return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function getMockData() {
  return [
    {
      id: 1,
      category_id: 2,
      category_name: "การเรียน",
      title: "สรุปวิชา OOP บทที่ 1",
      type: "text",
      content: "ทำความเข้าใจ 4 เสาหลักของ OOP: Abstraction, Encapsulation, Inheritance, Polymorphism",
      color: "#fef08a",
      created_at: "2026-10-11"
    },
    {
      id: 2,
      category_id: 3,
      category_name: "งาน",
      title: "สิ่งที่ต้องเตรียมส่งโปรเจกต์",
      type: "todo",
      color: "#bbf7d0",
      reminder_at: "2026-10-12 18:00:00",
      todo_items: [
        { id: 101, item_text: "ออกแบบฐานข้อมูล MySQL", is_done: 1 },
        { id: 102, item_text: "ทำหน้าจอ UI Responsive การ์ด 5 สี", is_done: 1 },
        { id: 103, item_text: "ทดสอบการเชื่อมต่อ API ด้วย Fetch", is_done: 0 }
      ]
    },
    {
      id: 3,
      category_id: 4,
      category_name: "ส่วนตัว",
      title: "ไอเดียของขวัญวันเกิด",
      type: "text",
      content: "ซื้อหูฟังไร้สาย หรือไม่ก็หนังสือพัฒนาตัวเองสักเล่ม",
      color: "#fbcfe8",
      created_at: "2026-10-11"
    }
  ];
}

// =========================================
// 8. ระบบแจ้งเตือน (Reminder & Toast System)
// =========================================

// ตรวจสอบโน๊ตที่มีการแจ้งเตือนใกล้ถึงเวลาจาก Backend อัตโนมัติเมื่อเปิดเว็บ
async function checkAutoReminders() {
  try {
    const res = await fetch(`${API_URL}?reminders=1`);
    const result = await res.json();
    if (result.success && result.data && result.data.length > 0) {
      const badge = document.getElementById('bellBadge');
      if (badge) {
        badge.textContent = result.data.length;
        badge.style.display = 'flex';
      }

      // แสดง Toast แจ้งเตือนรายการแรกที่ใกล้ถึงกำหนด
      const first = result.data[0];
      showToastNotification(
        `⏰ แจ้งเตือนงาน: ${escapeHtml(first.title)}`,
        `มีกำหนดเวลา: ${first.reminder_at || 'เร็วๆ นี้'}`,
        'warning'
      );
    }
  } catch (err) {
    // ป้องกัน error ขัดจังหวะหน้าเว็บ
  }
}

// ฟังก์ชันเมื่อกดปุ่มกระดิ่งบน Navbar เพื่อทดสอบการแจ้งเตือน (ใช้สาธิตวันพรีเซนต์)
async function testNotification() {
  const badge = document.getElementById('bellBadge');

  try {
    const res = await fetch(`${API_URL}?reminders=1`);
    const result = await res.json();

    if (result.success && result.data && result.data.length > 0) {
      if (badge) {
        badge.textContent = result.data.length;
        badge.style.display = 'flex';
      }

      result.data.forEach((item, index) => {
        setTimeout(() => {
          showToastNotification(
            `⏰ กำหนดส่ง: ${escapeHtml(item.title)}`,
            `หมวดหมู่: ${escapeHtml(item.category_name || 'งาน')} | กำหนด: ${item.reminder_at}`,
            'warning'
          );
        }, index * 300);
      });
      return;
    }
  } catch (err) {}

  // หากไม่มีข้อมูลแจ้งเตือนใน DB หรือทดสอบแบบออฟไลน์ ให้แสดง Demo Toast ทันที
  if (badge) {
    badge.textContent = '1';
    badge.style.display = 'flex';
  }
  showToastNotification(
    '🔔 ทดสอบระบบแจ้งเตือน (Demo)',
    'ตรวจพบงานใกล้ถึงกำหนด: "เตรียมสไลด์นำเสนอ OOP" ในอีก 30 นาที!',
    'warning'
  );
}

// สร้างการ์ด Toast แจ้งเตือนเด้งขึ้นมุมขวาบนอย่างสวยงาม
function showToastNotification(title, message, type = 'warning') {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast-card ${type === 'success' ? 'success' : ''}`;

  const iconClass = type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-bell';

  toast.innerHTML = `
    <div class="toast-icon">
      <i class="${iconClass}"></i>
    </div>
    <div class="toast-body">
      <div class="toast-title">${title}</div>
      <div class="toast-text">${message}</div>
    </div>
    <button class="toast-close" onclick="this.parentElement.remove()" title="ปิด">&times;</button>
  `;

  container.appendChild(toast);

  // หายไปเองหลัง 4.5 วินาที
  setTimeout(() => {
    toast.style.animation = 'fadeOutRight 0.35s ease forwards';
    setTimeout(() => toast.remove(), 350);
  }, 4500);
}

// ผูกฟังก์ชันเข้ากับ Window Object เพื่อให้เรียกใช้ได้แน่นอนทั้งจาก inline HTML และ Event Listener
window.testNotification = testNotification;
window.showToastNotification = showToastNotification;
window.checkAutoReminders = checkAutoReminders;