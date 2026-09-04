<?php
// views/dashboard.php — Clean modern mobile-first layout matching exact uploaded screenshot
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle    = 'Dashboard';
$activeNav    = 'dashboard';
$hasRightPanel= false;
include __DIR__ . '/../includes/header.php';

$hour = (int)date('G');
$greetingText = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
?>

<!-- DASHBOARD PAGE WRAPPER -->
<div class="dashboard-page-container">

  <!-- 1. GREETING & STATS CARD -->
  <div class="dash-card greeting-stats-card">
    <div class="dash-greeting-header hide-on-mobile">
      <div class="dash-greeting-left">
        <h1 class="dash-greeting-title"><?= $greetingText ?>, <?= htmlspecialchars($username) ?></h1>
        <p class="dash-greeting-sub">Here's what's happening today</p>
      </div>
      <div class="dash-date-badge">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span><?= date('d M Y') ?></span>
      </div>
    </div>

    <!-- 3x2 STAT CARDS GRID -->
    <div class="stats-grid-3x2" id="statsRow">
      <?php for($i=0;$i<6;$i++): ?>
      <div class="dash-stat-box">
        <div class="skeleton" style="width:36px;height:36px;border-radius:10px;margin-bottom:8px;"></div>
        <div class="skeleton" style="height:10px;width:70px;margin-bottom:6px;"></div>
        <div class="skeleton" style="height:18px;width:50px;"></div>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- 2. RECENT ACTIVITY CARD -->
  <div class="dash-card recent-activity-card">
    <div class="dash-card-header">
      <div class="dash-card-header-left">
        <span class="dash-header-icon" style="background:#6366F1;color:#fff;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </span>
        <h2 class="dash-card-title">Recent Activity</h2>
      </div>
      <a href="<?= $basePath ?>/reports" class="dash-card-link">View all &rsaquo;</a>
    </div>
    <div class="dash-card-body">
      <div class="dash-txn-list" id="recentTxnList">
        <?php for($i=0;$i<4;$i++): ?>
        <div class="dash-txn-item">
          <div class="skeleton" style="width:38px;height:38px;border-radius:10px;flex-shrink:0;"></div>
          <div style="flex:1;"><div class="skeleton" style="height:13px;width:120px;margin-bottom:4px;"></div><div class="skeleton" style="height:10px;width:80px;"></div></div>
          <div style="text-align:right;"><div class="skeleton" style="height:13px;width:60px;margin-bottom:4px;"></div><div class="skeleton" style="height:10px;width:40px;"></div></div>
        </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- 3. RECENT PROJECTS CARD -->
  <div class="dash-card recent-projects-card">
    <div class="dash-card-header">
      <div class="dash-card-header-left">
        <span class="dash-header-icon" style="background:#DBEAFE;color:#2563EB;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
        </span>
        <h2 class="dash-card-title">Recent Projects</h2>
      </div>
      <a href="<?= $basePath ?>/projects" class="dash-card-link">View all &rsaquo;</a>
    </div>
    <div class="dash-card-body">
      <div class="dash-projects-grid" id="recentProjectsGrid">
        <?php for($i=0;$i<2;$i++): ?>
        <div class="dash-project-tile">
          <div class="skeleton" style="height:110px;width:100%;"></div>
          <div style="padding:10px 12px;"><div class="skeleton" style="height:13px;width:70%;margin-bottom:6px;"></div><div class="skeleton" style="height:10px;width:50%;margin-bottom:8px;"></div><div class="skeleton" style="height:12px;width:60%;"></div></div>
        </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- 4. SCHEDULE & TODAY SUMMARY CARD -->
  <div class="dash-card schedule-summary-card">
    <div class="dash-card-header">
      <div class="dash-card-header-left">
        <span class="dash-header-icon" style="background:#EDE9FE;color:#7C3AED;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </span>
        <h2 class="dash-card-title">Schedule</h2>
      </div>
      <a href="#" onclick="showAddScheduleModal();return false;" class="dash-card-link" style="font-weight:700;">+ Add</a>
    </div>
    <div class="dash-card-body">
      <div class="dash-schedule-grid">
        <!-- Calendar Box -->
        <div class="dash-sub-box calendar-sub-box">
          <div id="dashCalendarWidget"></div>
        </div>

        <!-- Today & Summary Column -->
        <div class="dash-schedule-right-col">
          <!-- Today Section -->
          <div class="dash-sub-box" id="todayScheduleSection">
            <h3 class="dash-sub-title"><span>&#9201; Today</span></h3>
            <div class="schedule-list" id="todayScheduleList" style="min-height:50px;">
              <p style="font-size:12px;color:#94A3B8;text-align:center;padding:12px 0;">No schedule today</p>
            </div>
          </div>

          <!-- Today Summary -->
          <div class="dash-sub-box">
            <h3 class="dash-sub-title"><span>&#128200; Today Summary</span></h3>
            <div class="quick-summary" id="quickSummaryBox">
              <div class="summary-row"><span class="summary-label">&#128193; Active Projects</span><span class="summary-value" id="sumActiveProj">-</span></div>
              <div class="summary-row"><span class="summary-label">&#128176; Today Payments</span><span class="summary-value green" id="sumTodayPay">-</span></div>
              <div class="summary-row"><span class="summary-label">&#128170; Labor Present</span><span class="summary-value" id="sumLaborPres">-</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
