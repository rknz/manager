<?php
// views/dashboard.php — Unified Dashboard with desktop-optimized and mobile-optimized layouts
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
include __DIR__ . '/../includes/header.php';

$hour = (int)date('G');
$greetingText = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
?>

<!-- DASHBOARD PAGE CONTAINER -->
<div class="dashboard-page-container">

  <!-- ============================================================
       1. DESKTOP VIEW (Visible on >= 769px)
       Matching Reference Screenshot (4 Stat Cards, 4 Recent Projects, Quick Access 3x2, Recent Transactions)
       ============================================================ -->
  <div class="dash-desktop-view">

    <!-- ROW 1: 4 STAT METRIC CARDS -->
    <div class="dash-metric-cards-row" id="metricCardsRow">
      <!-- Card 1: Total Projects -->
      <a href="<?= $basePath ?>/projects" class="dash-metric-card">
        <div class="dash-metric-top">
          <div class="dash-metric-icon purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          </div>
          <div class="dash-metric-details">
            <span class="dash-metric-title">Total Projects</span>
            <span class="dash-metric-value" id="statTotalProjects">-</span>
          </div>
        </div>
        <div class="dash-metric-bottom">
          <span id="statOngoingProjects">- Ongoing</span>
          <span class="dash-metric-arrow">&#8250;</span>
        </div>
      </a>

      <!-- Card 2: Total Expenses -->
      <a href="<?= $basePath ?>/reports" class="dash-metric-card">
        <div class="dash-metric-top">
          <div class="dash-metric-icon green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
          </div>
          <div class="dash-metric-details">
            <span class="dash-metric-title">Total Expenses</span>
            <span class="dash-metric-value" id="statTotalExpenses">-</span>
          </div>
        </div>
        <div class="dash-metric-bottom">
          <span>This Month</span>
          <span class="dash-metric-pill-green" id="statExpensesGrowth">&#9650; 0%</span>
        </div>
      </a>

      <!-- Card 3: Total Payments -->
      <a href="<?= $basePath ?>/payments" class="dash-metric-card">
        <div class="dash-metric-top">
          <div class="dash-metric-icon blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="3"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
          </div>
          <div class="dash-metric-details">
            <span class="dash-metric-title">Total Payments</span>
            <span class="dash-metric-value" id="statTotalPayments">-</span>
          </div>
        </div>
        <div class="dash-metric-bottom">
          <span>This Month</span>
          <span class="dash-metric-pill-green" id="statPaymentsGrowth">&#9650; 0%</span>
        </div>
      </a>

      <!-- Card 4: Total Contractors -->
      <a href="<?= $basePath ?>/contractors" class="dash-metric-card">
        <div class="dash-metric-top">
          <div class="dash-metric-icon orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <div class="dash-metric-details">
            <span class="dash-metric-title">Total Contractors</span>
            <span class="dash-metric-value" id="statTotalContractors">-</span>
          </div>
        </div>
        <div class="dash-metric-bottom">
          <span>Active</span>
          <span id="statActiveContractors" style="font-weight:700;color:#0F172A;">-</span>
        </div>
      </a>
    </div>

    <!-- ROW 2: RECENT PROJECTS -->
    <div class="dash-section">
      <div class="dash-section-header">
        <h2 class="dash-section-title">Recent Projects</h2>
        <a href="<?= $basePath ?>/projects" class="dash-section-link">View All</a>
      </div>
      <div class="dash-projects-carousel-wrap">
        <div class="dash-recent-projects-grid" id="dashRecentProjectsGrid">
          <?php for($i=0;$i<4;$i++): ?>
          <div class="dash-recent-project-card">
            <div class="skeleton" style="height:125px;width:100%;"></div>
            <div style="padding:12px 14px;">
              <div class="skeleton" style="height:14px;width:70%;margin-bottom:6px;"></div>
              <div class="skeleton" style="height:11px;width:50%;margin-bottom:10px;"></div>
              <div class="skeleton" style="height:13px;width:60%;"></div>
            </div>
          </div>
          <?php endfor; ?>
        </div>
      </div>
      <!-- Carousel Dots -->
      <div class="dash-carousel-dots">
        <span class="dash-dot active"></span>
        <span class="dash-dot"></span>
        <span class="dash-dot"></span>
        <span class="dash-dot"></span>
      </div>
    </div>

    <!-- ROW 3: TWO-COLUMN SECTION -->
    <div class="dash-bottom-grid">

      <!-- LEFT: QUICK ACCESS -->
      <div class="dash-section">
        <div class="dash-section-header">
          <h2 class="dash-section-title">Quick Access</h2>
        </div>
        <div class="dash-qa-grid">
          <!-- 1. Quick Purchase -->
          <a href="<?= $basePath ?>/quick-purchase" class="dash-qa-tile">
            <div class="dash-qa-icon" style="background:#EEF2FF;color:#4F46E5;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            </div>
            <div class="dash-qa-name">Quick Purchase</div>
            <div class="dash-qa-desc">Add new purchase</div>
          </a>

          <!-- 2. Contractors -->
          <a href="<?= $basePath ?>/contractors" class="dash-qa-tile">
            <div class="dash-qa-icon" style="background:#ECFDF5;color:#059669;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="dash-qa-name">Contractors</div>
            <div class="dash-qa-desc">Manage contractors</div>
          </a>

          <!-- 3. Payments -->
          <a href="<?= $basePath ?>/payments" class="dash-qa-tile">
            <div class="dash-qa-icon" style="background:#E0F2FE;color:#0284C7;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="3"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            </div>
            <div class="dash-qa-name">Payments</div>
            <div class="dash-qa-desc">Make or record payment</div>
          </a>

          <!-- 4. Daily Labor -->
          <a href="<?= $basePath ?>/daily-labor" class="dash-qa-tile">
            <div class="dash-qa-icon" style="background:#FFF7ED;color:#EA580C;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div class="dash-qa-name">Daily Labor</div>
            <div class="dash-qa-desc">Labor & attendance</div>
          </a>

          <!-- 5. Reports -->
          <a href="<?= $basePath ?>/reports" class="dash-qa-tile">
            <div class="dash-qa-icon" style="background:#FCE7F3;color:#DB2777;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <div class="dash-qa-name">Reports</div>
            <div class="dash-qa-desc">View all reports</div>
          </a>

          <!-- 6. Backup & Restore -->
          <a href="<?= $basePath ?>/backup" class="dash-qa-tile">
            <div class="dash-qa-icon" style="background:#F3E8FF;color:#9333EA;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 16.9A5 5 0 0 0 18 7h-1.26A8 8 0 1 0 4 15.25"/><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/></svg>
            </div>
            <div class="dash-qa-name">Backup & Restore</div>
            <div class="dash-qa-desc">Backup your data</div>
          </a>
        </div>
      </div>

      <!-- RIGHT: RECENT TRANSACTIONS -->
      <div class="dash-section">
        <div class="dash-section-header">
          <h2 class="dash-section-title">Recent Transactions</h2>
          <a href="<?= $basePath ?>/reports" class="dash-section-link">View All</a>
        </div>
        <div class="dash-txns-card">
          <div id="dashRecentTxnList">
            <?php for($i=0;$i<4;$i++): ?>
            <div class="dash-txn-item-clean">
              <div class="skeleton" style="width:38px;height:38px;border-radius:50%;flex-shrink:0;"></div>
              <div style="flex:1;"><div class="skeleton" style="height:13px;width:130px;margin-bottom:4px;"></div><div class="skeleton" style="height:10px;width:80px;"></div></div>
              <div style="text-align:right;"><div class="skeleton" style="height:13px;width:60px;margin-bottom:4px;"></div><div class="skeleton" style="height:10px;width:40px;"></div></div>
            </div>
            <?php endfor; ?>
          </div>
        </div>
      </div>

    </div>

  </div><!-- /dash-desktop-view -->

  <!-- ============================================================
       2. MOBILE VIEW (Visible on <= 768px)
       Matching Exact Uploaded Screenshot (media_1788782732532.png)
       ============================================================ -->
  <div class="dash-mobile-view">

    <!-- Greeting Bar -->
    <div class="mob-dash-greeting">
      <div>
        <h1 class="mob-dash-title">Dashboard</h1>
        <p class="mob-dash-sub"><?= $greetingText ?>, <?= htmlspecialchars($username) ?></p>
      </div>
      <div class="mob-dash-date-pill">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span><?= date('d M Y') ?></span>
      </div>
    </div>

    <!-- 3x2 STATS GRID -->
    <div class="mob-stats-grid">
      <!-- 1. Total Projects -->
      <a href="<?= $basePath ?>/projects" class="mob-stat-card">
        <div class="mob-stat-icon" style="background:#DBEAFE;color:#2563EB;">&#128193;</div>
        <div class="mob-stat-label">TOTAL PROJECTS</div>
        <div class="mob-stat-val" id="mobStatProjects">-</div>
      </a>
      <!-- 2. Monthly Expenses -->
      <a href="<?= $basePath ?>/reports" class="mob-stat-card">
        <div class="mob-stat-icon" style="background:#FCE7F3;color:#DB2777;">&#128722;</div>
        <div class="mob-stat-label">MONTHLY EXPENSES</div>
        <div class="mob-stat-val" id="mobStatExpenses">-</div>
      </a>
      <!-- 3. Client Payments -->
      <a href="<?= $basePath ?>/payments" class="mob-stat-card">
        <div class="mob-stat-icon" style="background:#D1FAE5;color:#059669;">&#128176;</div>
        <div class="mob-stat-label">CLIENT PAYMENTS</div>
        <div class="mob-stat-val" id="mobStatPayments">-</div>
      </a>
      <!-- 4. Contractors -->
      <a href="<?= $basePath ?>/contractors" class="mob-stat-card">
        <div class="mob-stat-icon" style="background:#FEF3C7;color:#D97706;">&#128736;</div>
        <div class="mob-stat-label">CONTRACTORS</div>
        <div class="mob-stat-val" id="mobStatContractors">-</div>
      </a>
      <!-- 5. Total Due -->
      <a href="<?= $basePath ?>/reports" class="mob-stat-card">
        <div class="mob-stat-icon" style="background:#EDE9FE;color:#7C3AED;">&#9203;</div>
        <div class="mob-stat-label">TOTAL DUE</div>
        <div class="mob-stat-val" id="mobStatDue">-</div>
      </a>
      <!-- 6. Labor Present -->
      <a href="<?= $basePath ?>/daily-labor" class="mob-stat-card">
        <div class="mob-stat-icon" style="background:#FFE4E6;color:#E11D48;">&#128170;</div>
        <div class="mob-stat-label">LABOR PRESENT</div>
        <div class="mob-stat-val" id="mobStatLabor">-</div>
      </a>
    </div>

    <!-- RECENT ACTIVITY CARD -->
    <div class="mob-card">
      <div class="mob-card-header">
        <div class="mob-card-header-left">
          <span style="font-size:16px;">&#128260;</span>
          <h3 class="mob-card-title">Recent Activity</h3>
        </div>
      </div>
      <div id="mobActivityList">
        <?php for($i=0;$i<4;$i++): ?>
        <div class="mob-activity-item">
          <div class="skeleton" style="width:38px;height:38px;border-radius:10px;flex-shrink:0;"></div>
          <div style="flex:1;"><div class="skeleton" style="height:13px;width:120px;margin-bottom:4px;"></div><div class="skeleton" style="height:10px;width:80px;"></div></div>
          <div style="text-align:right;"><div class="skeleton" style="height:13px;width:60px;margin-bottom:4px;"></div><div class="skeleton" style="height:10px;width:40px;"></div></div>
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <!-- RECENT PROJECTS CARD -->
    <div class="mob-card">
      <div class="mob-card-header">
        <div class="mob-card-header-left">
          <span style="font-size:16px;">&#128193;</span>
          <h3 class="mob-card-title">Recent Projects</h3>
        </div>
        <a href="<?= $basePath ?>/projects" class="mob-card-link">View all &rarr;</a>
      </div>
      <div class="mob-projects-carousel" id="mobProjectsCarousel">
        <?php for($i=0;$i<2;$i++): ?>
        <div class="mob-project-card">
          <div class="skeleton" style="height:115px;width:100%;"></div>
          <div style="padding:10px 12px;"><div class="skeleton" style="height:13px;width:70%;margin-bottom:6px;"></div><div class="skeleton" style="height:10px;width:50%;margin-bottom:8px;"></div><div class="skeleton" style="height:12px;width:60%;"></div></div>
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <!-- SCHEDULE CARD -->
    <div class="mob-card">
      <div class="mob-card-header">
        <div class="mob-card-header-left">
          <span style="font-size:16px;">&#128197;</span>
          <h3 class="mob-card-title">Schedule</h3>
        </div>
        <a href="#" onclick="showAddScheduleModal();return false;" class="mob-card-link">+ Add</a>
      </div>
      <div id="mobCalendarWidget"></div>
    </div>

    <!-- TODAY CARD -->
    <div class="mob-card" id="mobTodaySection">
      <div class="mob-card-header">
        <div class="mob-card-header-left">
          <span style="font-size:16px;">&#9201;</span>
          <h3 class="mob-card-title" id="mobTodayTitle">Today</h3>
        </div>
      </div>
      <div id="mobTodayScheduleList" style="min-height:36px;display:flex;align-items:center;justify-content:center;">
        <p style="font-size:12px;color:#94A3B8;text-align:center;margin:8px 0;">No schedule today</p>
      </div>
    </div>

    <!-- TODAY SUMMARY CARD -->
    <div class="mob-card">
      <div class="mob-card-header">
        <div class="mob-card-header-left">
          <span style="font-size:16px;">&#128200;</span>
          <h3 class="mob-card-title">Today Summary</h3>
        </div>
      </div>
      <div class="mob-summary-row">
        <span class="mob-summary-label">&#128193; Active Projects</span>
        <span class="mob-summary-val" id="mobSumActiveProj">-</span>
      </div>
      <div class="mob-summary-row">
        <span class="mob-summary-label">&#128176; Today Payments</span>
        <span class="mob-summary-val green" id="mobSumTodayPay">-</span>
      </div>
      <div class="mob-summary-row">
        <span class="mob-summary-label">&#128170; Labor Present</span>
        <span class="mob-summary-val" id="mobSumLaborPres">-</span>
      </div>
    </div>

  </div><!-- /dash-mobile-view -->

