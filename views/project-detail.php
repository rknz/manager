<?php
// views/project-detail.php  v2.0
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$project_id = intval($_GET['id'] ?? 0);
if (!$project_id) { header('Location: ' . $basePath . '/projects'); exit; }
$stmt = $pdo->prepare("SELECT * FROM app_projects WHERE id=? AND is_deleted=0");
$stmt->execute([$project_id]);
$project = $stmt->fetch();
if (!$project) { header('Location: ' . $basePath . '/projects'); exit; }
$pageTitle = $project['name'];
$activeNav = 'projects';

// Load contractors, workers for dropdowns
$stmtC = $pdo->prepare("SELECT c.id,c.name,c.trade FROM app_contractors c JOIN app_project_contractors pc ON pc.contractor_id=c.id WHERE pc.project_id=? ORDER BY c.name");
$stmtC->execute([$project_id]);
$contractors = $stmtC->fetchAll();
$all_contractors = $pdo->query("SELECT id,name,trade FROM app_contractors WHERE is_active=1 ORDER BY name")->fetchAll();
$workers = $pdo->query("SELECT id,name,trade,default_daily_rate FROM app_workers WHERE is_active=1 ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id,name FROM app_categories ORDER BY name")->fetchAll();
$all_projects = $pdo->query("SELECT id,name FROM app_projects WHERE is_deleted=0 ORDER BY name")->fetchAll();

// Pre-calculate initial stats directly in PHP for instant 0ms render
$stmtSumPurch = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM app_supply_purchases WHERE project_id=? AND is_deleted=0");
$stmtSumPurch->execute([$project_id]);
$init_purchases = (float)$stmtSumPurch->fetchColumn();

$stmtSumCont = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_contractor_advances WHERE project_id=? AND is_deleted=0");
$stmtSumCont->execute([$project_id]);
$init_cont_paid = (float)$stmtSumCont->fetchColumn();

$stmtSumLabor = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_worker_payments WHERE project_id=? AND is_deleted=0");
$stmtSumLabor->execute([$project_id]);
$init_labor_paid = (float)$stmtSumLabor->fetchColumn();

$stmtSumClient = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM app_client_payments WHERE project_id=? AND is_deleted=0");
$stmtSumClient->execute([$project_id]);
$init_client_paid = (float)$stmtSumClient->fetchColumn();

$stmtSumBill = $pdo->prepare("SELECT COALESCE(SUM(grand_total),0) FROM app_contractor_bills WHERE project_id=? AND is_deleted=0");
$stmtSumBill->execute([$project_id]);
$init_cont_billed = (float)$stmtSumBill->fetchColumn();

$init_budget = (float)($project['estimated_budget'] ?? 0);
$init_total_expense = $init_purchases + $init_cont_paid + $init_labor_paid;
$init_client_due = $init_budget - $init_client_paid;
$init_total_payment = $init_cont_paid + $init_labor_paid;
$init_days_running = '-';
if (!empty($project['start_date'])) {
    $diff = time() - strtotime($project['start_date']);
    if ($diff >= 0) {
        $init_days_running = floor($diff / 86400) . ' days';
    }
}

// Fetch initial purchases for instant table display
$stmtPurch = $pdo->prepare("SELECT * FROM app_supply_purchases WHERE project_id=? AND is_deleted=0 ORDER BY purchase_date DESC, id DESC");
$stmtPurch->execute([$project_id]);
$initialPurchases = $stmtPurch->fetchAll(PDO::FETCH_ASSOC);

// Resolve project image with fallback to primary image from app_project_images
$projectHeroImg = null;
if (!empty($project['project_image'])) {
    $cleanImg = str_replace('\\', '/', trim($project['project_image']));
    if (str_starts_with($cleanImg, 'http://') || str_starts_with($cleanImg, 'https://')) {
        $projectHeroImg = $cleanImg;
    } else {
        $projectHeroImg = $basePath . '/' . ltrim($cleanImg, '/');
    }
}
if (empty($projectHeroImg)) {
    try {
        $imgStmt = $pdo->prepare("SELECT image_path FROM app_project_images WHERE project_id=? ORDER BY is_primary DESC, id DESC LIMIT 1");
        $imgStmt->execute([$project_id]);
        $fallbackImg = $imgStmt->fetchColumn();
        if (!empty($fallbackImg)) {
            $cleanImg = str_replace('\\', '/', trim($fallbackImg));
            $projectHeroImg = $basePath . '/' . ltrim($cleanImg, '/');
        }
    } catch (\Throwable $e) {}
}

include __DIR__ . '/../includes/header.php';
?>

<!-- PROJECT HEADER CARD -->
<div class="card mb-6 animate-fade-in project-detail-header-card" style="overflow:hidden;">
  <div class="project-hero-container">
    <?php if(!empty($projectHeroImg)): ?>
    <img src="<?= htmlspecialchars($projectHeroImg) ?>" class="project-hero-img" alt="<?= htmlspecialchars($project['name']) ?>" onerror="this.style.display='none'; var ph=document.getElementById('projectHeroPlaceholder'); if(ph) ph.style.display='flex';">
    <div class="project-hero-placeholder" id="projectHeroPlaceholder" style="display:none;">
      <span style="font-size:64px;opacity:.4;">&#127968;</span>
    </div>
    <?php else: ?>
    <div class="project-hero-placeholder">
      <span style="font-size:64px;opacity:.4;">&#127968;</span>
    </div>
    <?php endif; ?>
    <div class="project-hero-overlay">
      <div class="project-hero-content">
        <div style="flex:1;min-width:240px;">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
            <h1 style="color:#fff;font-size:24px;font-weight:800;line-height:1.2;margin:0;"><?=htmlspecialchars($project['name'])?></h1>
            <?php
            $statusBg = ['Ongoing'=>'#DBEAFE','Completed'=>'#D1FAE5','On Hold'=>'#FEF3C7','Cancelled'=>'#FEE2E2'];
            $statusColor = ['Ongoing'=>'#1D4ED8','Completed'=>'#065F46','On Hold'=>'#92400E','Cancelled'=>'#991B1B'];
            $st = $project['status'];
            ?>
            <span style="background:<?=$statusBg[$st]??'#f3f4f6'?>;color:<?=$statusColor[$st]??'#374151'?>;padding:3px 12px;border-radius:9999px;font-size:11px;font-weight:700;"><?=$st?></span>
          </div>
          <div class="project-hero-meta">
            <span><i class="fa-solid fa-user" style="color:#A78BFA;margin-right:4px;"></i> <strong><?=htmlspecialchars($project['client_name'])?></strong></span>
            <?php if($project['client_phone']): ?><a href="tel:<?=htmlspecialchars($project['client_phone'])?>"><i class="fa-solid fa-phone" style="color:#60A5FA;margin-right:4px;"></i> <?=htmlspecialchars($project['client_phone'])?></a><?php endif; ?>
            <?php if($project['address']): ?><span><i class="fa-solid fa-location-dot" style="color:#F87171;margin-right:4px;"></i> <?=htmlspecialchars($project['address'])?></span><?php endif; ?>
            <?php if(!empty($project['start_date']) || !empty($project['end_date'])): ?>
            <span><i class="fa-regular fa-calendar-days" style="color:#38BDF8;margin-right:4px;"></i> <?= !empty($project['start_date']) ? date('d M Y', strtotime($project['start_date'])) : '-' ?> &rarr; <?= !empty($project['end_date']) ? date('d M Y', strtotime($project['end_date'])) : 'Ongoing' ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <!-- Action Buttons Row at bottom of card -->
      <div class="project-hero-actions">
        <label class="btn btn-hero-action" title="Upload project image">
          <i class="fa-solid fa-cloud-arrow-up"></i> Upload
          <input type="file" id="projectImgUpload" accept="image/*" style="display:none;" onchange="uploadProjectImage(this)">
        </label>
        <button onclick="openModal('editProjectModal')" class="btn btn-hero-action"><i class="fa-solid fa-pen"></i> Edit</button>
        <a href="<?= $basePath ?>/projects" class="btn btn-hero-action"><i class="fa-solid fa-arrow-left"></i> Back</a>
      </div>
    </div>
  </div>
  <!-- 8 Stats Grid -->
  <div class="project-header-stats">
    <div class="stat-card-item">
      <div class="stat-icon-wrap" style="background:#FEE2E2;color:#DC2626;"><i class="fa-solid fa-wallet"></i></div>
      <div class="stat-val" style="color:var(--primary);" id="hdrBudget"><?= 'Tk. ' . number_format($init_budget, 0, '.', ',') ?></div>
      <div class="stat-lbl">Budget</div>
    </div>
    <div class="stat-card-item">
      <div class="stat-icon-wrap" style="background:#FFEDD5;color:#EA580C;"><i class="fa-solid fa-file-invoice-dollar"></i></div>
      <div class="stat-val" style="color:var(--danger);" id="hdrExpense"><?= 'Tk. ' . number_format($init_total_expense, 0, '.', ',') ?></div>
      <div class="stat-lbl">Total Expense</div>
    </div>
    <div class="stat-card-item">
      <div class="stat-icon-wrap" style="background:#DCFCE7;color:#16A34A;"><i class="fa-solid fa-money-bill-wave"></i></div>
      <div class="stat-val" style="color:var(--success);" id="hdrReceived"><?= 'Tk. ' . number_format($init_client_paid, 0, '.', ',') ?></div>
      <div class="stat-lbl">Client Paid</div>
    </div>
    <div class="stat-card-item">
      <div class="stat-icon-wrap" style="background:#FEF9C3;color:#CA8A04;"><i class="fa-solid fa-coins"></i></div>
      <div class="stat-val" style="color:var(--warning);" id="hdrDue"><?= 'Tk. ' . number_format($init_client_due, 0, '.', ',') ?></div>
      <div class="stat-lbl">Client Due</div>
    </div>
    <div class="stat-card-item">
      <div class="stat-icon-wrap" style="background:#DBEAFE;color:#2563EB;"><i class="fa-solid fa-calendar-days"></i></div>
      <div class="stat-val" style="color:var(--info);" id="hdrDays"><?= htmlspecialchars($init_days_running) ?></div>
      <div class="stat-lbl">Days Running</div>
    </div>
    <div class="stat-card-item">
      <div class="stat-icon-wrap" style="background:#F3E8FF;color:#9333EA;"><i class="fa-solid fa-cart-shopping"></i></div>
      <div class="stat-val" style="color:var(--primary);" id="hdrPurchase"><?= 'Tk. ' . number_format($init_purchases, 0, '.', ',') ?></div>
      <div class="stat-lbl">Total Purchase</div>
    </div>
    <div class="stat-card-item">
      <div class="stat-icon-wrap" style="background:#E0E7FF;color:#4F46E5;"><i class="fa-solid fa-file-lines"></i></div>
      <div class="stat-val" style="color:var(--warning);" id="hdrBilling"><?= 'Tk. ' . number_format($init_cont_billed, 0, '.', ',') ?></div>
      <div class="stat-lbl">Total Billing</div>
    </div>
    <div class="stat-card-item">
      <div class="stat-icon-wrap" style="background:#D1FAE5;color:#059669;"><i class="fa-solid fa-circle-check"></i></div>
      <div class="stat-val" style="color:var(--success);" id="hdrPayment"><?= 'Tk. ' . number_format($init_total_payment, 0, '.', ',') ?></div>
      <div class="stat-lbl">Total Payment</div>
    </div>
  </div>
</div>

<!-- TABS -->
<div class="tabs project-tabs-nav" id="mainTabs">
  <button class="tab-btn active" onclick="switchTab(this,'tabPurchases')"><i class="fa-solid fa-cart-shopping" style="color:#EF4444;margin-right:4px;"></i> Purchases</button>
  <button class="tab-btn" onclick="switchTab(this,'tabBilling')"><i class="fa-solid fa-wrench" style="color:#64748B;margin-right:4px;"></i> Billing</button>
  <button class="tab-btn" onclick="switchTab(this,'tabAttendance')"><i class="fa-solid fa-clipboard-user" style="color:#EC4899;margin-right:4px;"></i> Attendance</button>
  <button class="tab-btn" onclick="switchTab(this,'tabPayments')"><i class="fa-solid fa-sack-dollar" style="color:#F59E0B;margin-right:4px;"></i> Payments</button>
  <button class="tab-btn" onclick="switchTab(this,'tabSchedules')"><i class="fa-solid fa-calendar-days" style="color:#3B82F6;margin-right:4px;"></i> Schedule</button>
  <button class="tab-btn" onclick="switchTab(this,'tabReports')"><i class="fa-solid fa-chart-line" style="color:#8B5CF6;margin-right:4px;"></i> Reports</button>
  <button class="tab-btn" onclick="switchTab(this,'tabPrintouts')"><i class="fa-solid fa-print" style="color:#475569;margin-right:4px;"></i> Print Final-Bill</button>
</div>