var TODAY = '<?= date('Y-m-d') ?>';
var TODAY_LABEL = '<?= date('D, d M Y') ?>';
var currentCalMonth = new Date();

// Load Dashboard Data 
async function loadDashboard() {
  try {
    const r = await fetch(BASE_PATH + '/api/index.php?action=get_dashboard_stats');
    const d = await r.json();
    if (!d.success) return;
    renderStats(d.data);
    renderRecentProjects(d.data.recent_projects || []);
    renderRecentTxns(d.data.recent_transactions || []);
    renderDashboardSummary(d.data);
  } catch(e) { console.error(e); }
}

function renderStats(data) {
  const row = document.getElementById('statsRow');
  if (!row) return;
  const cards = [
    { label:'TOTAL PROJECTS', value: data.total_projects, icon:'&#128193;', iconBg:'#DBEAFE', link: BASE_PATH + '/projects' },
    { label:'MONTHLY EXPENSES', value: fmt(data.total_expenses_month), icon:'&#128722;', iconBg:'#FCE7F3', link: BASE_PATH + '/reports' },
    { label:'CLIENT PAYMENTS', value: fmt(data.total_payments_month), icon:'&#128176;', iconBg:'#D1FAE5', link: BASE_PATH + '/payments' },
    { label:'CONTRACTORS', value: data.total_contractors, icon:'&#128736;', iconBg:'#FEF3C7', link: BASE_PATH + '/contractors' },
    { label:'TOTAL DUE', value: fmt(data.total_due), icon:'&#9203;', iconBg:'#EDE9FE', link: BASE_PATH + '/reports' },
    { label:'LABOR PRESENT', value: data.labor_present || 0, icon:'&#128170;', iconBg:'#FFE4E6', link: BASE_PATH + '/daily-labor' }
  ];
  row.innerHTML = cards.map(c => `
    <a href="${c.link}" class="dash-stat-box">
      <div class="dash-stat-icon" style="background:${c.iconBg};">${c.icon}</div>
      <div class="dash-stat-label">${c.label}</div>
      <div class="dash-stat-bottom">
        <span class="dash-stat-val">${c.value}</span>
        <span class="dash-stat-arrow">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </span>
      </div>
    </a>`).join('');
}

function renderRecentProjects(projects) {
  const grid = document.getElementById('recentProjectsGrid');
  if (!grid) return;
  if (!projects.length) { grid.innerHTML = '<div class="empty-state" style="padding:24px;grid-column:1/-1;"><p>No projects yet</p></div>'; return; }
  const statusMap = { 'Ongoing':'ongoing','Completed':'completed','On Hold':'on-hold','Cancelled':'cancelled' };
  grid.innerHTML = projects.slice(0, 2).map(p => {
    const pct = p.progress || 0;
    const img = p.project_image
      ? `<img src="${BASE_PATH}/${p.project_image}" class="dash-project-img" alt="${esc(p.name)}" onerror="this.outerHTML='<div class=\'dash-project-img\' style=\'display:flex;align-items:center;justify-content:center;font-size:32px;color:#94A3B8;\'>&#127968;</div>'">`
      : `<div class="dash-project-img" style="display:flex;align-items:center;justify-content:center;font-size:32px;color:#94A3B8;">&#127968;</div>`;
    return `
    <a href="<?= $basePath ?>/project-detail?id=${p.id}" class="dash-project-tile">
      <div class="dash-project-img-wrap">
        ${img}
        <span class="dash-project-badge">${p.status || 'Ongoing'}</span>
      </div>
      <div class="dash-project-body">
        <div class="dash-project-name">${esc(p.name)}</div>
        <div class="dash-project-loc">&#128205; ${esc(p.client_name||'')}</div>
        <div class="dash-project-spent">${fmt(p.spent)}</div>
        <div class="dash-project-progress-row">
          <div class="dash-project-progress-bar"><div class="dash-project-progress-fill" style="width:${pct}%"></div></div>
          <span class="dash-project-pct">${pct}%</span>
        </div>
      </div>
    </a>`;
  }).join('');
}