</div><!-- /dashboard-page-container -->

<script>
var TODAY = '<?= date('Y-m-d') ?>';
var mobCurrentCalMonth = new Date();

// Load Dashboard Data
async function loadDashboard() {
  if (window.AppCache) {
    const cached = window.AppCache.get('dash_stats');
    if (cached) {
      populateDesktop(cached);
      populateMobile(cached);
    }
  }
  try {
    const r = await fetch(BASE_PATH + '/api/index.php?action=get_dashboard_stats');
    const d = await r.json();
    if (!d.success) return;
    if (window.AppCache) window.AppCache.set('dash_stats', d.data, 60000);
    populateDesktop(d.data);
    populateMobile(d.data);
  } catch(e) { console.error(e); }
}

// 1. POPULATE DESKTOP
function populateDesktop(data) {
  // Metric Cards
  const elProj = document.getElementById('statTotalProjects');
  if (elProj) elProj.textContent = data.total_projects || 0;
  const elOng = document.getElementById('statOngoingProjects');
  if (elOng) elOng.textContent = (data.ongoing || 0) + ' Ongoing';

  const elExp = document.getElementById('statTotalExpenses');
  if (elExp) elExp.textContent = fmtTk(data.total_expenses_month || 0);
  const elExpG = document.getElementById('statExpensesGrowth');
  if (elExpG) {
    const g = parseFloat(data.expenses_growth || 0);
    elExpG.innerHTML = (g >= 0 ? '&#9650; ' : '&#9660; ') + Math.abs(g) + '%';
    elExpG.style.color = g >= 0 ? '#059669' : '#EF4444';
  }

  const elPay = document.getElementById('statTotalPayments');
  if (elPay) elPay.textContent = fmtTk(data.total_payments_month || 0);
  const elPayG = document.getElementById('statPaymentsGrowth');
  if (elPayG) {
    const g = parseFloat(data.payments_growth || 0);
    elPayG.innerHTML = (g >= 0 ? '&#9650; ' : '&#9660; ') + Math.abs(g) + '%';
    elPayG.style.color = g >= 0 ? '#059669' : '#EF4444';
  }

  const elCont = document.getElementById('statTotalContractors');
  if (elCont) elCont.textContent = data.total_contractors || 0;
  const elActCont = document.getElementById('statActiveContractors');
  if (elActCont) elActCont.textContent = data.active_contractors || 0;

  // Recent Projects Grid
  const grid = document.getElementById('dashRecentProjectsGrid');
  if (grid) {
    const projects = data.recent_projects || [];
    if (!projects.length) {
      grid.innerHTML = '<div style="grid-column:1/-1;padding:32px;text-align:center;color:#94A3B8;background:#fff;border-radius:16px;border:1px solid #EDF2F7;">No recent projects found</div>';
    } else {
      const statusMap = { 'Ongoing': 'ongoing', 'Completed': 'completed', 'On Hold': 'onhold', 'Cancelled': 'cancelled' };
      const colorPalettes = ['linear-gradient(90deg, #6366F1, #8B5CF6)','linear-gradient(90deg, #10B981, #059669)','linear-gradient(90deg, #2563EB, #3B82F6)','linear-gradient(90deg, #F97316, #EA580C)'];
      grid.innerHTML = projects.slice(0, 4).map((p, idx) => {
        const pct = typeof p.progress === 'number' ? p.progress : 0;
        const stClass = statusMap[p.status] || 'ongoing';
        const fillGradient = colorPalettes[idx % colorPalettes.length];
        const imgHtml = p.project_image 
          ? `<img src="${BASE_PATH}/${esc(p.project_image)}" class="dash-rp-img" alt="${esc(p.name)}" onerror="this.outerHTML='<div class=\\'dash-rp-img\\' style=\\'display:flex;align-items:center;justify-content:center;font-size:32px;color:#94A3B8;\\'>&#127968;</div>'">`
          : `<div class="dash-rp-img" style="display:flex;align-items:center;justify-content:center;font-size:32px;color:#94A3B8;">&#127968;</div>`;
        const budgetVal = p.estimated_budget > 0 ? p.estimated_budget : (p.spent > 0 ? p.spent : 0);
        const locText = p.client_address || p.client_name || 'Dhaka';
        return `
        <a href="${BASE_PATH}/project-detail?id=${p.id}" class="dash-recent-project-card">
          <div class="dash-rp-img-wrap">
            ${imgHtml}
            <span class="dash-rp-badge ${stClass}">${esc(p.status || 'Ongoing')}</span>
          </div>
          <div class="dash-rp-body">
            <div class="dash-rp-name" title="${esc(p.name)}">${esc(p.name)}</div>
            <div class="dash-rp-loc" title="${esc(locText)}">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <span>${esc(locText)}</span>
            </div>
            <div class="dash-rp-budget-row">
              <span class="dash-rp-budget">${fmtTk(budgetVal)}</span>
              <span class="dash-rp-pct">${pct}%</span>
            </div>
            <div class="dash-rp-progress-bar">
              <div class="dash-rp-progress-fill" style="width:${pct}%;background:${fillGradient};"></div>
            </div>
          </div>
        </a>`;
      }).join('');
    }
  }

  // Recent Transactions
  const txnContainer = document.getElementById('dashRecentTxnList');
  if (txnContainer) {
    const txns = data.recent_transactions || [];
    if (!txns.length) {
      txnContainer.innerHTML = '<p style="padding:24px 0;text-align:center;color:#94A3B8;font-size:12.5px;">No recent transactions</p>';
    } else {
      const typeConfig = {
        purchase: { bg:'#D1FAE5', color:'#059669', isExpense:true, icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>' },
        contractor_payment: { bg:'#DBEAFE', color:'#2563EB', isExpense:true, icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="3"/><line x1="2" y1="10" x2="22" y2="10"/></svg>' },
        labor_payment: { bg:'#FEF3C7', color:'#D97706', isExpense:true, icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>' },
        client_payment: { bg:'#D1FAE5', color:'#059669', isExpense:false, icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>' }
      };
      txnContainer.innerHTML = txns.slice(0, 4).map(t => {
        const cfg = typeConfig[t.type] || { bg:'#F1F5F9', color:'#64748B', isExpense:true, icon:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>' };
        const amtClass = cfg.isExpense ? 'red' : 'green';
        const titleText = t.title || (cfg.isExpense ? 'Payment' : 'Income');
        const projText = t.project_name || '';
        return `
        <div class="dash-txn-item-clean">
          <div class="dash-txn-circle" style="background:${cfg.bg};color:${cfg.color};">${cfg.icon}</div>
          <div class="dash-txn-main">
            <div class="dash-txn-name" title="${esc(titleText)}">${esc(titleText)}</div>
            <div class="dash-txn-proj" title="${esc(projText)}">${esc(projText)}</div>
          </div>
          <div class="dash-txn-side">
            <div class="dash-txn-amount ${amtClass}">${fmtTk(t.amount)}</div>
            <div class="dash-txn-time">${fmtDateClean(t.tx_date)}</div>
          </div>
        </div>`;
      }).join('');
    }
  }
}

// 2. POPULATE MOBILE
function populateMobile(data) {
  // Mobile 3x2 Stat Cards
  const mProj = document.getElementById('mobStatProjects');
  if (mProj) mProj.textContent = data.total_projects || 0;
  const mExp = document.getElementById('mobStatExpenses');
  if (mExp) mExp.textContent = fmtTkShort(data.total_expenses_month || 0);
  const mPay = document.getElementById('mobStatPayments');
  if (mPay) mPay.textContent = fmtTkShort(data.total_payments_month || 0);
  const mCont = document.getElementById('mobStatContractors');
  if (mCont) mCont.textContent = data.total_contractors || 0;
  const mDue = document.getElementById('mobStatDue');
  if (mDue) mDue.textContent = fmtTkShort(data.total_due || 0);
  const mLabor = document.getElementById('mobStatLabor');
  if (mLabor) mLabor.textContent = data.labor_present || data.daily_labor_today || 0;

  // Mobile Recent Activity List
  const mobActList = document.getElementById('mobActivityList');
  if (mobActList) {
    const txns = data.recent_transactions || [];
    if (!txns.length) {
      mobActList.innerHTML = '<p style="padding:16px 0;text-align:center;color:#94A3B8;font-size:12px;">No recent activity</p>';
    } else {
      const typeIcons = {
        purchase: { bg:'#D1FAE5', icon:'&#128722;', isExp:true },
        contractor_payment: { bg:'#FEF3C7', icon:'&#128736;', isExp:true },
        labor_payment: { bg:'#DBEAFE', icon:'&#128170;', isExp:true },
        client_payment: { bg:'#D1FAE5', icon:'&#128176;', isExp:false }
      };
      mobActList.innerHTML = txns.slice(0, 4).map(t => {
        const ti = typeIcons[t.type] || { bg:'#F1F5F9', icon:'&#9679;', isExp:true };
        const amtStr = (ti.isExp ? '-Tk. ' : '+Tk. ') + parseFloat(t.amount || 0).toLocaleString('en-BD', {maximumFractionDigits:0});
        return `
        <div class="mob-activity-item">
          <div class="mob-activity-icon" style="background:${ti.bg};">${ti.icon}</div>
          <div class="mob-activity-details">
            <div class="mob-activity-title">${esc(t.title || 'Transaction')}</div>
            <div class="mob-activity-sub">${esc(t.project_name || '')}</div>
          </div>
          <div class="mob-activity-right">
            <div class="mob-activity-amount" style="color:${ti.isExp?'#EF4444':'#059669'};">${amtStr}</div>
            <div class="mob-activity-date">${fmtDateShort(t.tx_date)}</div>
          </div>
        </div>`;
      }).join('');
    }
  }

  // Mobile Recent Projects Carousel
  const mobProjCarousel = document.getElementById('mobProjectsCarousel');
  if (mobProjCarousel) {
    const projects = data.recent_projects || [];
    if (!projects.length) {
      mobProjCarousel.innerHTML = '<p style="padding:16px;color:#94A3B8;font-size:12px;">No projects yet</p>';
    } else {
      mobProjCarousel.innerHTML = projects.slice(0, 5).map(p => {
        const pct = typeof p.progress === 'number' ? p.progress : 0;
        const img = p.project_image 
          ? `<img src="${BASE_PATH}/${esc(p.project_image)}" class="mob-project-img" alt="${esc(p.name)}" onerror="this.outerHTML='<div class=\\'mob-project-img\\' style=\\'display:flex;align-items:center;justify-content:center;font-size:28px;color:#94A3B8;\\'>&#127968;</div>'">`
          : `<div class="mob-project-img" style="display:flex;align-items:center;justify-content:center;font-size:28px;color:#94A3B8;">&#127968;</div>`;
        const spentVal = p.spent > 0 ? p.spent : (p.estimated_budget > 0 ? p.estimated_budget : 0);
        return `
        <a href="${BASE_PATH}/project-detail?id=${p.id}" class="mob-project-card">
          <div class="mob-project-img-wrap">
            ${img}
            <span class="mob-project-badge">${esc(p.status || 'Ongoing')}</span>
          </div>
          <div class="mob-project-body">
            <div class="mob-project-name">${esc(p.name)}</div>
            <div class="mob-project-loc">&#128205; ${esc(p.client_name || p.client_address || '')}</div>
            <div class="mob-project-budget">${fmtTk(spentVal)}</div>
            <div class="mob-project-progress-bar">
              <div class="mob-project-progress-fill" style="width:${pct}%;"></div>
            </div>
          </div>
        </a>`;
      }).join('');
    }
  }

  // Mobile Today Summary Card
  const mSumProj = document.getElementById('mobSumActiveProj');
  if (mSumProj) mSumProj.textContent = data.ongoing || data.total_projects || '0';
  const mSumPay = document.getElementById('mobSumTodayPay');
  if (mSumPay) mSumPay.textContent = fmtTk(data.today_payments || 0);
  const mSumLab = document.getElementById('mobSumLaborPres');
  if (mSumLab) mSumLab.textContent = (data.daily_labor_today || data.labor_present || 0) + ' / ' + (data.total_workers || 4);
}

// MOBILE IN-PAGE CALENDAR
async function renderMobileCalendar(monthDate) {
  const widget = document.getElementById('mobCalendarWidget');
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

  let html = `
  <div class="mob-cal-header">
    <div class="mob-cal-title">${monthNames[month]} ${year}</div>
    <div class="mob-cal-nav">
      <button type="button" class="mob-cal-btn" onclick="mobCalPrevMonth()">&#8249;</button>
      <button type="button" class="mob-cal-btn" onclick="mobCalNextMonth()">&#8250;</button>
      <button type="button" class="mob-cal-add-btn" onclick="showAddScheduleModal();return false;" title="Add Schedule">+</button>
    </div>
  </div>
  <div class="mob-cal-grid">`;

  html += days.map(d => `<div class="mob-cal-day-name">${d}</div>`).join('');
  for (let i = 0; i < firstDay; i++) {
    html += '<div class="mob-cal-day" style="opacity:0.2;"></div>';
  }
  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const isToday = dateStr === TODAY;
    const hasSch = scheduleDates.has(dateStr);
    html += `<div class="mob-cal-day${isToday ? ' today' : ''}${hasSch ? ' has-schedule' : ''}" onclick="onMobCalDayClick('${dateStr}')">${d}</div>`;
  }
  html += '</div>';
  widget.innerHTML = html;
}

window.mobCalPrevMonth = function() {
  mobCurrentCalMonth.setMonth(mobCurrentCalMonth.getMonth() - 1);
  renderMobileCalendar(mobCurrentCalMonth);
};

window.mobCalNextMonth = function() {
  mobCurrentCalMonth.setMonth(mobCurrentCalMonth.getMonth() + 1);
  renderMobileCalendar(mobCurrentCalMonth);
};

window.onMobCalDayClick = function(dateStr) {
  loadMobDaySchedules(dateStr);
  showAddScheduleModal(dateStr);
};

async function loadMobDaySchedules(date) {
  try {
    const r = await fetch(BASE_PATH + '/api/schedules.php?action=list&date=' + date);
    const d = await r.json();
    const list = document.getElementById('mobTodayScheduleList');
    const title = document.getElementById('mobTodayTitle');
    if (title) title.textContent = (date === TODAY ? 'Today' : fmtDateShort(date));
    if (!list) return;
    if (d.success && d.data && d.data.length) {
      list.innerHTML = d.data.map(s => `
        <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid #F1F5F9;width:100%;">
          <div>
            <div style="font-size:12.5px;font-weight:600;color:#0F172A;">${esc(s.description)}</div>
            <div style="font-size:11px;color:#64748B;">${esc(s.category || 'General')}</div>
          </div>
          <span style="font-size:10.5px;background:#EDE9FE;color:#7C3AED;padding:2px 6px;border-radius:4px;">${esc(s.category || 'General')}</span>
        </div>`).join('');
    } else {
      list.innerHTML = '<p style="font-size:12px;color:#94A3B8;text-align:center;margin:8px 0;">No schedule today</p>';
    }
  } catch(e) {}
}

// Helpers
function fmtTk(n) {
  return 'Tk. ' + parseFloat(n || 0).toLocaleString('en-BD', { maximumFractionDigits: 0 });
}

function fmtTkShort(n) {
  return 'Tk. ' + parseFloat(n || 0).toLocaleString('en-BD', { maximumFractionDigits: 0 });
}

function esc(s) {
  return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmtDateClean(d) {
  if (!d) return '';
  const dt = new Date(d);
  if (isNaN(dt.getTime())) return d;
  return dt.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function fmtDateShort(d) {
  if (!d) return '';
  const dt = new Date(d);
  if (isNaN(dt.getTime())) return d;
  return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
}

function initDashboard() {
  loadDashboard();
  renderMobileCalendar(mobCurrentCalMonth);
  loadMobDaySchedules(TODAY);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDashboard);
} else {
  initDashboard();
}
</script>

<?php
include __DIR__ . '/../includes/footer.php';
?>