<!-- TAB: PURCHASES -->
<div id="tabPurchases" class="tab-content active">
  <div class="filter-bar project-tab-filter">
    <div class="search-input-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" class="form-input" id="purchSearchQ" placeholder="Search items..." list="globalPurchItemList" oninput="loadPurchases()"><datalist id="globalPurchItemList"></datalist></div>
    <select id="purchCatFilter" class="form-select filter-cat" onchange="loadPurchases()"><option value="">All Categories</option><?php foreach($categories as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach; ?></select>
    <div class="date-range-group">
      <input type="text" id="purchFrom" class="form-input smart-date" placeholder="From" data-date-target="purchFromH" onchange="loadPurchases()"><input type="hidden" id="purchFromH">
      <input type="text" id="purchTo"   class="form-input smart-date" placeholder="To"   data-date-target="purchToH"   onchange="loadPurchases()"><input type="hidden" id="purchToH">
    </div>
    <button class="btn btn-primary btn-sm filter-action-btn" onclick="openModal('addPurchModal')">+ Add Purchase</button>
  </div>
  <div class="card">
    <div class="card-header"><h3>Purchase List</h3><span id="purchTotal" class="badge badge-danger"><?= 'Tk. ' . number_format($init_purchases, 0, '.', ',') ?></span></div>
    <div class="table-wrapper hide-on-mobile">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Item</th><th>Category</th><th>Qty</th><th>Rate</th><th class="text-right">Total</th><th>Supplier</th><th></th></tr></thead>
        <tbody id="purchTable">
          <?php if(empty($initialPurchases)): ?>
          <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">No purchases found</td></tr>
          <?php else: foreach($initialPurchases as $p): ?>
          <tr>
            <td><?= !empty($p['purchase_date']) ? date('d M Y', strtotime($p['purchase_date'])) : '-' ?></td>
            <td><strong><?= htmlspecialchars($p['item_name'] ?? '') ?></strong><?= !empty($p['board_thickness']) ? '<br><span class="text-xs text-muted">Thickness: '.htmlspecialchars($p['board_thickness']).'</span>' : (!empty($p['board_type']) && $p['board_type'] !== ($p['item_name']??'') ? '<br><span class="text-xs text-muted">'.htmlspecialchars($p['board_type']).'</span>' : '') ?></td>
            <td><span class="badge badge-neutral"><?= htmlspecialchars($p['supply_category'] ?: '-') ?></span></td>
            <td><?= (float)($p['quantity'] ?? 0) ?> <?= htmlspecialchars($p['unit'] ?? '') ?></td>
            <td><?= 'Tk. ' . number_format((float)($p['rate'] ?? 0), 0, '.', ',') ?></td>
            <td class="td-amount text-danger"><?= 'Tk. ' . number_format((float)($p['total'] ?? 0), 0, '.', ',') ?></td>
            <td><?= htmlspecialchars($p['supplier'] ?: '-') ?></td>
            <td class="td-actions">
              <button class="btn btn-ghost btn-sm btn-icon" title="Edit" onclick="openEditPurch(<?= $p['id'] ?>)">&#9998;</button>
              <button class="btn btn-ghost btn-sm btn-icon" title="Delete" onclick="delPurch(<?= $p['id'] ?>)">&#10006;</button>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
        <tfoot><tr><td colspan="5" style="text-align:right;">Total:</td><td id="purchTableTotal" class="td-amount"><?= 'Tk. ' . number_format($init_purchases, 0, '.', ',') ?></td><td colspan="2"></td></tr></tfoot>
      </table>
    </div>
    <div id="purchMobileList" class="mobile-cards-list hide-on-desktop">
      <?php if(empty($initialPurchases)): ?>
      <div class="card" style="text-align:center;padding:24px 16px;color:var(--text-muted);border-radius:12px;border:1px solid var(--border);">No purchases found</div>
      <?php else: foreach($initialPurchases as $p): ?>
      <div class="mobile-record-card">
        <div class="mrc-header">
          <div class="mrc-header-left">
            <span class="mrc-date"><i class="fa-regular fa-calendar" style="color:var(--text-muted);"></i> <?= !empty($p['purchase_date']) ? date('d M Y', strtotime($p['purchase_date'])) : '-' ?></span>
            <span class="badge badge-neutral" style="font-size:10px;padding:2px 8px;"><?= htmlspecialchars($p['supply_category'] ?: '-') ?></span>
          </div>
          <div class="mrc-corner-actions">
            <button type="button" class="mrc-icon-btn mrc-btn-edit" title="Edit" onclick="openEditPurch(<?= $p['id'] ?>)">&#9998;</button>
            <button type="button" class="mrc-icon-btn mrc-btn-del" title="Delete" onclick="delPurch(<?= $p['id'] ?>)">&#10006;</button>
          </div>
        </div>
        <div class="mrc-title-row">
          <div class="mrc-title">
            <?= htmlspecialchars($p['item_name'] ?? '') ?>
            <?php if(!empty($p['board_thickness'])): ?>
              <span class="mrc-thick"><?= htmlspecialchars($p['board_thickness']) ?> mm</span>
            <?php elseif(!empty($p['board_type']) && $p['board_type'] !== ($p['item_name']??'')): ?>
              <span class="mrc-thick"><?= htmlspecialchars($p['board_type']) ?></span>
            <?php endif; ?>
          </div>
          <div class="mrc-amount text-danger"><?= 'Tk. ' . number_format((float)($p['total'] ?? 0), 0, '.', ',') ?></div>
        </div>
        <div class="mrc-meta-row">
          <div class="mrc-meta-item">
            <span>Qty:</span> <strong><?= (float)($p['quantity'] ?? 0) ?> <?= htmlspecialchars($p['unit'] ?? '') ?></strong> @ <?= 'Tk. ' . number_format((float)($p['rate'] ?? 0), 0, '.', ',') ?>
          </div>
          <div class="mrc-meta-item">
            <i class="fa-solid fa-store" style="font-size:10px;color:var(--text-muted);"></i> <?= htmlspecialchars($p['supplier'] ?: '—') ?>
          </div>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- TAB: BILLING -->
<div id="tabBilling" class="tab-content" style="display:none;">
  <div class="filter-bar project-tab-filter">
    <select id="billContFilter" class="form-select filter-cat" onchange="onBillingContactChange()"><option value="">All Contractors</option><?php foreach($contractors as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach; ?></select>
    <div class="filter-action-group" style="display:flex;gap:8px;flex-wrap:wrap;">
      <button class="btn btn-primary btn-sm" onclick="openAddAdvanceModal()">+ Add Advance</button>
      <?php if(!empty($all_contractors)): ?><button class="btn btn-outline btn-sm" onclick="openModal('addContractorToProjectModal')">+ Add Contractor</button><?php endif; ?>
      <button class="btn btn-secondary btn-sm" id="btnPrintAdv" onclick="printContractorAdvances()" style="display:none;">&#128247; Print Advances</button>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h3>Contractor Advances</h3><span id="billTotal" class="badge badge-warning">Tk. 0</span></div>
    <div class="table-wrapper hide-on-mobile">
      <table class="data-table">
        <thead><tr><th>Contractor</th><th>Amount</th><th>Method</th><th>Who Paid</th><th>Date</th><th></th></tr></thead>
        <tbody id="billTable"></tbody>
        <tfoot><tr><td style="text-align:right;">Total:</td><td id="billTableTotal" class="td-amount">Tk. 0</td><td colspan="4"></td></tr></tfoot>
      </table>
    </div>
    <div id="billMobileList" class="mobile-cards-list hide-on-desktop"></div>
  </div>
</div>

<!-- TAB: ATTENDANCE -->
<div id="tabAttendance" class="tab-content" style="display:none;">
  <div class="filter-bar project-tab-filter">
    <select id="attWorkerFilter" class="form-select filter-cat" onchange="loadAttendance()"><option value="">All Workers</option><?php foreach($workers as $w): ?><option value="<?=$w['id']?>"><?=htmlspecialchars($w['name'])?></option><?php endforeach; ?></select>
    <div class="date-range-group">
      <input type="text" id="attFrom" class="form-input smart-date" placeholder="From" data-date-target="attFromH" onchange="loadAttendance()"><input type="hidden" id="attFromH">
      <input type="text" id="attTo"   class="form-input smart-date" placeholder="To"   data-date-target="attToH"   onchange="loadAttendance()"><input type="hidden" id="attToH">
    </div>
    <div class="filter-action-group" style="display:flex;gap:8px;flex-wrap:wrap;">
      <button class="btn btn-primary btn-sm" onclick="openAttendanceEntry(PID, afterAttendanceSave)">+ Add Attendance</button>
      <button class="btn btn-outline btn-sm" onclick="openModal('addLaborPayModal')">&#128176; Pay</button>
      <button class="btn btn-secondary btn-sm" onclick="openModal('workerReportModal')">&#128247; Print Reports</button>
    </div>
  </div>
  <div class="two-col" style="align-items:start;">
    <div class="card mb-4">
      <div class="card-header"><h3>Attendance Records</h3><span id="attEarnedTotal" class="badge badge-success">Tk. 0</span></div>
      <div class="table-wrapper hide-on-mobile">
        <table class="data-table">
          <thead><tr><th>Date</th><th>Worker</th><th>Type</th><th>Rate</th><th class="text-right">Earned</th><th></th></tr></thead>
          <tbody id="attTable"></tbody>
          <tfoot><tr><td colspan="4" style="text-align:right;">Total:</td><td id="attTableTotal" class="td-amount">Tk. 0</td><td></td></tr></tfoot>
        </table>
      </div>
      <div id="attMobileList" class="mobile-cards-list hide-on-desktop"></div>
    </div>
    <div>
      <div class="card mb-4">
        <div class="card-header"><h3>Worker Summary</h3></div>
        <div id="workerSummary"></div>
      </div>
      <div class="card">
        <div class="card-header"><h3>Payment Records</h3><span id="attPaidTotal" class="badge badge-warning">Tk. 0</span></div>
        <div class="table-wrapper hide-on-mobile">
          <table class="data-table">
            <thead><tr><th>Worker</th><th>Amount</th><th>Date</th><th></th></tr></thead>
            <tbody id="laborPayTable"></tbody>
          </table>
        </div>
        <div id="laborPayMobileList" class="mobile-cards-list hide-on-desktop"></div>
      </div>
    </div>
  </div>
</div>

<!-- TAB: CLIENT PAYMENTS -->
<div id="tabPayments" class="tab-content" style="display:none;">
  <div class="filter-bar">
    <button class="btn btn-primary btn-sm" onclick="openModal('addClientPayModal')">+ Add Client Payment</button>
  </div>
  <div class="card">
    <div class="card-header"><h3>Client Payments</h3><span id="cpTotal" class="badge badge-success">Tk. 0</span></div>
    <div class="table-wrapper">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Method</th><th class="text-right">Amount</th><th>Notes</th><th></th></tr></thead>
        <tbody id="cpTable"></tbody>
        <tfoot><tr><td colspan="2" style="text-align:right;">Total:</td><td id="cpTableTotal" class="td-amount">Tk. 0</td><td colspan="2"></td></tr></tfoot>
      </table>
    </div>
  </div>
</div>

<!-- TAB: SCHEDULES -->
<div id="tabSchedules" class="tab-content" style="display:none;">
  <div class="filter-bar">
    <button class="btn btn-primary btn-sm" onclick="openProjectScheduleModal()">+ Add Schedule</button>
  </div>
  <div class="card">
    <div class="card-header"><h3>Project Schedules</h3></div>
    <div class="table-wrapper">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Status</th><th></th></tr></thead>
        <tbody id="schTable"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- TAB: REPORTS -->
<div id="tabReports" class="tab-content" style="display:none;">
  <div class="two-col">
    <div class="card"><div class="card-header"><h3>&#128200; Project Summary</h3></div>
      <div class="card-body" id="reportSummary"><div class="loading-state"><div class="spinner"></div></div></div>
    </div>
    <div class="card"><div class="card-header"><h3>&#128722; Purchase by Category</h3></div>
      <div class="card-body"><div id="purchByCat"></div></div>
    </div>
  </div>
</div>

<!-- TAB: PRINTOUTS -->
<div id="tabPrintouts" class="tab-content" style="display:none;">
  <div class="filter-bar">
    <button class="btn btn-primary btn-sm" onclick="openFinalBillModal('contractor')">&#128247; Generate Final Bill</button>
  </div>
  <div class="card">
    <div class="card-header"><h3>Saved Printouts</h3></div>
    <div class="card-body" id="printoutsList"><div class="loading-state"><div class="spinner"></div></div></div>
  </div>
</div>


<!--  MODALS  -->

<!-- ADD PURCHASE -->
<div class="modal-overlay" id="addPurchModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#128722; Add Purchase</h3><div class="modal-close" onclick="closeModal('addPurchModal')">&times;</div></div>
  <div class="modal-body">
    <div class="two-col">
      <div class="form-group"><label class="form-label">Date</label><input type="text" id="pDate" class="form-input smart-date" placeholder="e.g. <?=date('j/n/y')?>" data-date-target="pDateH"><input type="hidden" id="pDateH" value="<?=date('Y-m-d')?>"></div>
      <div class="form-group"><label class="form-label">Category</label>
        <select id="pCat" class="form-select" onchange="toggleBoardFields()">
          <option value="">-- Select --</option>
          <option value="Board & Wood">Board & Wood</option>
          <option value="Paint">Paint</option>
          <option value="Electrical & Sanitary">Electrical & Sanitary</option>
          <option value="Thai & Glass">Thai & Glass</option>
          <option value="Hardware">Hardware</option>
          <option value="Labour">Labour</option>
          <option value="Other">Other</option>
        </select></div>
    </div>
    <div class="form-group">
      <label class="form-label" id="pItemLabel">Item Name <span class="required">*</span></label>
      <input type="text" id="pItem" class="form-input" placeholder="Item name" list="pItemList" autocomplete="off">
      <datalist id="pItemList"></datalist>
      <datalist id="pBoardSuggestions">
        <option>Melamine</option><option>Partex</option><option>PVC</option><option>Gorjon</option><option>MDF</option><option>Ply</option><option>Plex</option><option>HPL</option>
      </datalist>
    </div>
    <div id="pBoardFields" class="form-group" style="display:none; margin-bottom:14px;">
      <label class="form-label">Board Thickness (mm) <span class="required">*</span></label>
      <input type="text" id="pThick" class="form-input" placeholder="e.g. 12mm" list="thickList" onblur="if(this.value && isFinite(this.value)) this.value += 'mm'">
      <datalist id="thickList"><option>6mm</option><option>8mm</option><option>9mm</option><option>10mm</option><option>12mm</option><option>18mm</option><option>25mm</option></datalist>
    </div>
    <div class="three-col">
      <div class="form-group"><label class="form-label" id="pQtyLabel">Qty <span class="required">*</span></label><input type="number" id="pQty" class="form-input" step="0.01" oninput="calcPurchTotal()"></div>
      <div class="form-group" id="pUnitGroup"><label class="form-label">Unit</label><input type="text" id="pUnit" class="form-input" list="pUnitList" placeholder="pcs"><datalist id="pUnitList"><option>pcs</option><option>sft</option><option>rft</option><option>kg</option><option>ltr</option><option>set</option></datalist></div>
      <div class="form-group"><label class="form-label">Rate (Tk) <span class="required">*</span></label><input type="number" id="pRate" class="form-input" step="0.01" oninput="calcPurchTotal()"></div>
    </div>
    <div class="form-group"><label class="form-label">Supplier</label><input type="text" id="pSupplier" class="form-input" placeholder="Optional" list="pSupplierList"><datalist id="pSupplierList"></datalist></div>
    <div style="background:var(--primary-light);padding:12px 16px;border-radius:var(--radius-md);display:flex;justify-content:space-between;align-items:center;">
      <span style="font-weight:600;color:var(--primary);">Total</span>
      <span id="pTotalDisplay" style="font-family:'Poppins','Noto Sans Bengali','Hind Siliguri','Nirmala UI','Vrinda','Shonar Bangla',sans-serif;font-size:20px;font-weight:800;color:var(--primary);">Tk. 0</span>
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addPurchModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="savePurchase()">Save</button></div>
</div></div>

<!-- EDIT PURCHASE -->
<div class="modal-overlay" id="editPurchModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#9998; Edit Purchase</h3><div class="modal-close" onclick="closeModal('editPurchModal')">&times;</div></div>
  <div class="modal-body">
    <input type="hidden" id="epId">
    <div class="two-col">
      <div class="form-group"><label class="form-label">Date</label><input type="text" id="epDate" class="form-input smart-date" data-date-target="epDateH"><input type="hidden" id="epDateH"></div>
      <div class="form-group"><label class="form-label">Category</label>
        <select id="epCat" class="form-select" onchange="toggleEditBoardFields()">
          <option value="">--</option>
          <option value="Board & Wood">Board & Wood</option>
          <option value="Paint">Paint</option>
          <option value="Electrical & Sanitary">Electrical & Sanitary</option>
          <option value="Thai & Glass">Thai & Glass</option>
          <option value="Hardware">Hardware</option>
          <option value="Labour">Labour</option>
          <option value="Other">Other</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label" id="epItemLabel">Item Name <span class="required">*</span></label>
      <input type="text" id="epItem" class="form-input" list="pItemList">
    </div>
    <div id="epBoardFields" class="form-group" style="display:none; margin-bottom:14px;">
      <label class="form-label">Board Thickness (mm) <span class="required">*</span></label>
      <input type="text" id="epThick" class="form-input" placeholder="e.g. 12mm" list="thickList" onblur="if(this.value && isFinite(this.value)) this.value += 'mm'">
    </div>
    <div class="three-col">
      <div class="form-group"><label class="form-label" id="epQtyLabel">Qty <span class="required">*</span></label><input type="number" id="epQty" class="form-input" oninput="calcEditPurchTotal()"></div>
      <div class="form-group" id="epUnitGroup"><label class="form-label">Unit</label><input type="text" id="epUnit" class="form-input"></div>
      <div class="form-group"><label class="form-label">Rate <span class="required">*</span></label><input type="number" id="epRate" class="form-input" oninput="calcEditPurchTotal()"></div>
    </div>
    <div style="background:var(--primary-light);padding:12px 16px;border-radius:var(--radius-md);display:flex;justify-content:space-between;"><span style="font-weight:600;color:var(--primary);">Total</span><span id="epTotalDisplay" style="font-family:'Poppins','Noto Sans Bengali','Hind Siliguri','Nirmala UI','Vrinda','Shonar Bangla',sans-serif;font-weight:800;color:var(--primary);font-size:18px;">Tk. 0</span></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('editPurchModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="updatePurchase()">Update</button></div>
</div></div>

<!-- ADD ADVANCE -->
<div class="modal-overlay" id="addAdvModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#128176; Add Contractor Advance</h3><div class="modal-close" onclick="closeModal('addAdvModal')">&times;</div></div>
  <div class="modal-body">
    <div class="form-group"><label class="form-label">Contractor <span class="required">*</span></label><select id="advContractor" class="form-select"><option value="">--</option><?php foreach($all_contractors as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?> (<?=htmlspecialchars($c['trade'])?>)</option><?php endforeach; ?></select></div>
    <div class="two-col">
      <div class="form-group"><label class="form-label">Amount (Tk) <span class="required">*</span></label><input type="number" id="advAmount" class="form-input"></div>
      <div class="form-group"><label class="form-label">Date</label><input type="text" id="advDate" class="form-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="advDateH"><input type="hidden" id="advDateH" value="<?=date('Y-m-d')?>"></div>
    </div>
    <div class="two-col">
      <div class="form-group"><label class="form-label">Who Paid</label><input type="text" id="advWhoPaid" class="form-input" value="<?=htmlspecialchars($_SESSION['username']??'')?>"></div>
      <div class="form-group"><label class="form-label">Who Received</label><input type="text" id="advWhoRec" class="form-input"></div>
    </div>
    <div class="form-group"><label class="form-label">Notes</label><input type="text" id="advNotes" class="form-input"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addAdvModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="saveAdvance()">Save</button></div>
</div></div>

<!-- GENERATE FINAL BILL MODAL -->
<div class="modal-overlay" id="generateFinalBillModal">
  <div class="modal final-bill-modal" data-form-nav>
    <div class="fb-mobile-drag-handle"></div>
    <div class="fb-modal-header">
      <div class="fb-header-left">
        <div class="fb-header-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
          </svg>
        </div>
        <div class="fb-header-text">
          <h3 id="fbModalTitle">Final Bill &mdash; <span id="fbProjectName"><?= htmlspecialchars($project['name'] ?? 'Project') ?></span></h3>
          <p class="fb-header-subtitle">Generate and print final bill for completed projects.</p>
        </div>
      </div>
      <button type="button" class="fb-close-btn" onclick="closeModal('generateFinalBillModal')" aria-label="Close">&times;</button>
    </div>
    <div class="fb-modal-body">
      <form id="finalBillForm" action="<?= $basePath ?>/print_custom_bill" method="POST" target="_blank">
        <input type="hidden" name="project_id" id="fbProjectId" value="<?= $project_id ?>">
        <input type="hidden" name="items_json" id="fbItemsJson" value="[]">

        <div class="fb-top-grid">
          <div class="fb-field-group">
            <label class="fb-field-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="#9C1F24" stroke-width="2" width="16" height="16">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
              </svg>
              Bill Type
            </label>
            <select id="fbType" name="bill_type" class="fb-select" onchange="loadFbTargets()">
              <option value="contractor">Contractor Bill</option>
              <option value="labor">Labor / Worker Bill</option>
            </select>
          </div>

          <div class="fb-field-group">
            <label class="fb-field-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="#9C1F24" stroke-width="2" width="16" height="16">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              Select Person <span class="required" style="color:#DC2626;">*</span>
            </label>
            <select id="fbTarget" name="target_id" class="fb-select" onchange="fetchFinalBillData(this.value)" required>
              <option value="">-- Select --</option>
            </select>
          </div>

          <div class="fb-field-group">
            <label class="fb-field-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="#9C1F24" stroke-width="2" width="16" height="16">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              Bill Date
            </label>
            <div class="fb-date-input-wrap">
              <input type="text" id="fbDate" class="fb-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="fbDateH" value="<?=date('j/n/y')?>">
              <input type="hidden" id="fbDateH" name="bill_date" value="<?=date('Y-m-d')?>">
            </div>
          </div>

          <div class="fb-field-group fb-items-header-group">
            <label class="fb-field-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="#9C1F24" stroke-width="2" width="16" height="16">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
              </svg>
              Bill Items
            </label>
            <button type="button" class="fb-add-row-btn" onclick="addFbRow()">
              + Add Row
            </button>
          </div>
        </div>

        <div class="fb-table-section">
          <div class="fb-table-header">
            <div class="fb-th-col fb-col-check">
              <input type="checkbox" id="fbSelectAll" class="fb-checkbox" onchange="toggleSelectAllFb(this)">
            </div>
            <div class="fb-th-col fb-col-desc">Item / Description</div>
            <div class="fb-th-col fb-col-thick">Thickness</div>
            <div class="fb-th-col fb-col-qty">Qty</div>
            <div class="fb-th-col fb-col-rate">Rate</div>
            <div class="fb-th-col fb-col-total">Total</div>
            <div class="fb-th-col fb-col-del">
              <svg viewBox="0 0 24 24" fill="none" stroke="#9C1F24" stroke-width="2" width="16" height="16">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg>
            </div>
          </div>
          <div id="fbItemsContainer" class="fb-items-container"></div>
        </div>

        <div class="fb-group-action-row">
          <button type="button" class="fb-group-btn" onclick="groupSelectedRows()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
              <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
              <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
            </svg>
            Group Selected Rows
          </button>
        </div>

        <div class="fb-grand-total-strip">
          <div class="fb-gt-label">
            <svg viewBox="0 0 24 24" fill="none" stroke="#9C1F24" stroke-width="2" width="20" height="20">
              <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1z"></path>
              <line x1="8" y1="6" x2="16" y2="6"></line>
              <line x1="8" y1="10" x2="16" y2="10"></line>
              <line x1="8" y1="14" x2="12" y2="14"></line>
            </svg>
            <span>Grand Total</span>
          </div>
          <div class="fb-gt-val" id="fbGrandTotal">Tk. 0</div>
        </div>

        <div class="fb-modal-footer">
          <button type="button" class="fb-btn-cancel" onclick="closeModal('generateFinalBillModal')">Cancel</button>
          <button type="button" class="fb-btn-print" onclick="generateAndPrintFb()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <polyline points="6 9 6 2 18 2 18 9"></polyline>
              <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
              <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Generate & Print
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- PRINT ADVANCES PREVIEW (editable, print-preview only) -->
<div class="modal-overlay" id="printAdvancesModal"><div class="modal" data-form-nav style="max-width:760px;">
  <div class="modal-header"><h3>&#128247; Advance Payment Preview</h3><div class="modal-close" onclick="closeModal('printAdvancesModal')">&times;</div></div>
  <div class="modal-body">
    <div class="form-group">
      <label class="form-label" id="advPreviewTitle">Adjust payments below, then print. Changes only affect this printout.</label>
    </div>
    <div style="display:grid; grid-template-columns:96px 1fr 84px 110px 34px; gap:8px; font-size:12px; font-weight:700; color:var(--text-muted); padding:0 4px 6px;">
      <span>Date</span><span>Name</span><span>Role</span><span>Amount (Tk)</span><span></span>
    </div>
    <div id="advPreviewRows" style="max-height:380px; overflow:auto;"></div>
    <div style="display:flex; gap:10px; margin-top:12px; flex-wrap:wrap;">
      <button type="button" class="btn btn-secondary btn-sm" onclick="addAdvPvRow({group:'pay',date:TODAY,name:'',person_type:'worker',amount:''})">+ Add Payment</button>
    </div>
    <div style="background:var(--warning-bg);padding:12px;border-radius:var(--radius-md);margin-top:12px;display:flex;justify-content:space-between;">
      <span style="font-weight:700;">Total Payments</span>
      <span id="advPvTotal" style="font-weight:800;font-size:18px;">Tk. 0</span>
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('printAdvancesModal')">Cancel</button><button class="btn btn-primary" onclick="printAdvancePreview()">&#128247; Print PDF</button></div>
</div></div>

<!-- ADD ATTENDANCE (shared modular entry) -->
<?php
$attEntryWorkers = $workers;
$attEntryLocked  = $project_id;
include __DIR__ . '/../includes/attendance_entry.php';
$extraScripts = array_merge($extraScripts ?? [], ['attendance-entry.js']);
?>

<!-- WORKER REPORT MODAL -->
<div class="modal-overlay" id="workerReportModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#128247; Print Worker Reports</h3><div class="modal-close" onclick="closeModal('workerReportModal')">&times;</div></div>
  <div class="modal-body">
    <div class="form-group"><label class="form-label">Select Worker <span class="required">*</span></label>
      <select id="wrWorker" class="form-select">
        <option value="">Select Worker</option>
        <?php foreach($workers as $w): ?><option value="<?=$w['id']?>"><?=htmlspecialchars($w['name'])?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="two-col">
      <div class="form-group"><label class="form-label">From Date</label><input type="text" id="wrFrom" class="form-input smart-date" data-date-target="wrFromH"><input type="hidden" id="wrFromH"></div>
      <div class="form-group"><label class="form-label">To Date</label><input type="text" id="wrTo" class="form-input smart-date" data-date-target="wrToH"><input type="hidden" id="wrToH"></div>
    </div>
  </div>
  <div class="modal-footer" style="justify-content: space-between;">
    <button class="btn btn-secondary" onclick="closeModal('workerReportModal')">Cancel</button>
    <div style="display:flex; gap:10px;">
      <button class="btn btn-primary" onclick="printWorkerReport('attendance')">&#128247; Print Attendance</button>
      <button class="btn btn-outline" onclick="printWorkerReport('payments')">&#128247; Print Payments</button>
    </div>
  </div>
</div></div>

<!-- EDIT ATTENDANCE MODAL -->
<div class="modal-overlay" id="editAttModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#9998; Edit Attendance</h3><div class="modal-close" onclick="closeModal('editAttModal')">&times;</div></div>
  <div class="modal-body">
    <input type="hidden" id="eaId">
    <input type="hidden" id="eaWorker">
    <div class="form-group">
      <label class="form-label">Worker</label>
      <div style="font-weight:700;font-size:14px;padding:8px 12px;background:var(--bg-card);border:1px solid var(--border);border-radius:6px;" id="eaWorkerName">-</div>
    </div>
    <div class="two-col">
      <div class="form-group"><label class="form-label">Work Date <span class="required">*</span></label><input type="text" id="eaDate" class="form-input smart-date" data-date-target="eaDateH"><input type="hidden" id="eaDateH"></div>
      <div class="form-group"><label class="form-label">Attendance Status <span class="required">*</span></label>
        <select id="eaMultiplier" class="form-select" onchange="calcEditAttEarned()">
          <option value="1">Full Day (1.0x)</option>
          <option value="0.5">Half Day (0.5x)</option>
          <option value="1.5">Overtime (1.5x)</option>
          <option value="2">Double (2.0x)</option>
        </select>
      </div>
    </div>
    <div class="two-col">
      <div class="form-group"><label class="form-label">Daily Rate (Tk) <span class="required">*</span></label><input type="number" id="eaDailyRate" class="form-input" oninput="calcEditAttEarned()"></div>
      <div class="form-group"><label class="form-label">Earned (Tk)</label><input type="text" id="eaEarnedDisplay" class="form-input" readonly style="font-weight:700;color:var(--success);"></div>
    </div>
    <div class="form-group"><label class="form-label">Notes</label><input type="text" id="eaNotes" class="form-input" placeholder="e.g. Work notes"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('editAttModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="updateAttendance()">Save Changes</button></div>
</div></div>

<!-- ADD LABOR PAYMENT -->
<div class="modal-overlay" id="addLaborPayModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#128176; Pay Worker</h3><div class="modal-close" onclick="closeModal('addLaborPayModal')">&times;</div></div>
  <div class="modal-body">
    <div class="form-group"><label class="form-label">Worker <span class="required">*</span></label><select id="lpWorker" class="form-select"><option value="">--</option><?php foreach($workers as $w): ?><option value="<?=$w['id']?>"><?=htmlspecialchars($w['name'])?></option><?php endforeach; ?></select></div>
    <div class="two-col">
      <div class="form-group"><label class="form-label">Amount (Tk) <span class="required">*</span></label><input type="number" id="lpAmount" class="form-input"></div>
      <div class="form-group"><label class="form-label">Date</label><input type="text" id="lpDate" class="form-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="lpDateH"><input type="hidden" id="lpDateH" value="<?=date('Y-m-d')?>"></div>
    </div>
    <div class="form-group"><label class="form-label">Who Paid</label><input type="text" id="lpWho" class="form-input" value="<?=htmlspecialchars($_SESSION['username']??'')?>"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addLaborPayModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="saveLaborPayment()">Save</button></div>
</div></div>

<!-- ADD CLIENT PAYMENT -->
<div class="modal-overlay" id="addClientPayModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#128176; Client Payment</h3><div class="modal-close" onclick="closeModal('addClientPayModal')">&times;</div></div>
  <div class="modal-body">
    <div class="two-col">
      <div class="form-group"><label class="form-label">Amount (Tk) <span class="required">*</span></label><input type="number" id="cpAmount" class="form-input"></div>
      <div class="form-group"><label class="form-label">Date</label><input type="text" id="cpDate" class="form-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="cpDateH"><input type="hidden" id="cpDateH" value="<?=date('Y-m-d')?>"></div>
    </div>
    <div class="form-group"><label class="form-label">Method</label><select id="cpMethod" class="form-select"><option>Cash</option><option>Bank Transfer</option><option>Cheque</option><option>Mobile Banking</option></select></div>
    <div class="form-group"><label class="form-label">Notes</label><input type="text" id="cpNotes" class="form-input"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addClientPayModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="saveClientPayment()">Save</button></div>
</div></div>

<!-- ADD SCHEDULE -->
<div class="modal-overlay" id="addSchModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#128197; Add Schedule</h3><div class="modal-close" onclick="closeModal('addSchModal')">&times;</div></div>
  <div class="modal-body">
    <div class="two-col">
      <div class="form-group"><label class="form-label">Date <span class="required">*</span></label><input type="text" id="schDate" class="form-input smart-date" placeholder="<?=date('j/n/y', strtotime('+1 day'))?>" data-date-target="schDateH"><input type="hidden" id="schDateH" value="<?=date('Y-m-d', strtotime('+1 day'))?>"></div>
      <div class="form-group"><label class="form-label">Category</label><select id="schCat" class="form-select"><option value="">General</option><option>Board</option><option>Paint</option><option>Glass</option><option>Electric</option><option>Payment</option></select></div>
    </div>
    <div class="form-group"><label class="form-label">Description <span class="required">*</span></label><textarea id="schDesc" class="form-textarea" rows="2" placeholder="What needs to be done?"></textarea></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addSchModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="saveProjectSchedule()">Save</button></div>
</div></div>

<!-- EDIT PROJECT -->
<div class="modal-overlay" id="editProjectModal">
  <div class="modal modal-lg" data-form-nav>
    <div class="modal-header">
      <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:38px;height:38px;border-radius:50%;background:#9C1F24;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;box-shadow:0 3px 10px rgba(156,31,36,0.25);">
          <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <div>
          <h3 style="margin:0;font-size:17.5px;font-weight:700;color:#0F172A;line-height:1.2;">Edit Project</h3>
          <p style="margin:2px 0 0 0;font-size:12px;color:#64748B;">Update project details, budget & client info</p>
        </div>
      </div>
      <div class="modal-close" onclick="closeModal('editProjectModal')">&times;</div>
    </div>
    <div class="modal-body">
      <div class="two-col">
        <div class="form-group">
          <label class="form-label">Project Name <span class="required">*</span></label>
          <input type="text" id="epName" class="form-input" value="<?=htmlspecialchars($project['name'])?>">
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select id="epStatus" class="form-select">
            <option <?=$project['status']=='Ongoing'?'selected':''?>>Ongoing</option>
            <option <?=$project['status']=='Completed'?'selected':''?>>Completed</option>
            <option <?=$project['status']=='On Hold'?'selected':''?>>On Hold</option>
            <option <?=$project['status']=='Cancelled'?'selected':''?>>Cancelled</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Address</label>
        <input type="text" id="epAddress" class="form-input" value="<?=htmlspecialchars($project['address']??'')?>">
      </div>
      <div class="two-col">
        <div class="form-group">
          <label class="form-label">Client Name</label>
          <input type="text" id="epClient" class="form-input" value="<?=htmlspecialchars($project['client_name'])?>">
        </div>
        <div class="form-group">
          <label class="form-label">Client Phone</label>
          <input type="tel" id="epPhone" class="form-input" value="<?=htmlspecialchars($project['client_phone']??'')?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Budget (Tk)</label>
        <input type="number" id="epBudget" class="form-input" value="<?=$project['estimated_budget']??0?>">
      </div>
      <div class="two-col">
        <div class="form-group">
          <label class="form-label">Start Date</label>
          <input type="text" id="epStartDate" class="form-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="epStartDateH" value="<?=$project['start_date'] ? date('j/n/y', strtotime($project['start_date'])) : ''?>">
          <input type="hidden" id="epStartDateH" value="<?=$project['start_date']??''?>">
        </div>
        <div class="form-group">
          <label class="form-label">Expected End Date</label>
          <input type="text" id="epEndDate" class="form-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="epEndDateH" value="<?=$project['end_date'] ? date('j/n/y', strtotime($project['end_date'])) : ''?>">
          <input type="hidden" id="epEndDateH" value="<?=$project['end_date']??''?>">
        </div>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label">Notes</label>
        <textarea id="epNotes" class="form-textarea" rows="3" placeholder="Additional notes or specifications..."><?=htmlspecialchars($project['notes']??'')?></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('editProjectModal')">Cancel</button>
      <button class="btn btn-primary" data-save-btn onclick="updateProject()"><i class="fa-solid fa-check"></i> Save Changes</button>
    </div>
  </div>
</div>

<!-- ADD CONTRACTOR TO PROJECT -->
<div class="modal-overlay" id="addContractorToProjectModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#128736; Add Contractor to Project</h3><div class="modal-close" onclick="closeModal('addContractorToProjectModal')">&times;</div></div>
  <div class="modal-body">
    <div class="form-group"><label class="form-label">Contractor</label><select id="ctpContractor" class="form-select"><option value="">--</option><?php foreach($all_contractors as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?> (<?=htmlspecialchars($c['trade'])?>)</option><?php endforeach; ?></select></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addContractorToProjectModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="addContractorToProject()">Add</button></div>
</div></div>

<script>
window.onerror = function(msg, url, lineNo, columnNo, error) {
  var data = "Error: " + msg + " at " + lineNo + ":" + columnNo;
  if (error && error.stack) data += "\nStack: " + error.stack;
  fetch(BASE_PATH + '/log_error.php', { method: 'POST', body: data });
  return false;
};
window.addEventListener('unhandledrejection', function(event) {
  fetch(BASE_PATH + '/log_error.php', { method: 'POST', body: "Unhandled Promise Rejection: " + event.reason });
});
var PID = <?=intval($project_id)?>;
var TODAY = '<?=date('Y-m-d')?>';
var allPurchases = <?= json_encode($initialPurchases) ?>;

//  HELPERS 
function fmt(n){return 'Tk. '+parseFloat(n||0).toLocaleString('en-BD',{maximumFractionDigits:0});}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmtDate(d){if(!d)return'-';var dt=new Date(d);return isNaN(dt)?d:dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});}

function switchTab(btn, id) {
  if (!btn) return;
  document.querySelectorAll('#mainTabs .tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.tab-content').forEach(t => {
    t.style.display = 'none';
    t.classList.remove('active');
  });
  const target = document.getElementById(id);
  if (target) {
    target.style.display = 'block';
    target.classList.add('active');
  }
  if(id==='tabPurchases')   loadPurchases();
  if(id==='tabBilling')     loadBilling();
  if(id==='tabAttendance')  { loadAttendance(); loadWorkerSummary(); loadLaborPayments(); }
  if(id==='tabPayments')    loadClientPayments();
  if(id==='tabSchedules')   loadSchedules();
  if(id==='tabReports')     loadReports();
  if(id==='tabPrintouts')   loadPrintouts();
}

//  SUMMARY BAR 
async function loadHeaderStats() {
  const r = await fetch(BASE_PATH + '/api/reports.php?action=project_summary&project_id='+PID, {cache: 'no-store'});
  const d = await r.json();
  if (!d.success) return;
  const s = d.data;
  const budget = parseFloat(s.project.estimated_budget||0);
  const expense = parseFloat(s.total_purchases||0) + parseFloat(s.total_contractor_paid||0) + parseFloat(s.total_labor_paid||0);
  const received = parseFloat(s.total_client_payments||0);
  const due = budget - received;
  document.getElementById('hdrBudget').textContent   = fmt(budget);
  document.getElementById('hdrExpense').textContent  = fmt(expense);
  document.getElementById('hdrReceived').textContent = fmt(received);
  document.getElementById('hdrDue').textContent      = fmt(due);
  const start = s.project.start_date ? new Date(s.project.start_date) : null;
  document.getElementById('hdrDays').textContent = start ? Math.floor((new Date()-start)/86400000)+' days' : '-';
  document.getElementById('hdrPurchase').textContent = fmt(s.total_purchases);
  document.getElementById('hdrBilling').textContent  = fmt(s.total_contractor_billed);
  const totalPayment = parseFloat(s.total_contractor_paid||0) + parseFloat(s.total_labor_paid||0);
  document.getElementById('hdrPayment').textContent  = fmt(totalPayment);
}

//  PURCHASES 
async function loadPurchases() {
  const q    = document.getElementById('purchSearchQ').value;
  const cat  = document.getElementById('purchCatFilter').value;
  const from = document.getElementById('purchFromH').value;
  const to   = document.getElementById('purchToH').value;
  let url = BASE_PATH + '/api/purchases.php?action=list&project_id='+PID;
  if(q)   url+='&q='+encodeURIComponent(q);
  if(cat) url+='&category='+cat;
  if(from)url+='&from='+from;
  if(to)  url+='&to='+to;
  const r = await fetch(url, {cache: 'no-store'}); const d = await r.json();
  allPurchases = d.data||[];
  renderPurchases(allPurchases, d.total||0);
}
function renderPurchases(rows, total) {
  const body = document.getElementById('purchTable');
  const mList = document.getElementById('purchMobileList');
  if(!rows.length){
    if(body) body.innerHTML='<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">No purchases found</td></tr>';
    if(mList) mList.innerHTML='<div class="card" style="text-align:center;padding:32px 16px;color:var(--text-muted);border-radius:14px;border:1px solid var(--border);">No purchases found</div>';
    document.getElementById('purchTotal').textContent='Tk. 0';
    document.getElementById('purchTableTotal').textContent='Tk. 0';
    return;
  }
  document.getElementById('purchTotal').textContent=fmt(total);
  document.getElementById('purchTableTotal').textContent=fmt(total);
  if(body) {
    body.innerHTML=rows.map(p=>`<tr>
      <td>${fmtDate(p.purchase_date)}</td>
      <td><strong>${esc(p.item_name)}</strong>${p.board_thickness?`<br><span class='text-xs text-muted'>Thickness: ${esc(p.board_thickness)}</span>`:(p.board_type&&p.board_type!==p.item_name?`<br><span class='text-xs text-muted'>${esc(p.board_type)}</span>`:'')}</td>
      <td><span class="badge badge-neutral">${esc(p.supply_category||'-')}</span></td>
      <td>${parseFloat(p.quantity)} ${esc(p.unit||'')}</td>
      <td>${fmt(p.rate)}</td>
      <td class="td-amount text-danger">${fmt(p.total)}</td>
      <td>${esc(p.supplier||'-')}</td>
      <td class="td-actions">
        <button class="btn btn-ghost btn-sm btn-icon" title="Edit" onclick="openEditPurch(${p.id})">&#9998;</button>
        <button class="btn btn-ghost btn-sm btn-icon" title="Delete" onclick="delPurch(${p.id})">&#10006;</button>
      </td>
    </tr>`).join('');
  }
  if(mList) {
    mList.innerHTML=rows.map(p=>`
      <div class="mobile-record-card">
        <div class="mrc-header">
          <div class="mrc-header-left">
            <span class="mrc-date"><i class="fa-regular fa-calendar" style="color:var(--text-muted);"></i> ${fmtDate(p.purchase_date)}</span>
            <span class="badge badge-neutral" style="font-size:10px;padding:2px 8px;">${esc(p.supply_category||'-')}</span>
          </div>
          <div class="mrc-corner-actions">
            <button type="button" class="mrc-icon-btn mrc-btn-edit" title="Edit" onclick="openEditPurch(${p.id})">&#9998;</button>
            <button type="button" class="mrc-icon-btn mrc-btn-del" title="Delete" onclick="delPurch(${p.id})">&#10006;</button>
          </div>
        </div>
        <div class="mrc-title-row">
          <div class="mrc-title">
            ${esc(p.item_name)}
            ${p.board_thickness ? `<span class="mrc-thick">${esc(p.board_thickness)} mm</span>` : (p.board_type && p.board_type !== p.item_name ? `<span class="mrc-thick">${esc(p.board_type)}</span>` : '')}
          </div>
          <div class="mrc-amount text-danger">${fmt(p.total)}</div>
        </div>
        <div class="mrc-meta-row">
          <div class="mrc-meta-item">
            <span>Qty:</span> <strong>${parseFloat(p.quantity)} ${esc(p.unit||'')}</strong> @ ${fmt(p.rate)}
          </div>
          <div class="mrc-meta-item">
            <i class="fa-solid fa-store" style="font-size:10px;color:var(--text-muted);"></i> ${esc(p.supplier||'—')}
          </div>
        </div>
      </div>
    `).join('');
  }
}
const pItemEl = document.getElementById('pItem');
if (pItemEl) {
  pItemEl.addEventListener('input', async function(e) {
    const val = e.target.value.trim();
    if (val.length < 2) return;
    const r = await fetch(BASE_PATH + '/api/purchases.php?action=autocomplete&field=item_name&term=' + encodeURIComponent(val));
    const d = await r.json();
    if (d.success && d.data) {
      const list = document.getElementById('pItemList');
      if (list) list.innerHTML = d.data.map(i => `<option value="${esc(i)}">`).join('');
    }
  });
}
const pSearchEl = document.getElementById('purchSearchQ');
if (pSearchEl) {
  pSearchEl.addEventListener('input', async function(e) {
    const val = e.target.value.trim();
    if (val.length < 2) return;
    const r = await fetch(BASE_PATH + '/api/purchases.php?action=autocomplete&field=item_name&term=' + encodeURIComponent(val));
    const d = await r.json();
    if (d.success && d.data) {
      const list = document.getElementById('globalPurchItemList');
      if (list) list.innerHTML = d.data.map(i => `<option value="${esc(i)}">`).join('');
    }
  });
}
function toggleBoardFields(){
  const val = document.getElementById('pCat').value;
  const isBoard = val === 'Board' || val === 'Board & Wood' || /board/i.test(val);
  const itemLabel = document.getElementById('pItemLabel');
  const itemInput = document.getElementById('pItem');
  const bf = document.getElementById('pBoardFields');
  const unitGroup = document.getElementById('pUnitGroup');
  const unitInput = document.getElementById('pUnit');
  const qtyLabel = document.getElementById('pQtyLabel');

  if (isBoard) {
    if (itemLabel) itemLabel.innerHTML = 'Board Type <span class="required">*</span>';
    if (itemInput) {
      itemInput.placeholder = 'e.g. Melamine, Partex, PVC, Gorjon';
      itemInput.setAttribute('list', 'pBoardSuggestions');
    }
    if (bf) bf.style.display = 'block';
    if (unitGroup) unitGroup.style.display = 'none';
    if (unitInput) unitInput.value = 'pcs';
    if (qtyLabel) qtyLabel.innerHTML = 'Qty (pcs) <span class="required">*</span>';
  } else {
    if (itemLabel) itemLabel.innerHTML = 'Item Name <span class="required">*</span>';
    if (itemInput) {
      itemInput.placeholder = 'Item name';
      itemInput.setAttribute('list', 'pItemList');
    }
    if (bf) bf.style.display = 'none';
    if (unitGroup) unitGroup.style.display = 'block';
    if (qtyLabel) qtyLabel.innerHTML = 'Qty <span class="required">*</span>';
  }
}

function toggleEditBoardFields(){
  const val = document.getElementById('epCat').value;
  const isBoard = val === 'Board' || val === 'Board & Wood' || /board/i.test(val);
  const itemLabel = document.getElementById('epItemLabel');
  const itemInput = document.getElementById('epItem');
  const bf = document.getElementById('epBoardFields');
  const unitGroup = document.getElementById('epUnitGroup');
  const unitInput = document.getElementById('epUnit');
  const qtyLabel = document.getElementById('epQtyLabel');

  if (isBoard) {
    if (itemLabel) itemLabel.innerHTML = 'Board Type <span class="required">*</span>';
    if (itemInput) {
      itemInput.placeholder = 'e.g. Melamine, Partex, PVC, Gorjon';
      itemInput.setAttribute('list', 'pBoardSuggestions');
    }
    if (bf) bf.style.display = 'block';
    if (unitGroup) unitGroup.style.display = 'none';
    if (unitInput) unitInput.value = 'pcs';
    if (qtyLabel) qtyLabel.innerHTML = 'Qty (pcs) <span class="required">*</span>';
  } else {
    if (itemLabel) itemLabel.innerHTML = 'Item Name <span class="required">*</span>';
    if (itemInput) {
      itemInput.placeholder = 'Item name';
      itemInput.setAttribute('list', 'pItemList');
    }
    if (bf) bf.style.display = 'none';
    if (unitGroup) unitGroup.style.display = 'block';
    if (qtyLabel) qtyLabel.innerHTML = 'Qty <span class="required">*</span>';
  }
}

function calcPurchTotal(){const q=parseFloat(document.getElementById('pQty').value)||0,r=parseFloat(document.getElementById('pRate').value)||0;const td=document.getElementById('pTotalDisplay');if(td)td.textContent=fmt(q*r);}
function calcEditPurchTotal(){const q=parseFloat(document.getElementById('epQty').value)||0,r=parseFloat(document.getElementById('epRate').value)||0;const td=document.getElementById('epTotalDisplay');if(td)td.textContent=fmt(q*r);}

async function savePurchase(){
  const catVal = document.getElementById('pCat').value;
  const isBoard = catVal === 'Board' || catVal === 'Board & Wood' || /board/i.test(catVal);
  const item = document.getElementById('pItem').value.trim();
  const thickVal = document.getElementById('pThick') ? document.getElementById('pThick').value.trim() : '';
  const qty = parseFloat(document.getElementById('pQty').value) || 0;
  const rate = parseFloat(document.getElementById('pRate').value) || 0;

  if (!item) { showToast(isBoard ? 'Board type is required' : 'Item name is required', 'warning'); return; }
  if (isBoard && !thickVal) {
    showToast('Board thickness (mm) is required', 'warning');
    document.getElementById('pThick').focus();
    return;
  }
  if (qty <= 0 || rate <= 0) { showToast('Quantity and rate must be greater than 0', 'warning'); return; }

  const fd = new FormData();
  fd.append('project_id', PID);
  fd.append('item_name', item);
  fd.append('supply_category', catVal);
  fd.append('board_type', item);
  fd.append('board_thickness', thickVal);
  fd.append('board_size', '');
  fd.append('quantity', qty);
  fd.append('unit', isBoard ? 'pcs' : (document.getElementById('pUnit').value || 'pcs'));
  fd.append('rate', rate);
  fd.append('supplier', document.getElementById('pSupplier').value);
  fd.append('purchase_date', document.getElementById('pDateH').value || TODAY);

  try {
    const r = await fetch(BASE_PATH + '/api/purchases.php?action=create', { method: 'POST', body: fd });
    const d = await r.json();
    if (d.success) {
      showToast('Purchase saved!', 'success');
      closeModal('addPurchModal');
      document.getElementById('pItem').value = '';
      document.getElementById('pQty').value = '';
      document.getElementById('pRate').value = '';
      document.getElementById('pSupplier').value = '';
      if (document.getElementById('pThick')) document.getElementById('pThick').value = '';
      document.getElementById('pCat').value = '';
      toggleBoardFields();
      calcPurchTotal();
      loadPurchases();
      loadHeaderStats();
    } else {
      showToast(d.message || 'Error saving purchase', 'error');
    }
  } catch(e) {
    showToast('Connection error', 'error');
  }
}

function openEditPurch(id){
  const p = allPurchases.find(x => x.id == id);
  if (!p) return;
  document.getElementById('epId').value = p.id;
  document.getElementById('epItem').value = p.item_name || '';
  document.getElementById('epCat').value = p.supply_category || '';
  if (document.getElementById('epThick')) document.getElementById('epThick').value = p.board_thickness || '';
  toggleEditBoardFields();
  document.getElementById('epQty').value = p.quantity || '';
  document.getElementById('epUnit').value = p.unit || '';
  document.getElementById('epRate').value = p.rate || '';
  document.getElementById('epDateH').value = p.purchase_date;
  SmartDate.setDateValue(document.getElementById('epDate'), p.purchase_date);
  calcEditPurchTotal();
  openModal('editPurchModal');
}

async function updatePurchase(){
  const catVal = document.getElementById('epCat').value;
  const isBoard = catVal === 'Board' || catVal === 'Board & Wood' || /board/i.test(catVal);
  const item = document.getElementById('epItem').value.trim();
  const thickVal = document.getElementById('epThick') ? document.getElementById('epThick').value.trim() : '';
  const qty = parseFloat(document.getElementById('epQty').value) || 0;
  const rate = parseFloat(document.getElementById('epRate').value) || 0;

  if (!item) { showToast(isBoard ? 'Board type is required' : 'Item name is required', 'warning'); return; }
  if (isBoard && !thickVal) {
    showToast('Board thickness (mm) is required', 'warning');
    document.getElementById('epThick').focus();
    return;
  }
  if (qty <= 0 || rate <= 0) { showToast('Quantity and rate must be greater than 0', 'warning'); return; }

  const fd = new FormData();
  fd.append('id', document.getElementById('epId').value);
  fd.append('project_id', PID);
  fd.append('item_name', item);
  fd.append('supply_category', catVal);
  fd.append('board_type', item);
  fd.append('board_thickness', thickVal);
  fd.append('board_size', '');
  fd.append('quantity', qty);
  fd.append('unit', isBoard ? 'pcs' : (document.getElementById('epUnit').value || 'pcs'));
  fd.append('rate', rate);
  fd.append('purchase_date', document.getElementById('epDateH').value || TODAY);

  try {
    const r = await fetch(BASE_PATH + '/api/purchases.php?action=update', { method: 'POST', body: fd });
    const d = await r.json();
    if (d.success) {
      showToast('Updated!', 'success');
      closeModal('editPurchModal');
      loadPurchases();
      loadHeaderStats();
    } else {
      showToast(d.message || 'Error updating purchase', 'error');
    }
  } catch(e) {
    showToast('Connection error', 'error');
  }
}
async function delPurch(id){
    confirmDelete('Delete this purchase?',async function(){
        try {
            const r = await fetch(BASE_PATH + '/api/purchases.php?action=delete&project_id='+PID,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
            const d = await r.json();
            if (d.success) {
                showToast('Deleted','success');
                const btn = document.querySelector(`button[onclick="delPurch(${id})"]`);
                if(btn) {
                    const row = btn.closest('tr') || btn.closest('.mobile-record-card') || btn.closest('.printout-item');
                    if(row) row.remove();
                }
                loadPurchases();loadHeaderStats();
            } else {
                showToast(d.message || 'Error deleting', 'error');
            }
        } catch(e) {
            showToast('Connection error', 'error');
        }
    });
}
// BILLING
let billData=[];
function onBillingContactChange(){
  loadBilling();
}
function openAddAdvanceModal(){
  const cid=(document.getElementById('billContFilter')?.value)||'';
  const sel=document.getElementById('advContractor');
  if(sel && cid) sel.value=cid;
  openModal('addAdvModal');
}
async function loadBilling(){
  const cid=(document.getElementById('billContFilter')?.value)||'';
  const btnPrint = document.getElementById('btnPrintAdv');
  if (btnPrint) btnPrint.style.display = cid ? 'inline-block' : 'none';
  const url=BASE_PATH + '/api/billing.php?action=list_advances_range&project_id='+PID+'&from=1900-01-01&to=2099-12-31'+(cid?'&contractor_id='+cid:'');
  const r=await fetch(url, {cache: 'no-store'});const d=await r.json();
  allAttRecords = d.data || [];
  billData=d.data||[];const total=d.total||0;
  const billTotEl = document.getElementById('billTotal');
  if (billTotEl) billTotEl.textContent = fmt(total);
  const billTblTotEl = document.getElementById('billTableTotal');
  if (billTblTotEl) billTblTotEl.textContent = fmt(total);
  const body=document.getElementById('billTable');
  const mList=document.getElementById('billMobileList');
  if(!billData.length){
    if(body) body.innerHTML='<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">No advances</td></tr>';
    if(mList) mList.innerHTML='<div class="card" style="text-align:center;padding:32px 16px;color:var(--text-muted);border-radius:14px;border:1px solid var(--border);">No advances</div>';
    return;
  }
  if(body) {
    body.innerHTML=billData.map(a=>`<tr><td><strong>${esc(a.contractor_name||'-')}</strong></td><td class="td-amount">${fmt(a.amount)}</td><td>${esc(a.payment_method||'-')}</td><td>${esc(a.who_paid||'-')}</td><td>${fmtDate(a.payment_date)}</td><td><button class="btn btn-ghost btn-sm btn-icon" onclick="delAdv(${a.id})">&#10006;</button></td></tr>`).join('');
  }
  if(mList) {
    mList.innerHTML=billData.map(a=>{
      const initial = esc(a.contractor_name || 'C').charAt(0).toUpperCase();
      return `
      <div class="mobile-record-card">
        <div class="mrc-header">
          <div class="mrc-header-left">
            <div class="mrc-avatar">${initial}</div>
            <div class="mrc-title">${esc(a.contractor_name || '-')}</div>
          </div>
          <div class="mrc-corner-actions">
            <button type="button" class="mrc-icon-btn mrc-btn-del" title="Delete" onclick="delAdv(${a.id})">&#10006;</button>
          </div>
        </div>
        <div class="mrc-title-row">
          <span class="mrc-date"><i class="fa-regular fa-calendar" style="color:var(--text-muted);"></i> ${fmtDate(a.payment_date)}</span>
          <div class="mrc-amount text-success">${fmt(a.amount)}</div>
        </div>
        <div class="mrc-meta-row">
          <div class="mrc-meta-item">
            <span>Paid by:</span> <strong>${esc(a.who_paid || '—')}</strong>
          </div>
          <div class="mrc-meta-item">
            <span class="badge badge-neutral" style="font-size:10px;padding:1px 6px;">${esc(a.payment_method || 'Cash')}</span>
          </div>
        </div>
        ${a.who_received || a.notes ? `
        <div class="mrc-meta-row" style="font-size:11px;color:var(--text-muted);">
          ${a.who_received ? `<div><span>Recv:</span> ${esc(a.who_received)}</div>` : ''}
          ${a.notes ? `<div><span>Note:</span> ${esc(a.notes)}</div>` : ''}
        </div>` : ''}
      </div>
      `;
    }).join('');
  }
}
async function saveAdvance(){
  const cid=document.getElementById('advContractor').value,amt=parseFloat(document.getElementById('advAmount').value)||0;
  if(!cid||amt<=0){showToast('Contractor and amount required','warning');return;}
  const fd=new FormData();fd.append('project_id',PID);fd.append('contractor_id',cid);fd.append('amount',amt);
  fd.append('payment_date',document.getElementById('advDateH').value||TODAY);
  fd.append('who_paid',document.getElementById('advWhoPaid').value);fd.append('who_received',document.getElementById('advWhoRec').value);
  fd.append('notes',document.getElementById('advNotes').value);fd.append('payment_method','Cash');
  const r=await fetch(BASE_PATH + '/api/billing.php?action=add_advance',{method:'POST',body:fd});
  const d=await r.json();
  if(d.success){showToast('Advance saved!','success');closeModal('addAdvModal');loadBilling();loadHeaderStats();}else showToast(d.message||'Error','error');
}
async function delAdv(id){
    confirmDelete('Delete advance?',async function(){
        try {
            const r = await fetch(BASE_PATH + '/api/billing.php?action=delete_advance&project_id='+PID,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
            const d = await r.json();
            if (d.success) {
                showToast('Deleted','success');
                const btn = document.querySelector(`button[onclick="delAdv(${id})"]`);
                if(btn) {
                    const row = btn.closest('tr') || btn.closest('.mobile-record-card') || btn.closest('.printout-item');
                    if(row) row.remove();
                }
                loadBilling();loadHeaderStats();
            } else {
                showToast(d.message || 'Error deleting', 'error');
            }
        } catch(e) {
            showToast('Connection error', 'error');
        }
    });
}
async function addContractorToProject(){
  const cid=document.getElementById('ctpContractor').value;if(!cid){showToast('Select contractor','warning');return;}
  const fd=new FormData();fd.append('project_id',PID);fd.append('contractor_id',cid);
  const r=await fetch(BASE_PATH + '/api/contractors.php?action=assign_to_project',{method:'POST',body:fd});
  const d=await r.json();
  if(d.success){showToast('Contractor added!','success');closeModal('addContractorToProjectModal');}else showToast(d.message||'Error','error');
}
function printContractorAdvances() {
  const cid = document.getElementById('billContFilter').value;
  if (!cid) { showToast('Please select a specific contractor from the filter first', 'warning'); return; }
  openPrintAdvancePreview(cid);
}

async function openPrintAdvancePreview(cid) {
  const container = document.getElementById('advPreviewRows');
  if (!container) return;
  document.getElementById('printAdvancesModal').dataset.cid = cid;
  container.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--text-muted);">Loading preview...</td></tr>';
  openModal('printAdvancesModal');
  const title = document.getElementById('billContFilter').selectedOptions[0]?.text || '';
  document.getElementById('advPreviewTitle').lastChild.textContent = ' ' + title + ' - Adjust payments, then print. Changes only affect this printout.';
  try {
    const r = await fetch(BASE_PATH + '/api/billing.php?action=get_advance_preview&project_id=' + PID + '&contractor_id=' + cid, {cache:'no-store'});
    const d = await r.json();
    container.innerHTML = '';
    if (!d.success) { container.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);">' + esc(d.message||'Error') + '</div>'; return; }
    (d.payments || []).forEach(p => addAdvPvRow({group:'pay', date:p.date, name:p.name, person_type:p.person_type, amount:p.amount}));
    if (!(d.payments||[]).length) container.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);">No payments found for this contractor.</div>';
  } catch(e) {
    container.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted);">Connection error.</div>';
  }
}

function addAdvPvRow(rec) {
  const container = document.getElementById('advPreviewRows');
  const isPay = rec.group === 'pay';
  const role = rec.person_type === 'contractor' ? 'Contractor' : 'Worker';
  const row = document.createElement('div');
  row.className = 'adv-pv-row';
  row.style.cssText = 'display:grid; grid-template-columns:96px 1fr 84px 110px 34px; gap:8px; margin-bottom:8px; align-items:center;';
  row.dataset.group = isPay ? 'pay' : 'attendance';
  row.dataset.source = rec.source || (isPay ? 'advance' : 'attendance');
  row.innerHTML =
    '<input type="text" class="form-input smart-date pv-date" value="' + esc(rec.date || TODAY) + '" style="width:100%;">' +
    '<input type="text" class="form-input pv-name" value="' + esc(rec.name || '') + '" style="font-weight:600;">' +
    '<span class="badge ' + (role==='Contractor' ? 'badge-primary' : 'badge-info') + '" style="text-align:center;">' + role + '</span>' +
    '<input type="number" class="form-input pv-amount" value="' + esc(rec.amount ?? '') + '" min="0" step="0.01" oninput="advPvTotal()">' +
    '<button type="button" class="btn btn-ghost btn-sm btn-icon" title="Remove" onclick="removeAdvPvRow(this)">&#10006;</button>';
  container.appendChild(row);
  const dateEl = row.querySelector('.pv-date');
  if (typeof SmartDate !== 'undefined' && SmartDate.setDateValue) SmartDate.setDateValue(dateEl, rec.date || TODAY);
  advPvTotal();
}

function removeAdvPvRow(btn) {
  const row = btn.closest('.adv-pv-row');
  if (row) { row.remove(); advPvTotal(); }
}

function advPvTotal() {
  const rows = document.querySelectorAll('#advPreviewRows .adv-pv-row');
  let total = 0;
  const payOnly = document.querySelectorAll('#advPreviewRows .adv-pv-row[data-group="pay"]');
  payOnly.forEach(r => { total += parseFloat(r.querySelector('.pv-amount').value) || 0; });
  const el = document.getElementById('advPvTotal');
  if (el) el.textContent = 'Tk. ' + total.toLocaleString('en-BD', {maximumFractionDigits:0});
}

function printAdvancePreview() {
  const cid = document.getElementById('printAdvancesModal').dataset.cid;
  const container = document.getElementById('advPreviewRows');
  const rows = container.querySelectorAll('.adv-pv-row');
  if (!rows.length) { showToast('Add at least one row to print', 'warning'); return; }
  const data = [];
  rows.forEach(r => {
    const group = r.dataset.group;
    const dateEl = r.querySelector('.pv-date');
    const dateVal = (typeof SmartDate !== 'undefined' && SmartDate.getDbValue) ? (SmartDate.getDbValue(dateEl) || dateEl.value) : dateEl.value;
    data.push({group: group, date: dateVal, name: r.querySelector('.pv-name').value, person_type: ((r.querySelectorAll('.badge')[0]||{}).textContent||'').trim()==='Contractor' ? 'contractor' : 'worker', amount: parseFloat(r.querySelector('.pv-amount').value) || 0});
  });
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = BASE_PATH + '/print_advances';
  form.target = '_blank';
  const add = (n,v) => { const i = document.createElement('input'); i.type='hidden'; i.name=n; i.value=v; form.appendChild(i); };
  add('project_id', PID);
  add('contractor_id', cid);
  add('rows_json', JSON.stringify(data));
  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
  closeModal('printAdvancesModal');
}


</script>
<script>
//  ATTENDANCE 
function afterAttendanceSave(){ loadAttendance(); loadWorkerSummary(); loadHeaderStats(); }
function printWorkerReport(type) {
  const wid = document.getElementById('wrWorker').value;
  if(!wid) { showToast('Please select a worker first', 'warning'); return; }
  const from = document.getElementById('wrFromH').value;
  const to = document.getElementById('wrToH').value;
  window.open(BASE_PATH + '/print_worker_report?type=' + type + '&project_id=' + PID + '&worker_id=' + wid + '&from=' + from + '&to=' + to, '_blank');
}
let allAttRecords = [];
function openEditAtt(id){
  const a = allAttRecords.find(x => x.id == id);
  if (!a) return;
  document.getElementById('eaId').value = a.id;
  document.getElementById('eaWorker').value = a.worker_id;
  document.getElementById('eaWorkerName').textContent = a.worker_name || 'Worker';
  document.getElementById('eaDailyRate').value = a.daily_rate || 0;
  document.getElementById('eaMultiplier').value = parseFloat(a.attendance_multiplier) || 1;
  document.getElementById('eaNotes').value = a.notes || '';
  document.getElementById('eaDateH').value = a.work_date;
  SmartDate.setDateValue(document.getElementById('eaDate'), a.work_date);
  calcEditAttEarned();
  openModal('editAttModal');
}
function calcEditAttEarned(){
  const rate = parseFloat(document.getElementById('eaDailyRate').value) || 0;
  const mult = parseFloat(document.getElementById('eaMultiplier').value) || 1;
  const earned = Math.round(rate * mult);
  document.getElementById('eaEarnedDisplay').value = 'Tk. ' + earned.toLocaleString();
}
async function updateAttendance(){
  const id = document.getElementById('eaId').value;
  const wid = document.getElementById('eaWorker').value;
  const date = document.getElementById('eaDateH').value;
  const rate = parseFloat(document.getElementById('eaDailyRate').value) || 0;
  const mult = parseFloat(document.getElementById('eaMultiplier').value) || 1;
  const notes = document.getElementById('eaNotes').value;
  if (!id || !wid || rate <= 0) {
    showToast('Daily rate must be greater than 0', 'warning');
    return;
  }
  const fd = new FormData();
  fd.append('id', id);
  fd.append('worker_id', wid);
  fd.append('project_id', PID);
  fd.append('work_date', date);
  fd.append('daily_rate', rate);
  fd.append('attendance_multiplier', mult);
  fd.append('notes', notes);
  try {
    const r = await fetch(BASE_PATH + '/api/attendance.php?action=edit_attendance', {method:'POST', body:fd});
    const d = await r.json();
    if (d.success) {
      showToast('Attendance updated!', 'success');
      closeModal('editAttModal');
      afterAttendanceSave();
    } else {
      showToast(d.message || 'Error updating attendance', 'error');
    }
  } catch(e) {
    showToast('Connection error', 'error');
  }
}

async function loadAttendance(){
  const wid=document.getElementById('attWorkerFilter').value,from=document.getElementById('attFromH').value,to=document.getElementById('attToH').value;
  let url=BASE_PATH + '/api/attendance.php?action=list_attendance&project_id='+PID;
  if(wid)url+='&worker_id='+wid;if(from)url+='&from='+from;if(to)url+='&to='+to;
  const r=await fetch(url, {cache: 'no-store'});const d=await r.json();
  allAttRecords = d.data || [];
  const body=document.getElementById('attTable');
  const mList=document.getElementById('attMobileList');
  if(!d.success||!d.data.length){
    if(body) body.innerHTML='<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">No attendance</td></tr>';
    if(mList) mList.innerHTML='<div class="card" style="text-align:center;padding:32px 16px;color:var(--text-muted);border-radius:14px;border:1px solid var(--border);">No attendance</div>';
    document.getElementById('attEarnedTotal').textContent='Tk. 0';
    document.getElementById('attTableTotal').textContent='Tk. 0';
    return;
  }
  let tot=0;
  const typeL={1:'Full',0.5:'Half',1.5:'OT',2:'2x'};
  const typeBadgeClass={1:'badge-info',0.5:'badge-warning',1.5:'badge-primary',2:'badge-success'};
  if(body) {
    body.innerHTML=d.data.map(a=>{
      tot+=parseFloat(a.earned||0);
      return`<tr><td>${fmtDate(a.work_date)}</td><td><strong>${esc(a.worker_name||'-')}</strong></td><td><span class="badge badge-info">${typeL[parseFloat(a.attendance_multiplier)]||a.attendance_multiplier+'x'}</span></td><td>${fmt(a.daily_rate)}</td><td class="td-amount text-success">${fmt(a.earned)}</td><td><div style="display:inline-flex;gap:4px;"><button class="btn btn-ghost btn-sm btn-icon" onclick="openEditAtt(${a.id})" title="Edit">&#9998;</button><button class="btn btn-ghost btn-sm btn-icon" onclick="delAtt(${a.id})" title="Delete">&#10006;</button></div></td></tr>`;
    }).join('');
  }
  if(mList) {
    mList.innerHTML=d.data.map(a=>{
      const initial = esc(a.worker_name || 'W').charAt(0).toUpperCase();
      const mult = parseFloat(a.attendance_multiplier);
      const bClass = typeBadgeClass[mult] || 'badge-info';
      const bLabel = typeL[mult] ? `${typeL[mult]} (${mult}x)` : `${mult}x`;
      return `
      <div class="mobile-record-card">
        <div class="mrc-header">
          <div class="mrc-header-left">
            <div class="mrc-avatar" style="background:#F0FDF4;color:#16A34A;border-color:rgba(22,163,74,0.15);">${initial}</div>
            <div class="mrc-title">${esc(a.worker_name || '-')}</div>
            <span class="badge ${bClass}" style="font-size:10px;padding:1px 6px;">${bLabel}</span>
          </div>
          <div class="mrc-corner-actions">
            <button type="button" class="mrc-icon-btn" title="Edit" onclick="openEditAtt(${a.id})" style="color:var(--text-muted);font-size:13px;margin-right:4px;">&#9998;</button>
            <button type="button" class="mrc-icon-btn mrc-btn-del" title="Delete" onclick="delAtt(${a.id})">&#10006;</button>
          </div>
        </div>
        <div class="mrc-title-row">
          <span class="mrc-date"><i class="fa-regular fa-calendar" style="color:var(--text-muted);"></i> ${fmtDate(a.work_date)}</span>
          <div class="mrc-amount text-success">${fmt(a.earned)}</div>
        </div>
        <div class="mrc-meta-row">
          <div class="mrc-meta-item">
            <span>Rate:</span> <strong>${fmt(a.daily_rate)}</strong>/day
          </div>
        </div>
      </div>
      `;
    }).join('');
  }
  document.getElementById('attEarnedTotal').textContent=fmt(tot);
  document.getElementById('attTableTotal').textContent=fmt(tot);
}
async function loadWorkerSummary(){
  const r=await fetch(BASE_PATH + '/api/attendance.php?action=get_summary&project_id='+PID);const d=await r.json();
  const box=document.getElementById('workerSummary');
  if(!d.success||!d.data.length){box.innerHTML='<div class="empty-state" style="padding:16px;"><p>No data</p></div>';return;}
  box.innerHTML=d.data.map(w=>`<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-light);"><div style="flex:1;min-width:0;"><div style="font-size:13px;font-weight:600;">${esc(w.worker_name)}</div><div style="font-size:11px;color:var(--text-muted);">${w.total_days} days</div></div><div style="text-align:right;"><div style="font-size:12px;">Earned: <strong>${fmt(w.total_earned)}</strong></div><div style="font-size:12px;color:${w.balance_due>0?'var(--danger)':'var(--success)'};">Due: <strong>${fmt(w.balance_due)}</strong></div></div></div>`).join('');
}
async function loadLaborPayments(){
  const r=await fetch(BASE_PATH + '/api/attendance.php?action=list_payments&project_id='+PID);const d=await r.json();
  const body=document.getElementById('laborPayTable');
  const mList=document.getElementById('laborPayMobileList');
  let tot=0;
  if(!d.success||!d.data.length){
    if(body) body.innerHTML='<tr><td colspan="4" style="text-align:center;padding:16px;color:var(--text-muted);">No payments</td></tr>';
    if(mList) mList.innerHTML='<div class="card" style="text-align:center;padding:24px 16px;color:var(--text-muted);border-radius:14px;border:1px solid var(--border);">No payments</div>';
    document.getElementById('attPaidTotal').textContent='Tk. 0';
    return;
  }
  if(body) {
    body.innerHTML=d.data.map(p=>{
      tot+=parseFloat(p.amount||0);
      return`<tr><td><strong>${esc(p.worker_name||'-')}</strong></td><td class="td-amount">${fmt(p.amount)}</td><td>${fmtDate(p.payment_date)}</td><td><button class="btn btn-ghost btn-sm btn-icon" onclick="delLP(${p.id})">&#10006;</button></td></tr>`;
    }).join('');
  }
  if(mList) {
    mList.innerHTML=d.data.map(p=>{
      const initial = esc(p.worker_name || 'W').charAt(0).toUpperCase();
      return `
      <div class="mobile-record-card">
        <div class="mrc-header">
          <div class="mrc-header-left">
            <div class="mrc-avatar" style="background:#FEF3C7;color:#D97706;border-color:rgba(217,119,6,0.15);">${initial}</div>
            <div class="mrc-title">${esc(p.worker_name || '-')}</div>
          </div>
          <div class="mrc-corner-actions">
            <button type="button" class="mrc-icon-btn mrc-btn-del" title="Delete" onclick="delLP(${p.id})">&#10006;</button>
          </div>
        </div>
        <div class="mrc-title-row">
          <span class="mrc-date"><i class="fa-regular fa-calendar" style="color:var(--text-muted);"></i> ${fmtDate(p.payment_date)}</span>
          <div class="mrc-amount" style="color:var(--warning);">${fmt(p.amount)}</div>
        </div>
      </div>
      `;
    }).join('');
  }
  document.getElementById('attPaidTotal').textContent=fmt(tot);
}
async function saveLaborPayment(){
  const wid=document.getElementById('lpWorker').value,amt=parseFloat(document.getElementById('lpAmount').value)||0;
  if(!wid||amt<=0){showToast('Worker and amount required','warning');return;}
  const fd=new FormData();fd.append('project_id',PID);fd.append('worker_id',wid);fd.append('amount',amt);fd.append('payment_date',document.getElementById('lpDateH').value||TODAY);fd.append('who_paid',document.getElementById('lpWho').value);fd.append('payment_method','Cash');
  const r=await fetch(BASE_PATH + '/api/attendance.php?action=add_payment',{method:'POST',body:fd});const d=await r.json();
  if(d.success){showToast('Payment saved!','success');closeModal('addLaborPayModal');loadLaborPayments();loadWorkerSummary();loadHeaderStats();}else showToast(d.message||'Error','error');
}
async function delAtt(id){
    confirmDelete('Delete attendance?',async function(){
        try {
            const r = await fetch(BASE_PATH + '/api/attendance.php?action=delete_attendance&project_id='+PID,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
            const d = await r.json();
            if (d.success) {
                showToast('Deleted','success');
                const btn = document.querySelector(`button[onclick="delAtt(${id})"]`);
                if(btn) {
                    const row = btn.closest('tr') || btn.closest('.mobile-record-card') || btn.closest('.printout-item');
                    if(row) row.remove();
                }
                loadAttendance();loadWorkerSummary();loadHeaderStats();
            } else {
                showToast(d.message || 'Error deleting', 'error');
            }
        } catch(e) {
            showToast('Connection error', 'error');
        }
    });
}
async function delLP(id){
    confirmDelete('Delete payment?',async function(){
        try {
            const r = await fetch(BASE_PATH + '/api/attendance.php?action=delete_payment&project_id='+PID,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
            const d = await r.json();
            if (d.success) {
                showToast('Deleted','success');
                const btn = document.querySelector(`button[onclick="delLP(${id})"]`);
                if(btn) {
                    const row = btn.closest('tr') || btn.closest('.mobile-record-card') || btn.closest('.printout-item');
                    if(row) row.remove();
                }
                loadLaborPayments();loadWorkerSummary();
            } else {
                showToast(d.message || 'Error deleting', 'error');
            }
        } catch(e) {
            showToast('Connection error', 'error');
        }
    });
}

//  CLIENT PAYMENTS 
async function loadClientPayments(){
  const r=await fetch(BASE_PATH + '/api/client_payments.php?action=list&project_id='+PID, {cache: 'no-store'});const d=await r.json();
  const body=document.getElementById('cpTable');let tot=0;
  if(!d.success||!d.data.length){body.innerHTML='<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted);">No payments</td></tr>';document.getElementById('cpTotal').textContent='Tk. 0';document.getElementById('cpTableTotal').textContent='Tk. 0';return;}
  d.data.forEach(p=>tot+=parseFloat(p.amount||0));
  document.getElementById('cpTotal').textContent=fmt(tot);document.getElementById('cpTableTotal').textContent=fmt(tot);
  body.innerHTML=d.data.map(p=>`<tr><td>${fmtDate(p.payment_date)}</td><td>${esc(p.payment_method||'-')}</td><td class="td-amount text-success">${fmt(p.amount)}</td><td>${esc(p.notes||'-')}</td><td><button class="btn btn-ghost btn-sm btn-icon" onclick="delCP(${p.id})">&#10006;</button></td></tr>`).join('');
}
async function saveClientPayment(){
  const amt=parseFloat(document.getElementById('cpAmount').value)||0;if(amt<=0){showToast('Amount required','warning');return;}
  const fd=new FormData();fd.append('project_id',PID);fd.append('amount',amt);fd.append('payment_date',document.getElementById('cpDateH').value||TODAY);fd.append('payment_method',document.getElementById('cpMethod').value);fd.append('notes',document.getElementById('cpNotes').value);
  const r=await fetch(BASE_PATH + '/api/client_payments.php?action=create',{method:'POST',body:fd});const d=await r.json();
  if(d.success){showToast('Payment saved!','success');closeModal('addClientPayModal');loadClientPayments();loadHeaderStats();}else showToast(d.message||'Error','error');
}
async function delCP(id){
    confirmDelete('Delete payment?',async function(){
        try {
            const r = await fetch(BASE_PATH + '/api/client_payments.php?action=delete&project_id='+PID,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
            const d = await r.json();
            if (d.success) {
                showToast('Deleted','success');
                const btn = document.querySelector(`button[onclick="delCP(${id})"]`);
                if(btn) {
                    const row = btn.closest('tr') || btn.closest('.printout-item');
                    if(row) row.remove();
                }
                loadClientPayments();loadHeaderStats();
            } else {
                showToast(d.message || 'Error deleting', 'error');
            }
        } catch(e) {
            showToast('Connection error', 'error');
        }
    });
}

//  SCHEDULES 
function openProjectScheduleModal() {
  var tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  var yyyy = tomorrow.getFullYear();
  var mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
  var dd = String(tomorrow.getDate()).padStart(2, '0');
  var tomorrowISO = yyyy + '-' + mm + '-' + dd;
  
  var dateInput = document.getElementById('schDate');
  var hiddenInput = document.getElementById('schDateH');
  if (dateInput) {
    if (window.SmartDate && window.SmartDate.setDateValue) {
      window.SmartDate.setDateValue(dateInput, tomorrowISO);
    } else {
      dateInput.value = tomorrowISO;
    }
  }
  if (hiddenInput) {
    hiddenInput.value = tomorrowISO;
  }
  var desc = document.getElementById('schDesc');
  if (desc) desc.value = '';
  var cat = document.getElementById('schCat');
  if (cat) cat.value = '';
  openModal('addSchModal');
}
async function loadSchedules(){
  const r=await fetch(BASE_PATH + '/api/schedules.php?action=list&project_id='+PID, {cache: 'no-store'});const d=await r.json();
  const body=document.getElementById('schTable');
  if(!d.success||!d.data.length){body.innerHTML='<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted);">No schedules</td></tr>';return;}
  const catCol={Board:'var(--stat-blue)',Paint:'var(--stat-orange)',Glass:'var(--success)',Electric:'var(--warning)',Payment:'var(--primary)'};
  body.innerHTML=d.data.map(s=>`<tr><td>${fmtDate(s.schedule_date)}</td><td>${s.category?`<span class="badge" style="background:${catCol[s.category]??'var(--border)'}22;color:${catCol[s.category]??'var(--text-muted)'};">${esc(s.category)}</span>`:'-'}</td><td>${esc(s.description)}</td><td><span class="badge ${s.is_done?'badge-success':'badge-warning'}">${s.is_done?'Done':'Pending'}</span></td><td class="td-actions"><button class="btn btn-ghost btn-sm" onclick="toggleSchDone(${s.id},${s.is_done})">${s.is_done?'&#8635;':'&#10003;'}</button><button class="btn btn-ghost btn-sm btn-icon" onclick="delSch(${s.id})">&#10006;</button></td></tr>`).join('');
}
async function saveProjectSchedule(){
  const desc=document.getElementById('schDesc').value.trim();if(!desc){showToast('Description required','warning');return;}
  const fd=new FormData();fd.append('project_id',PID);fd.append('description',desc);fd.append('schedule_date',document.getElementById('schDateH').value||TODAY);fd.append('category',document.getElementById('schCat').value);
  const r=await fetch(BASE_PATH + '/api/schedules.php?action=create',{method:'POST',body:fd});const d=await r.json();
  if(d.success){showToast('Schedule added!','success');closeModal('addSchModal');loadSchedules();}else showToast(d.message||'Error','error');
}
async function toggleSchDone(id,isDone){await fetch(BASE_PATH + '/api/schedules.php?action=mark_done',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,is_done:isDone?0:1})});loadSchedules();}
async function delSch(id){
    confirmDelete('Delete schedule?',async function(){
        try {
            const r = await fetch(BASE_PATH + '/api/schedules.php?action=delete',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
            const d = await r.json();
            if (d.success) {
                showToast('Deleted','success');
                const btn = document.querySelector(`button[onclick="delSch(${id})"]`);
                if(btn) {
                    const row = btn.closest('tr') || btn.closest('.printout-item');
                    if(row) row.remove();
                }
                loadSchedules();
            } else {
                showToast(d.message || 'Error deleting', 'error');
            }
        } catch(e) {
            showToast('Connection error', 'error');
        }
    });
}

//  REPORTS 
async function loadReports(){
  const r=await fetch(BASE_PATH + '/api/reports.php?action=project_summary&project_id='+PID, {cache: 'no-store'});const d=await r.json();
  if(!d.success)return;const s=d.data;
  const budget=parseFloat(s.project.estimated_budget||0);
  const totalExpense=parseFloat(s.total_purchases||0)+parseFloat(s.total_contractor_paid||0)+parseFloat(s.total_labor_paid||0);
  document.getElementById('reportSummary').innerHTML=`
    <div class="summary-row"><span class="summary-label">Budget</span><span class="summary-value">${fmt(budget)}</span></div>
    <div class="summary-row"><span class="summary-label">Purchases</span><span class="summary-value red">${fmt(s.total_purchases)}</span></div>
    <div class="summary-row"><span class="summary-label">Contractor Paid</span><span class="summary-value red">${fmt(s.total_contractor_paid)}</span></div>
    <div class="summary-row"><span class="summary-label">Labor Paid</span><span class="summary-value red">${fmt(s.total_labor_paid)}</span></div>
    <div class="summary-row" style="border-top:2px solid var(--border);padding-top:10px;"><span class="summary-label" style="font-weight:700;">Total Expense</span><span class="summary-value red">${fmt(totalExpense)}</span></div>
    <div class="summary-row"><span class="summary-label">Client Paid</span><span class="summary-value green">${fmt(s.total_client_payments)}</span></div>
    <div class="summary-row"><span class="summary-label">Remaining</span><span class="summary-value ${budget-totalExpense>=0?'green':'red'}">${fmt(budget-totalExpense)}</span></div>`;
  const r2=await fetch(BASE_PATH + '/api/reports.php?action=project_purchases&project_id='+PID);const d2=await r2.json();
  if(d2.success&&d2.by_category){
    const cats=Object.entries(d2.by_category).sort((a,b)=>b[1]-a[1]);
    document.getElementById('purchByCat').innerHTML=cats.map(([cat,amt])=>{const pct=d2.total>0?Math.round(amt/d2.total*100):0;return`<div style="margin-bottom:12px;"><div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;"><span>${esc(cat)}</span><span style="font-weight:700;">${fmt(amt)} (${pct}%)</span></div><div class="progress-bar-bg"><div class="progress-bar-fill blue" style="width:${pct}%"></div></div></div>`;}).join('');}
}

//  PRINTOUTS 
async function loadPrintouts() {
  const r = await fetch(BASE_PATH + '/api/printouts.php?action=list&project_id=' + PID + '&_t=' + Date.now());
  const d = await r.json();
  const box=document.getElementById('printoutsList');
  if(!d.success||!d.data.length){box.innerHTML='<div class="empty-state"><p>No saved printouts yet</p></div>';return;}
  box.innerHTML=d.data.map(p=>`<div class="printout-item"><div class="printout-icon">&#128247;</div><div class="printout-body"><div class="printout-title">${esc(p.title)}</div><div class="printout-meta">${fmtDate(p.created_at)} &middot; ${Math.round(p.file_size/1024)}KB</div></div><div style="display:flex;gap:6px;"><a href="${BASE_PATH}/${esc(p.file_path)}" target="_blank" class="btn btn-outline btn-sm">View</a><button class="btn btn-ghost btn-sm btn-icon" onclick="delPrintout(${p.id})">&#10006;</button></div></div>`).join('');
}
async function delPrintout(id){
    confirmDelete('Delete printout?',async function(pwd){
        try {
            const r = await fetch(BASE_PATH + '/api/printouts.php?action=delete&project_id='+PID,{
                method:'POST',
                headers:{'Content-Type':'application/json'},
                body:JSON.stringify({id, admin_password: pwd})
            });
            const d = await r.json();
            if (d.success) {
                showToast('Deleted','success');
                const btn = document.querySelector(`button[onclick="delPrintout(${id})"]`);
                if(btn) btn.closest('.printout-item').remove();
                loadPrintouts();
            } else {
                showToast(d.message || 'Error deleting', 'error');
            }
        } catch(e) {
            showToast('Connection error', 'error');
        }
    });
}

//  UPDATE PROJECT 
async function updateProject(){
  const name=document.getElementById('epName').value.trim();if(!name){showToast('Name required','warning');return;}
  const startRaw = document.getElementById('epStartDateH').value || (window.SmartDate ? window.SmartDate.parse(document.getElementById('epStartDate').value) : '') || document.getElementById('epStartDate').value || '';
  const endRaw   = document.getElementById('epEndDateH').value || (window.SmartDate ? window.SmartDate.parse(document.getElementById('epEndDate').value) : '') || document.getElementById('epEndDate').value || '';

  const fd=new FormData();fd.append('id',PID);fd.append('name',name);fd.append('status',document.getElementById('epStatus').value);
  fd.append('address',document.getElementById('epAddress').value);fd.append('client_name',document.getElementById('epClient').value);
  fd.append('client_phone',document.getElementById('epPhone').value);fd.append('estimated_budget',document.getElementById('epBudget').value||0);
  fd.append('start_date',startRaw);
  fd.append('end_date',endRaw);
  fd.append('notes',document.getElementById('epNotes').value);
  const r=await fetch(BASE_PATH + '/api/projects.php?action=update',{method:'POST',body:fd});const d=await r.json();
  if(d.success){showToast('Project updated!','success');closeModal('editProjectModal');location.reload();}else showToast(d.message||'Error','error');
}

//  IMAGE UPLOAD 
async function uploadProjectImage(input){
  const file=input.files[0];if(!file)return;
  const fd=new FormData();fd.append('image',file);fd.append('project_id',PID);fd.append('is_primary',1);
  const t=showToast('Uploading...','info',0);
  const r=await fetch(BASE_PATH + '/api/upload.php?action=project_image',{method:'POST',body:fd});const d=await r.json();
  if(t)t.remove();
  if(d.success){showToast('Image updated!','success');setTimeout(()=>location.reload(),800);}else showToast(d.message||'Error','error');
}

//  FINAL BILL GENERATOR 
async function openFinalBillModal(defaultType = 'contractor') {
  const form = document.getElementById('finalBillForm');
  if (form) {
    const projInput = form.querySelector('input[name="project_id"]');
    if (projInput) projInput.value = PID;
  }
  const pNameEl = document.getElementById('fbProjectName');
  if (pNameEl) pNameEl.textContent = <?= json_encode($project['name'] ?? 'Project') ?>;
  const typeSelect = document.getElementById('fbType');
  if (typeSelect) typeSelect.value = defaultType;
  await loadFbTargets();
  openModal('generateFinalBillModal');
  const targetSel = document.getElementById('fbTarget');
  if (targetSel && targetSel.value) {
    fetchFinalBillData(targetSel.value);
  }
}

async function loadFbTargets() {
  const type = document.getElementById('fbType').value;
  const targetSel = document.getElementById('fbTarget');
  targetSel.innerHTML = '<option value="">-- Loading... --</option>';
  
  try {
    if (type === 'contractor') {
      const r = await fetch(BASE_PATH + '/api/billing.php?action=list_project_contractors&project_id=' + PID);
      const d = await r.json();
      let list = d.data || [];
      if (!list.length) {
        const r2 = await fetch(BASE_PATH + '/api/billing.php?action=get_all_contractors');
        const d2 = await r2.json();
        list = d2.data || [];
      }
      targetSel.innerHTML = '<option value="">-- Select Contractor --</option>' + list.map(c => `<option value="${c.contractor_id || c.id}">${esc(c.name)} (${esc(c.trade||'Contractor')})</option>`).join('');
    } else {
      const r = await fetch(BASE_PATH + '/api/workers.php?action=list');
      const d = await r.json();
      const list = d.data || [];
      targetSel.innerHTML = '<option value="">-- Select Worker --</option>' + list.map(w => `<option value="${w.id}">${esc(w.name)} (${esc(w.trade||'Worker')})</option>`).join('');
    }
  } catch(e) {
    targetSel.innerHTML = '<option value="">-- Error loading --</option>';
  }
}

async function fetchFinalBillData(targetId) {
  if (!targetId) return;
  const type = document.getElementById('fbType').value;
  const container = document.getElementById('fbItemsContainer');
  if (!container) return;
  container.innerHTML = '';
  let hasItems = false;

  if (type === 'contractor') {
    try {
      const r = await fetch(BASE_PATH + '/api/billing.php?action=get_category_summary&project_id=' + PID + '&contractor_id=' + targetId);
      const d = await r.json();
      if (d.success) {
        if (d.data && d.data.length > 0) {
          d.data.forEach(item => {
            addFbRow(item.description || item.item_name, item.thickness || '', item.total_qty || item.qty || 1, item.rate || '');
            hasItems = true;
          });
        }
        if (d.attendance && d.attendance.length > 0) {
          d.attendance.forEach(att => {
            const role = att.person_type === 'contractor' ? 'Contractor' : 'Crew Labor';
            const desc = `${att.name} (${role})`;
            addFbRow(desc, '', att.days, att.rate || '');
            hasItems = true;
          });
        }
      }
    } catch(e) {}
  } else {
    try {
      const r = await fetch(BASE_PATH + '/api/billing.php?action=get_worker_bill_data&project_id=' + PID + '&worker_id=' + targetId);
      const d = await r.json();
      if (d.success && d.items && d.items.length > 0) {
        d.items.forEach(item => {
          addFbRow(item.description, item.thickness || '', item.qty, item.rate);
          hasItems = true;
        });
      }
    } catch(e) {}
  }

  if (!hasItems) {
    addFbRow();
  }
  calcFbTotal();
}

function addFbRow(desc = '', thickness = '', qty = '', rate = '') {
  const container = document.getElementById('fbItemsContainer');
  if (!container) return;
  const div = document.createElement('div');
  div.className = 'fb-row bill-item-row';
  div.innerHTML = `
    <div class="fb-row-main">
      <div class="fb-row-check-wrap">
        <input type="checkbox" class="fb-checkbox row-selector" title="Select to group">
      </div>
      <div class="fb-row-desc-wrap">
        <input type="text" class="fb-input bill-desc" placeholder="Description / Item name" value="${esc(desc)}">
      </div>
      <button type="button" class="fb-row-btn fb-row-del-mobile" onclick="removeFbRow(this)" title="Delete Row">
        <svg viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" width="16" height="16">
          <polyline points="3 6 5 6 21 6"></polyline>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        </svg>
      </button>
    </div>
    <div class="fb-row-sub">
      <div class="fb-thick-col">
        <label class="fb-mobile-sub-label">Thickness</label>
        <input type="text" class="fb-input bill-thickness" placeholder="e.g. 12mm" value="${esc(thickness)}">
      </div>
      <div class="fb-qty-col">
        <label class="fb-mobile-sub-label">Qty</label>
        <input type="number" class="fb-input bill-qty" placeholder="Qty" value="${qty}" step="any" oninput="calcFbTotal()">
      </div>
      <div class="fb-rate-col">
        <label class="fb-mobile-sub-label">Rate</label>
        <input type="number" class="fb-input bill-rate" placeholder="Rate" value="${rate}" step="any" oninput="calcFbTotal()">
      </div>
      <div class="fb-actions-col">
        <button type="button" class="fb-row-btn fb-row-clear-btn" onclick="clearFbRow(this)" title="Clear Row">
          <svg viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2.2" width="16" height="16">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
        <button type="button" class="fb-row-btn fb-row-del-desktop" onclick="removeFbRow(this)" title="Delete Row">
          <svg viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" width="16" height="16">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
        </button>
      </div>
    </div>
  `;
  container.appendChild(div);
  calcFbTotal();
}

function clearFbRow(btn) {
  const row = btn.closest('.fb-row');
  if (!row) return;
  const desc = row.querySelector('.bill-desc');
  const thick = row.querySelector('.bill-thickness');
  const qty = row.querySelector('.bill-qty');
  const rate = row.querySelector('.bill-rate');
  if (desc) desc.value = '';
  if (thick) thick.value = '';
  if (qty) qty.value = '';
  if (rate) rate.value = '';
  calcFbTotal();
}

function removeFbRow(btn) {
  const row = btn.closest('.fb-row');
  if (!row) return;
  row.remove();
  calcFbTotal();
}

function toggleSelectAllFb(masterCb) {
  const cbs = document.querySelectorAll('#fbItemsContainer .row-selector');
  cbs.forEach(cb => cb.checked = masterCb.checked);
}

function calcFbTotal() {
  let grand = 0;
  const rows = document.querySelectorAll('#fbItemsContainer .bill-item-row');
  rows.forEach(r => {
    const qty = parseFloat(r.querySelector('.bill-qty').value) || 0;
    const rate = parseFloat(r.querySelector('.bill-rate').value) || 0;
    grand += (qty * rate);
  });
  const el = document.getElementById('fbGrandTotal');
  if (el) el.textContent = fmt(grand);
}

function groupSelectedRows() {
  const checkedRows = Array.from(document.querySelectorAll('#fbItemsContainer .row-selector:checked')).map(cb => cb.closest('.bill-item-row'));
  if (checkedRows.length < 2) {
    showToast('Please select at least 2 rows to group', 'warning');
    return;
  }
  let totalQty = 0;
  let descs = [];
  let thicknesses = [];
  checkedRows.forEach(r => {
    const desc = r.querySelector('.bill-desc').value.trim();
    const thick = r.querySelector('.bill-thickness') ? r.querySelector('.bill-thickness').value.trim() : '';
    if (desc) descs.push(desc);
    if (thick) thicknesses.push(thick);
    totalQty += (parseFloat(r.querySelector('.bill-qty').value) || 0);
    r.remove();
  });
  totalQty = Math.round(totalQty * 100) / 100;
  const uniqueDescs = [...new Set(descs)];
  
  let groupedThickness = '';
  if (thicknesses.length > 0) {
    const uniqueThick = [...new Set(thicknesses.filter(Boolean))];
    if (uniqueThick.length === 1) {
      groupedThickness = uniqueThick[0];
    } else if (uniqueThick.length > 1) {
      const nums = thicknesses.map(t => parseFloat(t.replace(/[^0-9.]/g, ''))).filter(n => !isNaN(n));
      if (nums.length > 0) {
        const min = Math.min(...nums);
        const max = Math.max(...nums);
        groupedThickness = (min === max) ? min + 'mm' : min + 'mm - ' + max + 'mm';
      } else {
        groupedThickness = uniqueThick.join(', ');
      }
    }
  }

  addFbRow(uniqueDescs.join(', '), groupedThickness, totalQty || 1, '');
  calcFbTotal();
}

function generateAndPrintFb() {
  const form = document.getElementById('finalBillForm');
  const targetId = document.getElementById('fbTarget').value;
  if (!targetId) {
    showToast('Please select a contractor or worker', 'warning');
    return;
  }
  const rows = document.querySelectorAll('#fbItemsContainer .bill-item-row');
  const items = [];
  rows.forEach(r => {
    const desc = r.querySelector('.bill-desc').value.trim();
    const thick = r.querySelector('.bill-thickness') ? r.querySelector('.bill-thickness').value.trim() : '';
    const qty = parseFloat(r.querySelector('.bill-qty').value) || 0;
    const rate = parseFloat(r.querySelector('.bill-rate').value) || 0;
    if (desc || qty > 0) {
      items.push({
        description: desc,
        thickness: thick,
        qty: qty,
        rate: rate,
        total: (qty * rate)
      });
    }
  });
  if (items.length === 0) {
    showToast('Please add at least one bill item', 'warning');
    return;
  }
  document.getElementById('fbItemsJson').value = JSON.stringify(items);
  form.submit();
}

//  INIT 
var isProjectDetailInitialized = false;
function initProjectDetailPage(){
  if (isProjectDetailInitialized) return;
  isProjectDetailInitialized = true;
  if (typeof SmartDate !== 'undefined' && SmartDate.initAll) {
    try { SmartDate.initAll(); } catch(e){}
  }
  loadHeaderStats();
  var params = new URLSearchParams(window.location.search);
  if (params.get('openfb') === '1' || params.get('tab') === 'printouts') {
    var tabBtn = document.querySelector('.tab-btn[onclick*="tabPrintouts"]');
    if (tabBtn) switchTab(tabBtn, 'tabPrintouts');
    if (params.get('openfb') === '1') {
      setTimeout(function(){
        openFinalBillModal('contractor');
      }, 350);
    }
  } else {
    loadPurchases();
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initProjectDetailPage);
} else {
  initProjectDetailPage();
}
window.addEventListener('load', initProjectDetailPage);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