function renderRecentTxns(txns) {
  const list = document.getElementById('recentTxnList');
  if (!list) return;
  if (!txns.length) { list.innerHTML = '<div class="empty-state" style="padding:20px;"><p>No recent activity</p></div>'; return; }
  const typeMap = {
    purchase:            { icon:'&#128722;', color:'#D1FAE5', amountClass:'expense' },
    contractor_payment:  { icon:'&#128736;', color:'#FEF3C7', amountClass:'expense' },
    labor_payment:       { icon:'&#128170;', color:'#DBEAFE', amountClass:'expense' },
    client_payment:      { icon:'&#128176;', color:'#D1FAE5', amountClass:'income' },
  };
  list.innerHTML = txns.slice(0, 4).map(t => {
    const m = typeMap[t.type] || { icon:'&#9679;', color:'var(--border-light)', amountClass:'expense' };
    return `<div class="dash-txn-item">
      <div class="dash-txn-icon" style="background:${m.color};">${m.icon}</div>
      <div class="dash-txn-info">
        <div class="dash-txn-title">${esc(t.title)}</div>
        <div class="dash-txn-sub">${esc(t.project_name||'')}</div>
      </div>
      <div class="dash-txn-right">
        <div class="dash-txn-amt ${m.amountClass}">${m.amountClass==='expense'?'-':'+'}${fmt(t.amount)}</div>
        <div class="dash-txn-date">${fmtDate(t.tx_date)}</div>
      </div>
    </div>`;
  }).join('');
}

function renderDashboardSummary(data) {
  const elActive = document.getElementById('sumActiveProj');
  if (elActive) elActive.textContent = data.ongoing || data.total_projects || '0';
  const elPay = document.getElementById('sumTodayPay');
  if (elPay) elPay.textContent = fmt(data.today_payments || 0);
  const elLab = document.getElementById('sumLaborPres');
  if (elLab) elLab.textContent = (data.daily_labor_today || data.labor_present || 0) + ' / ' + (data.total_workers || 4);
}

// IN-PAGE CALENDAR
async function renderDashCalendar(monthDate) {
  const widget = document.getElementById('dashCalendarWidget');
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
  
  let html = `<div class="calendar-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
    <div class="cal-title" style="font-size:14px;font-weight:700;color:#0F172A;">${monthNames[month]} ${year}</div>
    <div class="cal-nav" style="display:flex;gap:4px;align-items:center;">
      <button type="button" class="cal-nav-btn" onclick="dashPrevMonth()">&#8249;</button>
      <button type="button" class="cal-nav-btn" onclick="dashNextMonth()">&#8250;</button>
    </div>
  </div>
  <div class="calendar-grid">`;
  html += days.map(d => `<div class="cal-day-name">${d}</div>`).join('');
  for (let i=0;i<firstDay;i++) html += '<div class="cal-day other-month"></div>';
  for (let d=1;d<=daysInMonth;d++) {
    const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const isToday = dateStr === TODAY;
    const hasSch  = scheduleDates.has(dateStr);
    html += `<div class="cal-day${isToday?' today':''}${hasSch?' has-schedule':''}" onclick="onDashCalDayClick('${dateStr}')" title="Click to add schedule for ${dateStr}">${d}</div>`;
  }
  html += '</div>';
  widget.innerHTML = html;
}

window.dashPrevMonth = function() { currentCalMonth.setMonth(currentCalMonth.getMonth()-1); renderDashCalendar(currentCalMonth); };
window.dashNextMonth = function() { currentCalMonth.setMonth(currentCalMonth.getMonth()+1); renderDashCalendar(currentCalMonth); };

window.selectedDashDate = TODAY;
window.onDashCalDayClick = function(dateStr) {
  window.selectedDashDate = dateStr;
  showDashDaySchedules(dateStr);
  showAddScheduleModal(dateStr);
};

window.showDashDaySchedules = async function(date) {
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
  } catch(e) {}
};

function initDashboardPage() {
  loadDashboard();
  renderDashCalendar(currentCalMonth);
  if (typeof renderTodaySchedules === 'function') {
    fetch(BASE_PATH + '/api/schedules.php?action=list&date=' + TODAY)
      .then(r => r.json())
      .then(d => { if (d.success) renderTodaySchedules(d.data); });
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDashboardPage);
} else {
  initDashboardPage();
}
window.addEventListener('load', initDashboardPage);

// Helpers
function fmt(n)     { return 'Tk. ' + parseFloat(n||0).toLocaleString('en-BD',{maximumFractionDigits:0}); }
function esc(s)     { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtDate(d) { if (!d) return ''; var dt=new Date(d); return isNaN(dt)?d:dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short'}); }
</script>

<?php
$hasRightPanel = false;
include __DIR__ . '/../includes/footer.php';
?>
