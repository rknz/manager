// right-panel.js
var currentCalMonth = new Date();
var TODAY = new Date().toISOString().split('T')[0];

function renderQuickSummary(s) {
  const box = document.getElementById('quickSummaryBox');
  if (!box) return;
  box.innerHTML = `
    <div class="summary-row"><span class="summary-label">&#128193; Active Projects</span><span class="summary-value">${document.querySelectorAll('.project-card').length || s.active_projects || 0}</span></div>
    <div class="summary-row"><span class="summary-label">&#128176; Today Payments</span><span class="summary-value green">Tk. ${parseFloat(s.today_payments||0).toLocaleString('en-BD',{maximumFractionDigits:0})}</span></div>
    <div class="summary-row"><span class="summary-label">&#128170; Labor Present</span><span class="summary-value">${s.labor_present||0} / ${s.labor_total||0}</span></div>
  `;
}

function renderTodaySchedules(schedules) {
  const list = document.getElementById('todayScheduleList');
  if (!list) return;
  if (!schedules.length) { list.innerHTML = '<p style="font-size:12px;color:var(--text-muted);text-align:center;padding:12px 0;">No schedule today</p>'; return; }
  const catColors = { Board:'#3B82F6', Paint:'#F97316', Glass:'#10B981', Electric:'#F59E0B', Payment:'#9C1F24' };
  
  function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  
  list.innerHTML = schedules.map(s => {
    const col = catColors[s.category] || 'var(--primary)';
    return `<div class="schedule-item">
      <div class="schedule-indicator" style="background:${col};"></div>
      <div class="schedule-body">
        <div class="schedule-title" onclick="toggleScheduleDone(${s.id}, this)">${esc(s.description)}</div>
        <div class="schedule-time">${esc(s.project_name||'General')}</div>
      </div>
      ${s.category ? `<span class="schedule-cat-badge" style="background:${col}22;color:${col};">${s.category}</span>` : ''}
    </div>`;
  }).join('');
}

async function renderCalendar(monthDate) {
  const widget = document.getElementById('calendarWidget');
  if (!widget) return;
  const year = monthDate.getFullYear();
  const month = monthDate.getMonth();
  const monthStr = `${year}-${String(month+1).padStart(2,'0')}`;
  let schedules = [];
  try {
    const r = await fetch(BASE_PATH + '/api/schedules.php?action=list_by_month&month=' + monthStr);
    const d = await r.json();
    if (d.success) schedules = d.data;
  } catch(e) {}
  const scheduleDates = new Set(schedules.map(s => s.schedule_date));
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month+1, 0).getDate();
  const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const days = ['Su','Mo','Tu','We','Th','Fr','Sa'];
  let html = `<div class="calendar-header">
    <div class="cal-title">${monthNames[month]} ${year}</div>
    <div class="cal-nav" style="display:flex;gap:4px;align-items:center;">
      <div class="cal-nav-btn" onclick="prevMonth()">&#8249;</div>
      <div class="cal-nav-btn" onclick="nextMonth()">&#8250;</div>
      <div class="cal-add-btn" onclick="showAddScheduleModal()" title="Add Schedule">+</div>
    </div>
  </div>
  <div class="calendar-grid">`;
  html += days.map(d => `<div class="cal-day-name">${d}</div>`).join('');
  for (let i=0;i<firstDay;i++) html += '<div class="cal-day other-month"></div>';
  for (let d=1;d<=daysInMonth;d++) {
    const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const isToday = dateStr === TODAY;
    const hasSch  = scheduleDates.has(dateStr);
    html += `<div class="cal-day${isToday?' today':''}${hasSch?' has-schedule':''}" onclick="showDaySchedules('${dateStr}')">${d}</div>`;
  }
  html += '</div>';
  widget.innerHTML = html;
}

window.prevMonth = function() { currentCalMonth.setMonth(currentCalMonth.getMonth()-1); renderCalendar(currentCalMonth); };
window.nextMonth = function() { currentCalMonth.setMonth(currentCalMonth.getMonth()+1); renderCalendar(currentCalMonth); };

window.showDaySchedules = async function(date) {
  function fmtDate(d) { if (!d) return ''; var dt=new Date(d); return isNaN(dt)?d:dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short'}); }
  try {
    const r = await fetch(BASE_PATH + '/api/schedules.php?action=list&date=' + date);
    const d = await r.json();
    if (d.success) {
      renderTodaySchedules(d.data);
      const sec = document.getElementById('todayScheduleSection');
      if (sec) {
        const title = sec.querySelector('h3 span');
        if (title) title.innerHTML = '&#9201; ' + (date === TODAY ? 'Today' : fmtDate(date));
      }
    }
  } catch(e) { showToast('Error loading schedule', 'error'); }
};

window.showAddScheduleModal = function(defaultDate) { 
  if (typeof openModal === 'function') openModal('addScheduleModal'); 
  var targetDate;
  if (defaultDate) {
    targetDate = defaultDate;
  } else {
    var tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    var yyyy = tomorrow.getFullYear();
    var mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
    var dd = String(tomorrow.getDate()).padStart(2, '0');
    targetDate = yyyy + '-' + mm + '-' + dd;
  }

  var dateInput = document.getElementById('globalSchDate');
  var hiddenInput = document.getElementById('globalSchDateHidden');
  if (dateInput) {
    if (window.SmartDate && window.SmartDate.setDateValue) {
      window.SmartDate.setDateValue(dateInput, targetDate);
    } else {
      dateInput.value = targetDate;
    }
  }
  if (hiddenInput) {
    hiddenInput.value = targetDate;
  }
  var descInput = document.getElementById('globalSchDesc');
  if (descInput) {
    descInput.value = '';
    setTimeout(function(){ descInput.focus(); }, 100);
  }
};

window.toggleScheduleDone = async function(id, el) {
  try {
    await fetch(BASE_PATH + '/api/schedules.php?action=mark_done', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id, is_done:1}) });
    el.style.textDecoration = 'line-through'; el.style.opacity = '.5';
    showToast('Marked as done!', 'success');
  } catch(e) {}
};

window.saveSchedule = async function() {
  const date = document.getElementById('globalSchDateHidden').value || document.getElementById('globalSchDate').value;
  const desc = document.getElementById('globalSchDesc').value.trim();
  const cat  = document.getElementById('globalSchCategory').value;
  if (!desc) { showToast('Please enter a description', 'warning'); return; }
  const fd = new FormData();
  fd.append('schedule_date', date || TODAY);
  fd.append('description', desc);
  fd.append('category', cat);
  try {
    const r = await fetch(BASE_PATH + '/api/schedules.php?action=create', { method:'POST', body: fd });
    const d = await r.json();
    if (d.success) {
      showToast('Schedule added!', 'success');
      closeModal('addScheduleModal');
      if (typeof renderCalendar === 'function') renderCalendar(currentCalMonth);
      if (typeof renderDashCalendar === 'function') renderDashCalendar(currentCalMonth);
      if (typeof showDashDaySchedules === 'function') showDashDaySchedules(date || TODAY);
      loadRightPanelData();
    } else { 
      showToast(d.message || 'Error', 'error'); 
    }
  } catch(e) { 
    showToast('Connection error', 'error'); 
  }
};

async function loadRightPanelData() {
  try {
    const r = await fetch(BASE_PATH + '/api/index.php?action=get_dashboard_stats');
    const d = await r.json();
    if (!d.success) return;
    renderQuickSummary(d.data.quick_summary || {});
    renderTodaySchedules(d.data.today_schedules || []);
  } catch(e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', function() {
  renderCalendar(currentCalMonth);
  loadRightPanelData();
});
