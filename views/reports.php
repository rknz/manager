<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle='Reports';$activeNav='reports';
$projects=$pdo->query("SELECT id,name FROM app_projects WHERE is_deleted=0 ORDER BY name")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="filter-bar">
  <select id="rptProject" class="form-select" style="max-width:280px;" onchange="loadReport()"><option value="">-- Select Project --</option><?php foreach($projects as $p): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option><?php endforeach; ?></select>
  <div class="filter-tabs">
    <button class="filter-tab active" data-rpt="summary" onclick="setRpt(this,'summary')">Summary</button>
    <button class="filter-tab" data-rpt="purchases" onclick="setRpt(this,'purchases')">Purchases</button>
    <button class="filter-tab" data-rpt="labor" onclick="setRpt(this,'labor')">Labor</button>
    <button class="filter-tab" data-rpt="attendance" onclick="setRpt(this,'attendance')">Attendance</button>
    <button class="filter-tab" data-rpt="payments" onclick="setRpt(this,'payments')">Payments</button>
    <button class="filter-tab" data-rpt="expense" onclick="setRpt(this,'expense')">Expense</button>
    <button class="filter-tab" data-rpt="financial" onclick="setRpt(this,'financial')">Financial</button>
  </div>
</div>
<style>
.rpt-bar { background: #fff; border: 1px solid #e5e7eb; border-left: 4px solid #9C1F24; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
.rpt-bar .rb-title { font-weight: 700; font-size: 15px; color: #1f2937; }
.rpt-bar .rb-proj { background: #f3f4f6; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; color: #374151; }
.rpt-bar .rb-hint { font-size: 12px; color: #6b7280; }
.rpt-empty { border: 1px dashed #d1d5db; border-radius: 8px; padding: 18px; text-align: center; color: #6b7280; font-size: 13px; background: #fff; margin-bottom: 14px; }
.rpt-empty b { color: #1f2937; }
.rpt-empty-state { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:38px 24px; text-align:center; }
.rpt-empty-state .res-icon { width:64px; height:64px; margin:0 auto 16px; border-radius:50%; background:#F8EFEF; display:flex; align-items:center; justify-content:center; font-size:28px; box-shadow:inset 0 0 0 1px rgba(156,31,36,.06); }
.rpt-empty-state .res-title { font-size:16px; font-weight:700; color:#1F2937; margin-bottom:6px; }
.rpt-empty-state .res-desc { font-size:13px; color:#6B7280; max-width:460px; margin:0 auto; line-height:1.65; }
.rpt-empty-state .res-desc b { color:#374151; }
.rpt-bar-row { display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
.rpt-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px 18px; font-size:13px; }
.rpt-info-grid .ig-item { display:flex; flex-direction:column; gap:2px; }
.rpt-info-grid .ig-v { font-weight:600; color:var(--text-primary); word-break:break-word; }
.rpt-info-grid .ig-k { font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.03em; }
.na { color:#9CA3AF; }
.vchart { display:flex; gap:8px; align-items:flex-end; height:150px; padding-top:6px; }
.vcol { flex:1; display:flex; align-items:flex-end; justify-content:flex-end; gap:3px; height:100%; flex-direction:row; }
.vbar { width:12px; max-width:34%; border-radius:4px 4px 0 0; min-height:0; transition:height .5s ease; }
.vlabel { position:absolute; } 
.rpt-legend { display:flex; gap:16px; flex-wrap:wrap; margin-bottom:10px; font-size:12px; color:var(--text-secondary); }
.rpt-legend .lg-item { display:inline-flex; align-items:center; gap:6px; }
.rpt-legend i { width:10px; height:10px; border-radius:2px; display:inline-block; }
.chart-wrap { display:flex; height:150px; }
.chart-wrap .ylabels { display:flex; flex-direction:column; justify-content:space-between; padding:2px 4px 22px 0; color:#9CA3AF; font-size:10px; text-align:right; }
.chart-inner { flex:1; display:flex; align-items:flex-end; gap:6px; overflow-x:auto; }
.chart-inner .ccol { flex:1; min-width:18px; display:flex; align-items:flex-end; justify-content:center; height:100%; }
.chart-inner .cbar { width:70%; max-width:34px; border-radius:4px 4px 0 0; transition:height .5s ease; }
.cxlabels { display:flex; gap:6px; padding-top:6px; }
.cxlabels span { flex:1; text-align:center; font-size:10px; color:#9CA3AF; white-space:nowrap; overflow:hidden; }
.rpt-pager { display:flex; align-items:center; justify-content:flex-end; gap:10px; padding:10px 14px 0; font-size:12px; color:var(--text-muted); }
.rpt-pager .pg-btn { background:transparent; border:1px solid var(--border); color:var(--text-secondary); border-radius:6px; width:26px; height:26px; cursor:pointer; font-size:13px; line-height:1; }
.rpt-pager .pg-btn:hover:not(:disabled){ border-color:var(--primary); color:var(--primary); }
.rpt-pager .pg-btn:disabled{ opacity:.4; cursor:not-allowed; }
.health-card { border-radius:12px; padding:18px; display:flex; gap:14px; align-items:center; }
.health-card .hc-icon { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
.health-card .hc-label { font-size:15px; font-weight:700; }
.health-card .hc-detail { font-size:12.5px; margin-top:3px; opacity:.85; line-height:1.5; }
.health-card.tone-success { background:#ECFDF5; border:1px solid #A7F3D0; }
.health-card.tone-success .hc-icon { background:#D1FAE5; }
.health-card.tone-warn { background:#FFFBEB; border:1px solid #FDE68A; }
.health-card.tone-warn .hc-icon { background:#FEF3C7; }
.health-card.tone-danger { background:#FEF2F2; border:1px solid #FECACA; }
.health-card.tone-danger .hc-icon { background:#FEE2E2; }
.health-card.tone-muted { background:#F9FAFB; border:1px solid #E5E7EB; }
.health-card.tone-muted .hc-icon { background:#F3F4F6; }
.stat-note { font-size:12px; color:#9CA3AF; margin-top:6px; }
@media print {
  body * { visibility: hidden; }
  #rptPurchases, #rptPurchases * { visibility: visible; }
  #rptPurchases { position: absolute; left: 0; top: 0; width: 100%; }
  .hide-on-print { display: none !important; }
  .no-print { display: none !important; }
}
</style>

<div id="rptNoProj" class="rpt-empty-state" style="display:none;">
  <div class="res-icon">&#128194;</div>
  <div class="res-title">Select a project</div>
  <div class="res-desc">Choose a project from the dropdown above to load its financial reports &amp; analytics. Every section — KPIs, tables, filters and charts — is scoped to the selected project.</div>
</div>

<div class="rpt-sec" id="rptSummary" style="display:none;"></div>

<div class="rpt-sec" id="rptPurchases" style="display:none;">
  <div class="rpt-bar" id="purchBar"><span class="rb-title">&#128722; Purchases</span><span class="rb-hint">Select a project to load this section</span></div>
  <div class="rpt-bar-row mt-2 no-print">
    <input type="text" id="rptQ" class="form-input" placeholder="Search item / supplier / notes..." style="max-width:220px;" onchange="loadReport()">
    <input type="text" id="rptFrom" class="form-input smart-date" placeholder="From" data-date-target="rptFromH" style="max-width:120px;" onchange="loadReport()"><input type="hidden" id="rptFromH">
    <input type="text" id="rptTo"   class="form-input smart-date" placeholder="To"   data-date-target="rptToH"   style="max-width:120px;" onchange="loadReport()"><input type="hidden" id="rptToH">
    <select id="rptCat" class="form-select" style="max-width:160px;" onchange="loadReport()"><option value="">All Categories</option></select>
    <select id="rptSup" class="form-select" style="max-width:160px;" onchange="loadReport()"><option value="">All Suppliers</option></select>
    <button class="btn btn-ghost btn-sm" onclick="clearPurchFilters()">&#10005; Clear</button>
  </div>
  <div id="purchEmpty" style="display:none;"></div>
  <div id="purchContent">
    <div class="stats-row" id="purchKpis"></div>
    <div class="card mt-4">
      <div class="card-header"><h3>Purchase List</h3><span id="purchRptTotal" class="badge badge-danger">Tk. 0</span><button class="btn btn-outline btn-sm no-print" style="margin-left:auto;" onclick="window.print()">Print</button></div>
      <div class="table-wrapper"><table class="data-table print-friendly"><thead><tr><th>Date</th><th>Item</th><th>Cat</th><th>Supplier</th><th class="text-right">Qty</th><th>Unit</th><th class="text-right">Rate</th><th class="text-right">Total</th></tr></thead><tbody id="purchRptTable"></tbody></table></div>
      <div id="purchPager" class="rpt-pager"></div>
    </div>
    <div class="two-col mt-4">
      <div class="card"><div class="card-header"><h3>By Category</h3></div><div class="card-body" id="purchCatChart"></div></div>
      <div class="card"><div class="card-header"><h3>By Supplier</h3></div><div class="card-body" id="purchSupChart"></div></div>
    </div>
    <div class="card mt-4"><div class="card-header"><h3>Monthly Purchases</h3></div><div class="card-body" id="purchMonthChart"></div></div>
  </div>
</div>

<div class="rpt-sec" id="rptLabor" style="display:none;">
  <div class="rpt-bar" id="laborBar"><span class="rb-title">&#128168; Labor Wages</span><span class="rb-hint">Select a project to load this section</span></div>
  <div class="rpt-bar-row mt-2 no-print">
    <input type="text" id="labQ" class="form-input" placeholder="Search worker..." style="max-width:180px;" onchange="loadReport()">
    <select id="labTrade" class="form-select" style="max-width:160px;" onchange="loadReport()"><option value="">All Trades</option></select>
    <input type="text" id="labFrom" class="form-input smart-date" placeholder="From" data-date-target="labFromH" style="max-width:120px;" onchange="loadReport()"><input type="hidden" id="labFromH">
    <input type="text" id="labTo"   class="form-input smart-date" placeholder="To"   data-date-target="labToH"   style="max-width:120px;" onchange="loadReport()"><input type="hidden" id="labToH">
    <button class="btn btn-ghost btn-sm" onclick="clearLaborFilters()">&#10005; Clear</button>
  </div>
  <div id="laborEmpty" style="display:none;"></div>
  <div id="laborContent">
    <div class="stats-row" id="laborKpis"></div>
    <div class="two-col mt-4" style="align-items:start;">
      <div class="card"><div class="card-header"><h3>Labor Category Breakdown</h3><span id="laborCatTotal" class="badge badge-neutral">Tk. 0</span></div><div class="card-body" id="laborCatChart"></div></div>
      <div class="card">
        <div class="card-header"><h3>Worker-wise Labor Summary</h3><span id="laborSumBadge" class="badge badge-success">Tk. 0</span></div>
        <div class="table-wrapper"><table class="data-table"><thead><tr><th>Worker</th><th>Trade</th><th>Phone</th><th class="text-right">Rate</th><th class="text-right">Days</th><th class="text-right">Earned</th><th class="text-right">Paid</th><th class="text-right">Due</th></tr></thead><tbody id="laborSumTable"></tbody></table></div>
      </div>
    </div>
    <div class="two-col mt-4" style="align-items:start;">
      <div class="card"><div class="card-header"><h3>Monthly Labor Cost</h3></div><div class="card-body" id="laborMonthChart"></div></div>
      <div class="card">
        <div class="card-header"><h3>Labor Payment History</h3><span id="laborRptTotal" class="badge badge-warning">Tk. 0</span></div>
        <div class="table-wrapper"><table class="data-table"><thead><tr><th>Worker</th><th>Trade</th><th class="text-right">Amount</th><th>Date</th></tr></thead><tbody id="laborRptTable"></tbody></table></div>
      </div>
    </div>
  </div>
</div>

<div class="rpt-sec" id="rptAttendance" style="display:none;">
  <div class="rpt-bar" id="attBar"><span class="rb-title">&#128203; Attendance</span><span class="rb-hint">Select a project to load this section</span></div>
  <div id="attStats" class="stats-row mt-2" style="display:none;"></div>
  <div class="card mt-4">
    <div class="card-header" style="flex-wrap:wrap;gap:10px;">
      <h3>&#128203; Attendance Records</h3>
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;" class="no-print">
        <div id="attRptChips" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
          <button type="button" class="filter-chip" data-range="today" onclick="setAttRangeRpt('today')">Today</button>
          <button type="button" class="filter-chip" data-range="week" onclick="setAttRangeRpt('week')">This Week</button>
          <button type="button" class="filter-chip" data-range="month" onclick="setAttRangeRpt('month')">This Month</button>
          <button type="button" class="filter-chip" data-range="lastmonth" onclick="setAttRangeRpt('lastmonth')">Last Month</button>
          <button type="button" class="filter-chip" data-range="all" onclick="setAttRangeRpt('all')">All Time</button>
        </div>
        <select id="attWorker" class="form-select" style="max-width:150px;" onchange="loadReport()"><option value="">All Workers</option></select>
        <select id="attTrade" class="form-select" style="max-width:130px;" onchange="loadReport()"><option value="">All Trades</option></select>
        <select id="attStatus" class="form-select" style="max-width:120px;" onchange="loadReport()"><option value="">All Status</option><option value="present">Present</option><option value="half">Half Day</option><option value="leave">Leave/Off</option><option value="overtime">Overtime</option></select>
        <span style="color:var(--text-light);font-size:12px;">Range:</span>
        <input type="text" id="attRptFrom" class="form-input smart-date" placeholder="From" data-date-target="attRptFromH" style="max-width:105px;" onchange="loadReport();clearAttChipsRpt()"><input type="hidden" id="attRptFromH">
        <input type="text" id="attRptTo"   class="form-input smart-date" placeholder="To"   data-date-target="attRptToH"   style="max-width:105px;" onchange="loadReport();clearAttChipsRpt()"><input type="hidden" id="attRptToH">
        <span id="attRptTotalBadge" class="badge badge-primary">Tk. 0</span>
      </div>
    </div>
    <div id="attTableWrap">
      <div class="table-wrapper">
        <table class="data-table">
          <thead><tr><th>Worker</th><th>Trade</th><th>Type</th><th class="text-right">Rate</th><th class="text-right">Earned</th><th>Date</th><th></th></tr></thead>
          <tbody id="attRptBody"><tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-muted);">Select a project</td></tr></tbody>
          <tfoot><tr><td colspan="4" style="text-align:right;">Total Earned:</td><td id="attRptTotal" class="td-amount">Tk. 0</td><td colspan="2"></td></tr></tfoot>
        </table>
      </div>
    </div>
    <div id="attTableEmpty" style="display:none;"></div>
  </div>
  <div class="two-col mt-4">
    <div class="card"><div class="card-header"><h3>Daily Attendance</h3></div><div class="card-body" id="attDayChart"></div></div>
    <div class="card"><div class="card-header"><h3>Attendance Rate (Monthly)</h3></div><div class="card-body" id="attMonChart"></div></div>
  </div>
  <div class="card mt-4"><div class="card-header"><h3>Status Distribution</h3></div><div class="card-body" id="attStatusChart"></div></div>
</div>

<div class="rpt-sec" id="rptPayments" style="display:none;">
  <div class="rpt-bar" id="payBar"><span class="rb-title">&#128176; Payments</span><span class="rb-hint">Select a project to load this section</span></div>
  <div id="payEmpty" style="display:none;"></div>
  <div id="payContent">
    <div class="stats-row" id="payStats"></div>
    <div class="card mt-4">
      <div class="card-header"><h3>Client Payments (Received)</h3><span id="clientRptTotal" class="badge badge-success">Tk. 0</span></div>
      <div class="table-wrapper"><table class="data-table"><thead><tr><th>Date</th><th>Method</th><th class="text-right">Amount</th><th>Notes</th></tr></thead><tbody id="clientRptTable"></tbody></table></div>
    </div>
    <div class="two-col mt-4" style="align-items:start;">
      <div class="card">
        <div class="card-header"><h3>Contractor Payments (Advances)</h3><span id="contractorRptTotal" class="badge badge-danger">Tk. 0</span></div>
        <div class="table-wrapper"><table class="data-table"><thead><tr><th>Contractor</th><th>Trade</th><th class="text-right">Amount</th><th>Date</th><th>Method</th></tr></thead><tbody id="contractorRptTable"></tbody></table></div>
      </div>
      <div class="card">
        <div class="card-header"><h3>Labor Payments</h3><span id="laborPayTotal" class="badge badge-warning">Tk. 0</span></div>
        <div class="table-wrapper"><table class="data-table"><thead><tr><th>Worker</th><th>Trade</th><th class="text-right">Amount</th><th>Date</th></tr></thead><tbody id="laborPayTable"></tbody></table></div>
      </div>
    </div>
    <div class="card mt-4">
      <div class="card-header"><h3>Other Expenses Paid</h3><span id="expPayTotal" class="badge badge-info">Tk. 0</span></div>
      <div class="table-wrapper"><table class="data-table"><thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Vendor</th><th class="text-right">Amount</th><th>Method</th></tr></thead><tbody id="expPayTable"></tbody></table></div>
    </div>
    <div class="two-col mt-4">
      <div class="card"><div class="card-header"><h3>Monthly Received vs Paid</h3></div><div class="card-body" id="payFlowChart"></div></div>
      <div class="card"><div class="card-header"><h3>Received vs Receivable</h3></div><div class="card-body" id="payRecChart"></div></div>
    </div>
  </div>
</div>

<div class="rpt-sec" id="rptExpense" style="display:none;">
  <div class="rpt-bar" id="expBar"><span class="rb-title">&#128176; Expenses</span><span class="rb-hint">Select a project to load this section</span></div>
  <div class="rpt-bar-row mt-2 no-print">
    <input type="text" id="expQ" class="form-input" placeholder="Search description / vendor..." style="max-width:200px;" onchange="loadReport()">
    <input type="text" id="expFrom" class="form-input smart-date" placeholder="From" data-date-target="expFromH" style="max-width:120px;" onchange="loadReport()"><input type="hidden" id="expFromH">
    <input type="text" id="expTo"   class="form-input smart-date" placeholder="To"   data-date-target="expToH"   style="max-width:120px;" onchange="loadReport()"><input type="hidden" id="expToH">
    <select id="expCat" class="form-select" style="max-width:140px;" onchange="loadReport()"><option value="">All Categories</option></select>
    <select id="expStatus" class="form-select" style="max-width:120px;" onchange="loadReport()"><option value="">All Status</option><option value="paid">Paid</option><option value="partial">Partial</option><option value="unpaid">Unpaid</option></select>
    <select id="expMethod" class="form-select" style="max-width:140px;" onchange="loadReport()"><option value="">All Methods</option><option>Cash</option><option>Bank Transfer</option><option>Cheque</option><option>bKash</option><option>Nagad</option></select>
    <button class="btn btn-ghost btn-sm" onclick="clearExpFilters()">&#10005; Clear</button>
    <button class="btn btn-primary btn-sm" style="margin-left:auto;" onclick="openExpModal()">&#43; Add Expense</button>
  </div>
  <div id="expEmpty" style="display:none;"></div>
  <div id="expContent">
    <div class="stats-row" id="expKpis"></div>
    <div class="card mt-4">
      <div class="card-header"><h3>Expense Ledger</h3><span id="expRptTotal" class="badge badge-danger">Tk. 0</span></div>
      <div class="table-wrapper"><table class="data-table"><thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Vendor</th><th class="text-right">Amount</th><th class="text-right">Paid</th><th class="text-right">Due</th><th>Method</th><th>Status</th><th></th></tr></thead><tbody id="expRptTable"></tbody></table></div>
      <div id="expPager" class="rpt-pager"></div>
    </div>
    <div class="two-col mt-4">
      <div class="card"><div class="card-header"><h3>By Category</h3></div><div class="card-body" id="expCatChart"></div></div>
      <div class="card"><div class="card-header"><h3>Paid vs Due</h3></div><div class="card-body" id="expPayChart"></div></div>
    </div>
    <div class="card mt-4"><div class="card-header"><h3>Monthly Expenses</h3></div><div class="card-body" id="expMonthChart"></div></div>
  </div>
</div>

<div class="rpt-sec" id="rptFinancial" style="display:none;">
  <div class="rpt-bar" id="finBar"><span class="rb-title">&#128200; Financial Overview</span><span class="rb-hint">Select a project to load this section</span></div>
  <div id="finContent">
    <div class="two-col mt-2" style="align-items:start;">
      <div class="card"><div class="card-header"><h3>Revenue</h3></div><div class="card-body" id="finRev"></div></div>
      <div class="card"><div class="card-header"><h3>Profit / Loss</h3></div><div class="card-body" id="finPL"></div></div>
    </div>
    <div class="two-col mt-4" style="align-items:start;">
      <div class="card"><div class="card-header"><h3>Project Costs</h3></div><div class="card-body" id="finCost"></div></div>
      <div class="card">
        <div class="card-header"><h3>Budget vs Actual</h3></div>
        <div class="table-wrapper"><table class="data-table"><thead><tr><th>Category</th><th class="text-right">Budget</th><th class="text-right">Actual</th><th class="text-right">Variance</th></tr></thead><tbody id="finBudgetTable"></tbody></table></div>
        <div class="card-body"><div id="finBudgetBar"></div><p class="stat-note">Per-line budgets are not tracked separately — only the project-level contract value (Estimated Budget) is used for comparison.</p></div>
      </div>
    </div>
    <div class="card mt-4"><div class="card-header"><h3>Financial Health</h3></div><div class="card-body" id="finHealth"></div></div>
    <div class="card mt-4"><div class="card-header"><h3>Cost Composition</h3></div><div class="card-body" id="finCostChart"></div></div>
    <div class="card mt-4"><div class="card-header"><h3>Monthly Cash Flow (In vs Out)</h3></div><div class="card-body" id="finFlowChart"></div></div>
  </div>
</div>

<div class="modal-overlay" id="addExpModal">
  <div class="modal" data-form-nav>
    <div class="modal-header"><h3 id="expModalTitle">Add Expense</h3><button class="modal-close" onclick="closeModal('addExpModal')">&times;</button></div>
    <div class="modal-body">
      <input type="hidden" id="expEditId">
      <div class="form-group"><label class="form-label">Category</label><input type="text" id="expCatIn" class="form-input" list="expCatList" placeholder="e.g. Transport"><datalist id="expCatList"><option>Transport</option><option>Food &amp; Boarding</option><option>Utilities</option><option>Equipment &amp; Tools</option><option>Office</option><option>Rent</option><option>Marketing</option><option>Repair &amp; Maintenance</option><option>Legal</option><option>Other</option></datalist></div>
      <div class="form-group"><label class="form-label">Description</label><input type="text" id="expDesc" class="form-input" placeholder="e.g. Fuel for site pickup"></div>
      <div class="form-group"><label class="form-label">Vendor / Payee</label><input type="text" id="expVendor" class="form-input" placeholder="e.g. Supplier name"></div>
      <div class="two-col">
        <div class="form-group"><label class="form-label">Amount (Tk) <span class="required">*</span></label><input type="number" id="expAmount" class="form-input" min="0" step="0.01" placeholder="0"></div>
        <div class="form-group"><label class="form-label">Paid (Tk)</label><input type="number" id="expPaid" class="form-input" min="0" step="0.01" placeholder="Full amount"></div>
      </div>
      <div class="two-col">
        <div class="form-group"><label class="form-label">Payment Method</label><select id="expMethodIn" class="form-select"><option>Cash</option><option>Bank Transfer</option><option>Cheque</option><option>bKash</option><option>Nagad</option></select></div>
        <div class="form-group"><label class="form-label">Date</label><input type="text" id="expDate" class="form-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="expDateH"><input type="hidden" id="expDateH" value="<?=date('Y-m-d')?>"></div>
      </div>
      <div class="form-group"><label class="form-label">Notes</label><textarea id="expNotes" class="form-input" rows="2" placeholder="Optional notes"></textarea></div>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addExpModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="saveExpense()">Save</button></div>
  </div>
</div>

<script>
var currentRpt = 'summary';
var projectsData = <?= json_encode($projects) ?>;
var purchPage=1, expPage=1;
var purchRpt=null, expRpt=null;

function fmt(n){return 'Tk. '+parseFloat(n||0).toLocaleString('en-BD',{maximumFractionDigits:0});}
function fmtNA(n){return (n===null||n===undefined||isNaN(n))?'N/A':fmt(n);}
function fmtK(n){n=Number(n||0);if(n>=1000000)return (n/1000000).toFixed(1).replace(/\.0$/,'')+'M';if(n>=1000)return (n/1000).toFixed(1).replace(/\.0$/,'')+'K';return String(Math.round(n));}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmtDate(d){if(!d)return'-';var dt=new Date(d);return isNaN(dt)?d:dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});}
function emptyRow(cols,msg){return '<tr><td colspan="'+cols+'" style="text-align:center;color:#9CA3AF;padding:18px;font-style:italic;">'+msg+'</td></tr>';}
function num(n){return Number(n||0).toLocaleString('en-BD',{maximumFractionDigits:1});}
function statCard(label,val,icon,bg){return '<div class="stat-card"><div class="stat-card-icon" style="background:'+bg+';">'+icon+'</div><div class="stat-card-body"><div class="stat-card-label">'+label+'</div><div class="stat-card-value">'+val+'</div></div></div>';}
function rptBar(el,title,pid,hint){const out='<span class="rb-title">'+title+'</span><span class="rb-proj">'+esc(projName(pid))+'</span><span class="rb-hint">'+hint+'</span>';document.getElementById(el).innerHTML=out;}
function rptEmptyState(icon,title,desc){return '<div class="rpt-empty-state"><div class="res-icon">'+icon+'</div><div class="res-title">'+title+'</div><div class="res-desc">'+desc+'</div></div>';}
function loadErrorPanel(cols,msg){return '<tr><td colspan="'+cols+'" style="text-align:center;padding:18px;"><span style="color:#DC2626;font-size:13px;">&#9888; '+msg+'</span> <a href="javascript:void(0)" onclick="loadReport()" style="color:var(--primary);font-weight:600;font-size:13px;">Retry</a></td></tr>';}
function loadErrorCard(what){return '<div class="card"><div class="card-body" style="color:#DC2626;font-size:13px;">&#9888; '+what+' could not be loaded. <a href="javascript:void(0)" onclick="loadReport()" style="color:var(--primary);font-weight:600;">Retry</a></div></div>';}
function setRpt(btn,mode){document.querySelectorAll('[data-rpt]').forEach(b=>b.classList.remove('active'));btn.classList.add('active');currentRpt=mode;loadReport();}
function secId(mode){const map={summary:'rptSummary',purchases:'rptPurchases',labor:'rptLabor',attendance:'rptAttendance',payments:'rptPayments',expense:'rptExpense',financial:'rptFinancial'};return map[mode];}
function showCurrentSec(pid){
  document.querySelectorAll('.rpt-sec').forEach(s=>s.style.display='none');
  const no=document.getElementById('rptNoProj');
  if(!pid){no.style.display='block';return;}
  no.style.display='none';
  document.getElementById(secId(currentRpt)).style.display='block';
}
function getPid(){return document.getElementById('rptProject').value;}
function projName(pid){const p=projectsData.find(x=>String(x.id)===String(pid));return p?p.name:'This project';}

async function getJSON(url){
  try {
    const r=await fetch(url,{cache:'no-store'});
    if(!r.ok) throw new Error('HTTP '+r.status);
    const d=await r.json();
    if(!d || d.success===false){
      if(d && d.redirect) { window.location.href=d.redirect; }
      throw new Error((d&&d.message)||'Request failed');
    }
    return d;
  } catch(e){
    console.error('API error:',url,e&&e.message?e.message:e);
    showToast('Data load failed. Please retry.','error');
    return null;
  }
}
function fillSel(elId,opts,ph,keep){
  const sel=document.getElementById(elId),cur=keep?sel.value:'';
  sel.innerHTML='<option value="">'+ph+'</option>'+opts.map(o=>'<option value="'+esc(o.value)+'">'+esc(o.label)+'</option>').join('');
  if(keep) sel.value=cur;
}
function monLabel(ym,ref){
  if(!ym)return'?';const d=new Date(ym+'-01');const lbl=d.toLocaleDateString('en-GB',{month:'short'});
  if(ref && d.getFullYear()!==new Date(ref+'-01').getFullYear()) return lbl+' '+String(d.getFullYear()).slice(2);
  return lbl;
}
function hBars(items,tot,color){
  if(!items||!items.length) return '<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
  return items.map(it=>{
    const pct=it.pct!==undefined?it.pct:(tot>0?Math.round((Number(it.value)||0)/tot*100):0);
    return `<div style="margin-bottom:12px;"><div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;"><span>${esc(it.label)}</span><span style="font-weight:700;">${it.txt||fmt(it.value)}${pct>=0?' ('+pct+'%)':''}</span></div><div class="progress-bar-bg"><div class="progress-bar-fill ${color}" style="width:${Math.min(100,pct)}%"></div></div></div>`;
  }).join('');
}
function axisChart(labels,series){
  if(!labels||!labels.length)return '<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
  let max=10;series.forEach(s=>labels.forEach(k=>{const v=Number(s.data[k]||0);if(v>max)max=v;}));max=max||1;
  const legend=series.length>1?'<div class="rpt-legend">'+series.map(s=>'<span class="lg-item"><i style="background:'+s.color+'"></i>'+s.label+'</span>').join('')+'</div>':'';
  const inner='<div class="chart-wrap"><div class="ylabels">'+[max,Math.round(max/2),0].map(n=>fmtK(n)).map(t=>'<span>'+t+'</span>').join('')+'</div><div class="chart-inner">'
    +labels.map(k=>{
      const bars=series.map(s=>{const v=Number(s.data[k]||0);const h=Math.max(v>0?2:0,Math.round(v/max*110));return '<div class="cbar" style="height:'+h+'px;background:'+s.color+';" title="'+esc(s.label)+': '+esc(fmt(v))+'"></div>';}).join('');
      return '<div class="ccol">'+bars+'</div>';
    }).join('')+'</div></div>';
  return legend+inner+'<div class="cxlabels">'+labels.map(k=>'<span title="'+esc(k)+'">'+esc(monLabel(k,labels[0]))+'</span>').join('')+'</div>';
}
function numGrid(grid){return '<div class="rpt-info-grid">'+grid.map(g=>'<div class="ig-item"><span class="ig-k">'+esc(g.k)+'</span><span class="ig-v">'+g.v+'</span></div>').join('')+'</div>';}
function sumRow(label,val,cls){return `<div class="summary-row"><span class="summary-label">${label}</span><span class="summary-value ${cls||''}">${val}</span></div>`;}

function setRptTabContent(el,html){document.getElementById(el).innerHTML=html;}

/* ---------------- loadReport dispatch ---------------- */
async function loadReport(){
  const pid=getPid();
  showCurrentSec(pid);
  if(!pid) return;
  if(currentRpt==='summary')      await loadSummaryRpt(pid);
  else if(currentRpt==='purchases')   await loadPurchRpt(pid);
  else if(currentRpt==='labor')       await loadLaborRpt(pid);
  else if(currentRpt==='attendance')  await loadAttendanceRpt(pid);
  else if(currentRpt==='payments')    await loadPayRpt(pid);
  else if(currentRpt==='expense')     await loadExpenseRpt(pid);
  else if(currentRpt==='financial')   await loadFinancialRpt(pid);
}

/* ---------------- Summary ---------------- */
async function loadSummaryRpt(pid){
  const d=await getJSON(BASE_PATH+'/api/reports.php?action=project_summary&project_id='+pid);
  const box=document.getElementById('rptSummary');
  if(!d){box.innerHTML=loadErrorCard('Project summary');return;}
  const s=d.data,pr=s.project,budget=parseFloat(pr.estimated_budget||0),hasBud=budget>0;
  const spent=parseFloat(s.total_purchases||0)+parseFloat(s.total_contractor_paid||0)+parseFloat(s.total_labor_paid||0)+parseFloat(s.total_expenses||0);
  const received=parseFloat(s.total_client_payments||0);
  const receivable=hasBud?Math.max(0,budget-received):null;
  const profit=received-spent;
  const pct=budget>0?Math.min(100,Math.round(spent/budget*100)):0;
  const projType=pr.project_type||'Residential';
  const statusBadge={Ongoing:'badge-success','Completed':'badge-neutral','On Hold':'badge-warning'}[pr.status]||'badge-neutral';
  const cards=[
    {label:hasBud?'Contract Value':'Contract Value',val:hasBud?fmt(budget):'Tk. 0',icon:'&#128200;',bg:'var(--stat-blue-bg)'},
    {label:'Total Spent',val:fmt(spent),icon:'&#128722;',bg:'var(--stat-red-bg)'},
    {label:'Client Received',val:fmt(received),icon:'&#128176;',bg:'var(--stat-green-bg)'},
    {label:'Client Receivable',val:hasBud?fmt(receivable):'N/A',icon:'&#9203;',bg:'var(--stat-orange-bg)'},
    {label:'Profit / Loss',val:fmt(profit),icon:'&#128181;',bg:profit>=0?'var(--stat-green-bg)':'var(--stat-red-bg)'},
  ];
  const progress=hasBud
    ? `<div style="margin-bottom:12px;"><div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;"><span>Budget used</span><span style="font-weight:700;">${pct}%</span></div><div class="progress-bar-bg"><div class="progress-bar-fill ${spent>budget?'red':'blue'}" style="width:${pct}%"></div></div></div>`
    : '<div class="na" style="font-size:13px;">Contract value is not set — progress can\'t be measured. Set an Estimated Budget on the project to track spend vs budget.</div>';
  box.innerHTML=
    '<div class="rpt-bar" id="sumBar"><span class="rb-title">&#128200; Summary</span><span class="rb-proj">'+esc(projName(pid))+'</span><span class="rb-hint">Overall financial position and progress</span></div>'+
    '<div class="stats-row">'+cards.map(c=>statCard(c.label,c.val,c.icon,c.bg)).join('')+'</div>'+
    '<div class="two-col mt-4" style="align-items:start;">'+
      '<div class="card"><div class="card-header"><h3>Project Information</h3></div><div class="card-body">'+numGrid([
        {k:'Client',v:esc(pr.client_name||'-')},{k:'Phone',v:esc(pr.client_phone||'-')},
        {k:'Project Type',v:esc(projType)},{k:'Status',v:'<span class="badge '+statusBadge+'">'+esc(pr.status||'-')+'</span>'},
        {k:'Start Date',v:fmtDate(pr.start_date)},{k:'End Date',v:fmtDate(pr.end_date)},
        {k:'Address',v:esc(pr.address||'-')},{k:'Materials (items)',v:num(s.total_qty)+' qty'}
      ])+'</div></div>'+
      '<div class="card"><div class="card-header"><h3>Project Progress</h3></div><div class="card-body">'+progress+
        '<div style="font-size:12px;color:#6B7280;margin-top:4px;">Spent '+fmt(spent)+' of contract '+ (hasBud?fmt(budget):'not set') +'</div>'
      +'</div></div>'+
    '</div>'+
    '<div class="card mt-4"><div class="card-header"><h3>Financial Overview</h3><span id="sumOverViewBadge" class="badge badge-danger">Spent '+fmt(spent)+'</span></div><div class="card-body"><div class="quick-summary">'+
      sumRow('Material Purchases',fmt(s.total_purchases),'red')+
      sumRow('Contractor Paid',fmt(s.total_contractor_paid),'red')+
      sumRow('Contractor Billed (committed)',fmt(s.total_contractor_billed),'')+
      sumRow('Labor Earned',fmt(s.total_labor_earned),'')+
      sumRow('Labor Paid',fmt(s.total_labor_paid),'red')+
      sumRow('Other Expenses',fmt(s.total_expenses),'red')+
      sumRow('<strong>Total Spent</strong>',fmt(spent),'red')+
      sumRow('Client Received',fmt(received),'green')+
      sumRow('Client Receivable',hasBud?fmt(receivable):'N/A','')+
      sumRow('<strong>Profit / Loss</strong>',fmt(profit),profit>=0?'green':'red')+
    '</div></div></div>'+
    '<div class="card mt-4"><div class="card-header"><h3>Monthly Project Cost</h3></div><div class="card-body" id="sumMonCost"></div></div>';
  const months=Object.keys(Object.assign({},s.monthly_purchases,s.monthly_advances,s.monthly_labor,s.monthly_expenses)).sort();
  const outSeries={};months.forEach(m=>{outSeries[m]=(Number(s.monthly_purchases[m]||0)+Number(s.monthly_advances[m]||0)+Number(s.monthly_labor[m]||0)+Number(s.monthly_expenses[m]||0));});
  document.getElementById('sumMonCost').innerHTML=axisChart(months,[{label:'Project Cost',data:outSeries,color:'#3B82F6'}]);
}

/* ---------------- Purchases ---------------- */
function clearPurchFilters(){
  ['rptQ','rptFrom','rptFromH','rptTo','rptToH'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';if(id.endsWith('H')){}else{if(el&&el.dataset)el.dataset.parsedDb='';}});
  document.getElementById('rptCat').value='';document.getElementById('rptSup').value='';
  loadReport();
}
async function loadPurchRpt(pid){
  rptBar('purchBar','&#128722; Purchases',pid,'Material purchases with category breakdown and list');
  const q=document.getElementById('rptQ').value,from=document.getElementById('rptFromH').value,to=document.getElementById('rptToH').value;
  const cat=document.getElementById('rptCat').value,sup=document.getElementById('rptSup').value;
  let url=BASE_PATH+'/api/reports.php?action=project_purchases&project_id='+pid;
  if(q)url+='&q='+encodeURIComponent(q);if(from)url+='&from='+from;if(to)url+='&to='+to;if(cat)url+='&category='+encodeURIComponent(cat);if(sup)url+='&supplier='+encodeURIComponent(sup);
  const d=await getJSON(url);
  const content=document.getElementById('purchContent'),empty=document.getElementById('purchEmpty');
  if(!d){
    content.style.display='none';empty.style.display='none';
    document.getElementById('purchRptTable').innerHTML=loadErrorPanel(8,'Purchases could not be loaded.');document.getElementById('purchPager').innerHTML='';
    document.getElementById('purchKpis').innerHTML=loadErrorCard('x').replace('x could not be loaded','Purchases could not be loaded');
    return;
  }
  purchRpt=d; purchPage=1;
  const cats=[...new Set((d.data||[]).map(r=>r.cat_name||r.supply_category||'').filter(Boolean))].sort();
  const sups=[...new Set((d.data||[]).map(r=>r.supplier||'').filter(Boolean))].sort();
  fillSel('rptCat',cats.map(c=>({value:c,label:c})),'All Categories',true);
  fillSel('rptSup',sups.map(s=>({value:s,label:s})),'All Suppliers',true);
  if(!d.data||!d.data.length){
    content.style.display='none';empty.style.display='block';
    empty.innerHTML=rptEmptyState('&#128722;','No purchases recorded','No purchases found for <b>'+esc(projName(pid))+'</b>'+(q||from||to||cat||sup?' matching the current filters. Try clearing the filters.':' yet.')+' Add materials from the project <b>Purchases</b> tab or <b>Quick Purchase</b> — the category chart and full list will appear here.');
    return;
  }
  content.style.display='';empty.style.display='none';
  renderPurch();
}
function renderPurch(){
  const d=purchRpt;
  const kpi=[
    {label:'Purchase Orders',val:num(d.count),icon:'&#128722;',bg:'var(--stat-red-bg)'},
    {label:'Items Purchased',val:num(d.total_qty)+' qty',icon:'&#128230;',bg:'var(--stat-blue-bg)'},
    {label:'Total Amount',val:fmt(d.total),icon:'&#128181;',bg:'var(--stat-green-bg)'},
    {label:'Suppliers',val:num(d.suppliers||0),icon:'&#128230;',bg:'var(--stat-orange-bg)'},
  ];
  document.getElementById('purchKpis').innerHTML=kpi.map(c=>statCard(c.label,c.val,c.icon,c.bg)).join('');
  document.getElementById('purchRptTotal').textContent=fmt(d.total);
  const rows=(d.data||[]).slice().reverse();
  const per=15,total=rows.length,pages=Math.max(1,Math.ceil(total/per));
  if(purchPage>pages)purchPage=pages;
  const pageRows=rows.slice((purchPage-1)*per,purchPage*per);
  document.getElementById('purchRptTable').innerHTML=pageRows.map(p=>
    `<tr><td>${fmtDate(p.purchase_date)}</td><td><strong>${esc(p.item_name)}</strong></td><td><span class="badge badge-neutral">${esc(p.supply_category||'-')}</span></td><td>${esc(p.supplier||'-')}</td><td class="td-amount">${num(p.quantity)}</td><td>${esc(p.unit||'-')}</td><td class="td-amount">${fmt(p.rate)}</td><td class="td-amount text-danger">${fmt(p.total)}</td></tr>`).join('')
    +(pageRows.length?'':emptyRow(8,'No rows on this page.'));
  document.getElementById('purchPager').innerHTML=pagerHTML('purchPageGo',purchPage,pages,total);
  const cats=Object.entries(d.by_category||{}).sort((a,b)=>b[1]-a[1]);
  document.getElementById('purchCatChart').innerHTML=hBars(cats.map(([c,v])=>({label:c,value:v})),d.total,'blue');
  const sups=Object.entries(d.by_supplier||{}).sort((a,b)=>b[1]-a[1]);
  document.getElementById('purchSupChart').innerHTML=hBars(sups.map(([c,v])=>({label:c,value:v})),d.total,'orange');
  const months=Object.keys(d.monthly||{}).sort();
  document.getElementById('purchMonthChart').innerHTML=months.length?axisChart(months,[{label:'Purchases',data:d.monthly,color:'#3B82F6'}]):'<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
}
function purchPageGo(n){const total=(purchRpt&&purchRpt.data||[]).length;const pages=Math.max(1,Math.ceil(total/15));purchPage=Math.max(1,Math.min(n,pages));renderPurch();}
function pagerHTML(fn,page,pages,total){
  return `<button class="pg-btn" ${page<=1?'disabled':''} onclick="${fn}(${page-1})">&#8249;</button><span class="pg-info">Page ${page} of ${pages} &bull; ${total} rows</span><button class="pg-btn" ${page>=pages?'disabled':''} onclick="${fn}(${page+1})">&#8250;</button>`;
}

/* ---------------- Labor ---------------- */
function clearLaborFilters(){
  ['labQ','labFrom','labFromH','labTo','labToH'].forEach(id=>{const el=document.getElementById(id);if(el){el.value='';if(id.endsWith('H')){}else if(el.dataset)el.dataset.parsedDb='';}});
  document.getElementById('labTrade').value='';loadReport();
}
async function loadLaborRpt(pid){
  rptBar('laborBar','&#128168; Labor Wages',pid,'Attendance earned + labor payments for this project');
  const q=document.getElementById('labQ').value,from=document.getElementById('labFromH').value,to=document.getElementById('labToH').value;
  const trade=document.getElementById('labTrade').value;
  let url=BASE_PATH+'/api/reports.php?action=labor_report&project_id='+pid;
  if(q)url+='&q='+encodeURIComponent(q);if(from)url+='&from='+from;if(to)url+='&to='+to;if(trade)url+='&trade='+encodeURIComponent(trade);
  const d=await getJSON(url);
  const content=document.getElementById('laborContent'),empty=document.getElementById('laborEmpty');
  if(!d){content.style.display='';empty.style.display='none';return;}
  fillSel('labTrade',(d.trade_options||[]).filter(Boolean).map(t=>({value:t,label:t})),'All Trades',true);
  const k=d.kpis||{};
  const hasAny=Number(k.earned||0)>0||Number(k.paid||0)>0||(d.workers||[]).length>0;
  if(!hasAny){
    content.style.display='none';empty.style.display='block';
    empty.innerHTML=rptEmptyState('&#128168;','No labor records yet','No attendance or wage payments are recorded for <b>'+esc(projName(pid))+'</b>'+(q||from||to||trade?' matching the current filters.':' .')+' Mark daily attendance from the <b>Daily Labor</b> page — earned wages, payments and balances will be summarized here.');
    return;
  }
  content.style.display='';empty.style.display='none';
  document.getElementById('laborKpis').innerHTML=
    statCard('Labor Earned',fmt(k.earned),'&#128200;','var(--stat-blue-bg)')+
    statCard('Labor Paid',fmt(k.paid),'&#128722;','var(--stat-red-bg)')+
    statCard('Balance Due',fmt((k.earned||0)-(k.paid||0)),'&#9203;','var(--stat-orange-bg)')+
    statCard('Workers',num(k.workers||0),'&#128101;','var(--stat-purple-bg)')+
    statCard('Working Days',num(k.days||0),'&#128197;','var(--stat-green-bg)');
  const cats=d.categories||[];
  const catTot=cats.reduce((a,c)=>a+Number(c.earned||0),0);
  document.getElementById('laborCatTotal').textContent=fmt(k.earned);
  document.getElementById('laborCatChart').innerHTML=hBars(cats.map(c=>({label:(c.name||'Other')+' ('+num(c.workers)+' workers)',value:c.earned})),catTot,'blue');
  const wrows=d.workers||[];
  document.getElementById('laborSumBadge').textContent=fmt(k.earned);
  document.getElementById('laborSumTable').innerHTML=wrows.length?wrows.map(w=>{
    const due=Number(w.earned)-Number(w.paid);
    return `<tr><td><strong>${esc(w.worker_name||'-')}</strong></td><td>${esc(w.trade||'-')}</td><td>${esc(w.phone||'-')}</td><td class="td-amount">${fmt(w.default_daily_rate)}</td><td class="td-amount">${num(w.days)}</td><td class="td-amount">${fmt(w.earned)}</td><td class="td-amount">${fmt(w.paid)}</td><td class="td-amount" style="color:${due>0?'var(--danger)':'var(--success)'};">${fmt(due)}</td></tr>`;
  }).join(''):emptyRow(8,'No labor (attendance) records found for this project.');
  const months=(d.monthly||[]).map(m=>m.ym);
  const earnedBy={};(d.monthly||[]).forEach(m=>earnedBy[m.ym]=m.earned);
  document.getElementById('laborMonthChart').innerHTML=months.length?axisChart(months,[{label:'Labor Cost',data:earnedBy,color:'#F97316'}]):'<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
  const p=await getJSON(BASE_PATH+'/api/reports.php?action=labor_payments_report&project_id='+pid+'&'+(from?'from='+from+'&':'')+(to?'to='+to:''));
  if(p){
    document.getElementById('laborRptTotal').textContent=fmt(p.total||0);
    document.getElementById('laborRptTable').innerHTML=(p.data&&p.data.length)?p.data.map(pay=>`<tr><td><strong>${esc(pay.worker_name||'-')}</strong></td><td>${esc(pay.trade||'-')}</td><td class="td-amount">${fmt(pay.amount)}</td><td>${fmtDate(pay.payment_date)}</td></tr>`).join(''):emptyRow(4,'No labor payments found for this project.');
  } else {
    document.getElementById('laborRptTotal').textContent='Error';
    document.getElementById('laborRptTable').innerHTML=loadErrorPanel(4,'Labor payment history could not be loaded.');
  }
}

/* ---------------- Attendance ---------------- */
function pad2Rpt(n){return String(n).padStart(2,'0');}
function isoRpt(d){return d.getFullYear()+'-'+pad2Rpt(d.getMonth()+1)+'-'+pad2Rpt(d.getDate());}
function clearAttChipsRpt(){document.querySelectorAll('#attRptChips .filter-chip').forEach(b=>b.classList.remove('active'));}
function setAttRangeRpt(kind){
  const now=new Date();let from='',to='';
  if(kind==='today'){from=to=isoRpt(now);}
  else if(kind==='week'){const d=new Date(now),day=(d.getDay()+6)%7;d.setDate(d.getDate()-day);from=isoRpt(d);d.setDate(d.getDate()+6);to=isoRpt(d);}
  else if(kind==='month'){from=now.getFullYear()+'-'+pad2Rpt(now.getMonth()+1)+'-01';to=isoRpt(new Date(now.getFullYear(),now.getMonth()+1,0));}
  else if(kind==='lastmonth'){const lm=new Date(now.getFullYear(),now.getMonth()-1,1);from=lm.getFullYear()+'-'+pad2Rpt(lm.getMonth()+1)+'-01';to=isoRpt(new Date(now.getFullYear(),now.getMonth(),0));}
  document.querySelectorAll('#attRptChips .filter-chip').forEach(b=>b.classList.toggle('active',b.dataset.range===kind));
  const fv=document.getElementById('attRptFrom'),tv=document.getElementById('attRptTo');
  const fh=document.getElementById('attRptFromH'),th=document.getElementById('attRptToH');
  if(from){SmartDate.setDateValue(fv,from);fh.value=from;}else{fv.value='';fv.dataset.parsedDb='';fh.value='';}
  if(to){SmartDate.setDateValue(tv,to);th.value=to;}else{tv.value='';tv.dataset.parsedDb='';th.value='';}
  loadReport();
}
async function loadAttendanceRpt(pid){
  rptBar('attBar','&#128203; Attendance',pid,'Daily attendance / hajira for this project');
  const from=document.getElementById('attRptFromH').value,to=document.getElementById('attRptToH').value;
  const worker=document.getElementById('attWorker').value,trade=document.getElementById('attTrade').value,status=document.getElementById('attStatus').value;
  let url=BASE_PATH+'/api/reports.php?action=attendance_report&project_id='+pid;
  if(from)url+='&from='+from;if(to)url+='&to='+to;if(worker)url+='&worker_id='+worker;if(trade)url+='&trade='+encodeURIComponent(trade);if(status)url+='&status='+status;
  const d=await getJSON(url);
  const body=document.getElementById('attRptBody');
  const inr=document.getElementById('attRptTotal'),badge=document.getElementById('attRptTotalBadge');
  const wrap=document.getElementById('attTableWrap'),emp=document.getElementById('attTableEmpty');
  const statsBox=document.getElementById('attStats');
  if(!d){inr.textContent='Error';badge.textContent='Error';wrap.style.display='';emp.style.display='none';statsBox.style.display='none';body.innerHTML=loadErrorPanel(7,'Attendance records could not be loaded.');return;}
  fillSel('attWorker',(d.worker_options||[]).map(w=>({value:w.id,label:w.name})),'All Workers',true);
  fillSel('attTrade',(d.trade_options||[]).filter(Boolean).map(t=>({value:t,label:t})),'All Trades',true);
  const k=d.kpis||{};
  document.getElementById('attStats').style.display='';
  statsBox.innerHTML=
    statCard('Working Days',num(k.days),'&#128197;','var(--stat-blue-bg)')+
    statCard('Attendance Rate',num(k.rate)+'%','&#128200;','var(--stat-green-bg)')+
    statCard('Present',num(k.present),'&#9989;','var(--stat-green-bg)')+
    statCard('Half Day',num(k.half),'&#9200;','var(--stat-orange-bg)')+
    statCard('Leave / Off',num(k.leaves),'&#128683;','var(--stat-red-bg)')+
    statCard('Overtime',num(k.overtime)+' d','&#128161;','var(--stat-purple-bg)');
  if(!d.records||!d.records.length){
    wrap.style.display='none';emp.style.display='block';
    emp.innerHTML=rptEmptyState('&#128203;','No attendance records','No attendance found for <b>'+esc(projName(pid))+'</b>'+(from||to||worker||trade||status?' in the selected filters. Try adjusting them.':' yet.')+' Mark attendance from the <b>Daily Labor</b> page — each recorded day appears here with its earned amount.');
    inr.textContent='Tk. 0';badge.textContent='Tk. 0';
    document.getElementById('attDayChart').innerHTML='<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
    document.getElementById('attMonChart').innerHTML='<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
    document.getElementById('attStatusChart').innerHTML='<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
    return;
  }
  wrap.style.display='';emp.style.display='none';
  let total=0;
  const typeLabel={0:'Leave/Off',0.5:'Half Day',1:'Full Day',1.5:'Overtime',2:'Double'};
  body.innerHTML=d.records.map(a=>{total+=parseFloat(a.earned||0);const mult=parseFloat(a.attendance_multiplier);const lbl=typeLabel[mult]!==undefined?typeLabel[mult]:mult+'x';const cls=mult>=1.5?'badge-warning':(mult===0?'badge-danger':(mult===0.5?'badge-info':'badge-success'));return `<tr><td><strong>${esc(a.worker_name)}</strong></td><td>${esc(a.trade||'-')}</td><td><span class="badge ${cls}">${lbl}</span></td><td class="td-amount">${fmt(a.daily_rate)}</td><td class="td-amount text-success">${fmt(a.earned)}</td><td>${fmtDate(a.work_date)}</td><td><button class="btn btn-ghost btn-sm" title="Delete" onclick="deleteAttRpt(${a.id})">&#10006;</button></td></tr>`;}).join('');
  inr.textContent=fmt(total);badge.textContent=fmt(total);
  const days=(d.daily||[]).slice(-15);
  const daySeries={};days.forEach(x=>daySeries[x.d]=x.days);
  document.getElementById('attDayChart').innerHTML=days.length?axisChart(days.map(x=>x.d),[ {label:'Days',data:daySeries,color:'#3B82F6'} ]):'<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
  const mons=(d.monthly||[])||[];
  const monSeries={};mons.forEach(m=>monSeries[m.ym]={days:m.days,earned:m.earned});
  document.getElementById('attMonChart').innerHTML=mons.length?axisChart(mons.map(m=>m.ym),[  {label:'Days',data:Object.fromEntries(mons.map(m=>[m.ym,m.days])),color:'#10B981'} ]):'<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
  const recCount=d.records.length;
  document.getElementById('attStatusChart').innerHTML=hBars([
    {label:'Present (Full Day)',value:k.present},{label:'Half Day',value:k.half},{label:'Leave / Off',value:k.leaves},{label:'Overtime',value:k.overtime}
  ],recCount,'green');
}
async function deleteAttRpt(id){
  const pid=getPid();
  confirmDelete('Delete this attendance record?', async function(){
    const r=await fetch(BASE_PATH+'/api/attendance.php?action=delete_attendance&project_id='+pid,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
    const d=await r.json();
    if(d.success){showToast('Deleted','success');loadAttendanceRpt(pid);}
    else showToast((d&&d.message)||'Error','error');
  });
}

/* ---------------- Payments ---------------- */
async function loadPayRpt(pid){
  rptBar('payBar','&#128176; Payments',pid,'Client received, contractor advances, labor & expense payments');
  const content=document.getElementById('payContent'),empty=document.getElementById('payEmpty');
  const d=await getJSON(BASE_PATH+'/api/reports.php?action=payment_report&project_id='+pid);
  if(!d){content.style.display='';empty.style.display='none';return;}
  const t=d.totals||{};
  const hasAny=(t.received>0||t.paid_out>0);
  if(!hasAny){
    content.style.display='none';empty.style.display='block';
    empty.innerHTML=rptEmptyState('&#128176;','No payments recorded','No client receipts, contractor advances, labor payments or other expenses are recorded for <b>'+esc(projName(pid))+'</b>. Record client payments in <b>Payments</b>, contractor advances in the project <b>Billing</b> tab, worker wages in <b>Daily Labor</b>, and other costs under <b>Expense</b> — everything will be summarized here.');
    return;
  }
  content.style.display='';empty.style.display='none';
  document.getElementById('payStats').innerHTML=
    statCard('Client Received',fmt(t.received),'&#128176;','var(--stat-green-bg)')+
    statCard('Client Receivable',fmtNA(t.receivable),'&#9203;','var(--stat-orange-bg)')+
    statCard('Contractor Paid',fmt(t.adv_paid),'&#128184;','var(--stat-red-bg)')+
    statCard('Labor Paid',fmt(t.lab_paid),'&#128101;','var(--stat-purple-bg)')+
    statCard('Expenses Paid',fmt(t.exp_paid),'&#128722;','var(--stat-orange-bg)')+
    statCard('Net (Received - Paid)',fmt(t.net),'&#128181;',t.net>=0?'var(--stat-green-bg)':'var(--stat-red-bg)');
  const cl=d.client_payments||[];
  document.getElementById('clientRptTotal').textContent=fmt(t.received);
  document.getElementById('clientRptTable').innerHTML=cl.length?cl.map(p=>`<tr><td>${fmtDate(p.payment_date)}</td><td>${esc(p.payment_method||'-')}</td><td class="td-amount text-success">${fmt(p.amount)}</td><td>${esc(p.notes||'-')}</td></tr>`).join(''):emptyRow(4,'No client payments found.');
  const co=d.advances||[];
  document.getElementById('contractorRptTotal').textContent=fmt(t.adv_paid);
  document.getElementById('contractorRptTable').innerHTML=co.length?co.map(p=>`<tr><td><strong>${esc(p.contractor_name||'-')}</strong></td><td>${esc(p.trade||'-')}</td><td class="td-amount">${fmt(p.amount)}</td><td>${fmtDate(p.payment_date)}</td><td>${esc(p.payment_method||'-')}</td></tr>`).join(''):emptyRow(5,'No contractor payments found.');
  const la=d.labor_payments||[];
  document.getElementById('laborPayTotal').textContent=fmt(t.lab_paid);
  document.getElementById('laborPayTable').innerHTML=la.length?la.map(p=>`<tr><td><strong>${esc(p.worker_name||'-')}</strong></td><td>${esc(p.trade||'-')}</td><td class="td-amount">${fmt(p.amount)}</td><td>${fmtDate(p.payment_date)}</td></tr>`).join(''):emptyRow(4,'No labor payments found.');
  const ex=d.expenses||[];
  document.getElementById('expPayTotal').textContent=fmt(t.exp_paid);
  document.getElementById('expPayTable').innerHTML=ex.length?ex.map(p=>`<tr><td>${fmtDate(p.expense_date)}</td><td><span class="badge badge-neutral">${esc(p.category||'-')}</span></td><td>${esc(p.description||'-')}</td><td>${esc(p.vendor||'-')}</td><td class="td-amount">${fmt(p.amount)}</td><td>${esc(p.payment_method||'-')}</td></tr>`).join(''):emptyRow(6,'No expense payments found.');
  const months=Object.keys(Object.assign({},d.monthly_in,d.monthly_out)).sort();
  document.getElementById('payFlowChart').innerHTML=months.length?axisChart(months,[
    {label:'Received',data:d.monthly_in,color:'#10B981'},
    {label:'Paid Out',data:d.monthly_out,color:'#EF4444'}
  ]):'<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
  const hasB=d.project&&d.project.has_budget;
  document.getElementById('payRecChart').innerHTML=hasB
    ? hBars([
        {label:'Received',value:t.received},
        {label:'Receivable (due)',value:t.receivable||0}
      ],(t.received+(t.receivable||0)),'green')
    : '<p class="na" style="font-size:13px;">Contract value not set — receivable can\'t be computed. Set an Estimated Budget on the project.</p>';
}

/* ---------------- Expense ---------------- */
function clearExpFilters(){
  ['expQ','expFrom','expFromH','expTo','expToH'].forEach(id=>{const el=document.getElementById(id);if(el){el.value='';if(id.endsWith('H')){}else if(el.dataset)el.dataset.parsedDb='';}});
  document.getElementById('expCat').value='';document.getElementById('expStatus').value='';document.getElementById('expMethod').value='';
  loadReport();
}
async function loadExpenseRpt(pid){
  rptBar('expBar','&#128176; Expenses',pid,'Other project expenses with paid / due tracking');
  const q=document.getElementById('expQ').value,from=document.getElementById('expFromH').value,to=document.getElementById('expToH').value;
  const cat=document.getElementById('expCat').value,status=document.getElementById('expStatus').value,method=document.getElementById('expMethod').value;
  let url=BASE_PATH+'/api/expenses.php?action=list&project_id='+pid;
  if(q)url+='&q='+encodeURIComponent(q);if(from)url+='&from='+from;if(to)url+='&to='+to;if(cat)url+='&category='+encodeURIComponent(cat);if(status)url+='&status='+status;if(method)url+='&method='+encodeURIComponent(method);
  const d=await getJSON(url);
  const content=document.getElementById('expContent'),empty=document.getElementById('expEmpty');
  if(!d){content.style.display='';empty.style.display='none';document.getElementById('expRptTable').innerHTML=loadErrorPanel(10,'Expenses could not be loaded.');document.getElementById('expPager').innerHTML='';return;}
  expRpt=d;expPage=1;
  const cats=[...(new Set((d.data||[]).map(r=>r.category||'').filter(Boolean)))].sort();
  fillSel('expCat',cats.map(c=>({value:c,label:c})),'All Categories',true);
  if(!d.data||!d.data.length){
    content.style.display='none';empty.style.display='block';
    empty.innerHTML=rptEmptyState('&#128176;','No expenses recorded','No expenses found for <b>'+esc(projName(pid))+'</b>'+(q||from||to||cat||status||method?' matching the current filters.':' yet.')+' Use the <b>Add Expense</b> button to record transport, utilities, equipment or other project costs — they will appear here and feed into the Financial tab.');
    return;
  }
  content.style.display='';empty.style.display='none';
  renderExp();
}
function expStatusBadge(r){
  const paid=Number(r.paid),amount=Number(r.amount);
  if(paid>=amount&&amount>0)return '<span class="badge badge-success">Paid</span>';
  if(paid>0)return '<span class="badge badge-warning">Partial</span>';
  return '<span class="badge badge-danger">Unpaid</span>';
}
function renderExp(){
  const d=expRpt,t=d.totals||{};
  const topCat=(d.by_category||[])[0];
  document.getElementById('expKpis').innerHTML=
    statCard('Total Expenses',fmt(t.total),'&#128176;','var(--stat-red-bg)')+
    statCard('Paid',fmt(t.paid),'&#9989;','var(--stat-green-bg)')+
    statCard('Due',fmt(t.due),'&#9203;','var(--stat-orange-bg)')+
    statCard('Categories',num(t.categories||0),'&#128230;','var(--stat-blue-bg)')+
    statCard('Top Category',topCat?esc(topCat.name):'N/A','&#128200;','var(--stat-purple-bg)');
  document.getElementById('expRptTotal').textContent=fmt(t.total);
  const rows=(d.data||[]).slice();
  const per=15,total=rows.length,pages=Math.max(1,Math.ceil(total/per));
  if(expPage>pages)expPage=pages;
  const pageRows=rows.slice((expPage-1)*per,expPage*per);
  document.getElementById('expRptTable').innerHTML=pageRows.map(r=>
    `<tr><td>${fmtDate(r.expense_date)}</td><td><span class="badge badge-neutral">${esc(r.category||'-')}</span></td><td>${esc(r.description||'-')}</td><td>${esc(r.vendor||'-')}</td><td class="td-amount">${fmt(r.amount)}</td><td class="td-amount text-success">${fmt(r.paid)}</td><td class="td-amount text-danger">${fmt(Number(r.amount)-Number(r.paid))}</td><td>${esc(r.payment_method||'-')}</td><td>${expStatusBadge(r)}</td><td style="white-space:nowrap;"><button class="btn btn-ghost btn-sm" title="Edit" onclick="openExpModal(${r.id})">&#9998;</button> <button class="btn btn-ghost btn-sm" title="Delete" onclick="deleteExpense(${r.id})">&#10006;</button></td></tr>`).join('')
    +(pageRows.length?'':emptyRow(10,'No rows on this page.'));
  document.getElementById('expPager').innerHTML=pagerHTML('expPageGo',expPage,pages,total);
  document.getElementById('expCatChart').innerHTML=hBars((d.by_category||[]).map(c=>({label:c.name,value:c.total})),t.total,'blue');
  const paid=Number(t.paid||0),due=Number(t.due||0);
  document.getElementById('expPayChart').innerHTML=hBars([
    {label:'Paid',value:paid},{label:'Due',value:due}
  ],(paid+due),'green');
  const months=(d.monthly||[]).map(m=>m.ym);
  const mseries={};(d.monthly||[]).forEach(m=>mseries[m.ym]=m.total);
  document.getElementById('expMonthChart').innerHTML=months.length?axisChart(months,[{label:'Expenses',data:mseries,color:'#F97316'}]):'<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
}
function expPageGo(n){const total=(expRpt&&expRpt.data||[]).length;const pages=Math.max(1,Math.ceil(total/15));expPage=Math.max(1,Math.min(n,pages));renderExp();}
function expEditRow(id){return (expRpt&&expRpt.data||[]).find(r=>String(r.id)===String(id))||null;}
function openExpModal(id){
  const pid=getPid();if(!pid){showToast('Select a project first.','error');return;}
  const editRow=id?expEditRow(id):null;
  document.getElementById('expModalTitle').textContent=editRow?'Edit Expense':'Add Expense';
  document.getElementById('expEditId').value=editRow?editRow.id:'';
  document.getElementById('expCatIn').value=editRow?(editRow.category||''):'';
  document.getElementById('expDesc').value=editRow?(editRow.description||''):'';
  document.getElementById('expVendor').value=editRow?(editRow.vendor||''):'';
  document.getElementById('expAmount').value=editRow?editRow.amount:'';
  document.getElementById('expPaid').value=editRow?editRow.paid:'';
  document.getElementById('expMethodIn').value=editRow?(editRow.payment_method||'Cash'):'Cash';
  document.getElementById('expNotes').value=editRow?(editRow.notes||''):'';
  const d=editRow?(editRow.expense_date||''):isoRpt(new Date());
  document.getElementById('expDateH').value=d;
  if(SmartDate.setDateValue)SmartDate.setDateValue(document.getElementById('expDate'),d);
  else document.getElementById('expDate').value=fmtDate(d);
  openModal('addExpModal');
}
async function saveExpense(){
  const pid=getPid();
  const id=document.getElementById('expEditId').value;
  const amount=parseFloat(document.getElementById('expAmount').value||0);
  const cat=document.getElementById('expCatIn').value.trim();
  if(!amount||amount<=0){showToast('Enter a valid amount.','error');return;}
  if(!cat){showToast('Enter a category.','error');return;}
  let paid=parseFloat(document.getElementById('expPaid').value||'');
  if(isNaN(paid))paid=amount;
  const body=new URLSearchParams({project_id:pid,amount:amount,paid:paid,category:cat,
    description:document.getElementById('expDesc').value,vendor:document.getElementById('expVendor').value,
    payment_method:document.getElementById('expMethodIn').value,expense_date:document.getElementById('expDateH').value,
    notes:document.getElementById('expNotes').value});
  if(id)body.set('id',id);
  const r=await fetch(BASE_PATH+'/api/expenses.php?action='+(id?'update':'create'),{method:'POST',body});
  const d=await r.json().catch(()=>({success:false,message:'Bad response'}));
  if(d.success){showToast(d.message||'Saved','success');closeModal('addExpModal');loadExpenseRpt(pid);}
  else showToast(d.message||'Error','error');
}
function deleteExpense(id){
  const pid=getPid();
  confirmDelete('Delete this expense record?', async function(){
    const r=await fetch(BASE_PATH+'/api/expenses.php?action=delete&project_id='+pid,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
    const d=await r.json();
    if(d.success){showToast('Expense deleted','success');loadExpenseRpt(pid);}
    else showToast((d&&d.message)||'Error','error');
  });
}

/* ---------------- Financial ---------------- */
async function loadFinancialRpt(pid){
  rptBar('finBar','&#128200; Financial Overview',pid,'Revenue, costs, budget vs actual and financial health');
  const content=document.getElementById('finContent');
  const d=await getJSON(BASE_PATH+'/api/reports.php?action=financial_report&project_id='+pid);
  if(!d){content.innerHTML=loadErrorCard('Financial overview');return;}
  const rev=d.revenue,c=d.costs,pl=d.profit,h=d.health;
  const hasB=rev.has_budget;
  document.getElementById('finRev').innerHTML='<div class="quick-summary">'+
    sumRow('Contract Value',hasB?fmt(rev.contract):'<span class="na">Not set</span>')+
    sumRow('Total Received',fmt(rev.received),'green')+
    sumRow('Receivable',fmtNA(rev.receivable))+
    '</div>';
  const plCls=pl.net>=0?'green':'red';
  document.getElementById('finPL').innerHTML='<div style="display:flex;flex-direction:column;gap:10px;">'+
    '<div><div style="font-size:28px;font-weight:800;color:'+(pl.net>=0?'var(--success)':'var(--danger)')+';">'+(pl.net>=0?'+':'')+fmt(pl.net)+'</div><div style="font-size:12px;color:#9CA3AF;">Net Profit / Loss (received minus spent)</div></div>'+
    '<div style="display:flex;gap:12px;flex-wrap:wrap;"><span class="badge badge-neutral">Margin: '+(pl.margin===null?'N/A':pl.margin+'%')+'</span><span class="badge badge-neutral">Profit on received</span></div>'+
    '</div>';
  document.getElementById('finCost').innerHTML='<div class="quick-summary">'+
    sumRow('Material Purchases',fmt(c.purchases),'red')+
    sumRow('Contractor Paid',fmt(c.contractor_paid),'red')+
    sumRow('Contractor Billed',fmt(c.contractor_billed),'')+
    sumRow('Labor Paid',fmt(c.labor_paid),'red')+
    sumRow('Labor Earned',fmt(c.labor_earned),'')+
    sumRow('Other Expenses',fmt(c.expenses),'red')+
    sumRow('<strong>Total Spent (cash)</strong>',fmt(c.total_cost),'red')+
    sumRow('Committed (incl. billed+earned)',fmt(c.total_committed),'')+
    '</div>';
  document.getElementById('finBudgetTable').innerHTML=
    '<tr><td>Material Purchases</td><td class="td-amount na">--</td><td class="td-amount">'+fmt(c.purchases)+'</td><td class="td-amount na">--</td></tr>'+
    '<tr><td>Contractor Payments</td><td class="td-amount na">--</td><td class="td-amount">'+fmt(c.contractor_paid)+'</td><td class="td-amount na">--</td></tr>'+
    '<tr><td>Labor (paid)</td><td class="td-amount na">--</td><td class="td-amount">'+fmt(c.labor_paid)+'</td><td class="td-amount na">--</td></tr>'+
    '<tr><td>Other Expenses</td><td class="td-amount na">--</td><td class="td-amount">'+fmt(c.expenses)+'</td><td class="td-amount na">--</td></tr>'+
    '<tr style="font-weight:700;background:var(--border-light);"><td>Total</td><td class="td-amount">'+(hasB?fmt(rev.contract):'<span class="na">--</span>')+'</td><td class="td-amount">'+fmt(c.total_cost)+'</td><td class="td-amount">'+(hasB?fmt(rev.contract-c.total_cost):'<span class="na">--</span>')+'</td></tr>';
  const pct=d.budget_pct;
  if(hasB&&pct!==null){
    const over=c.total_cost>rev.contract;
    document.getElementById('finBudgetBar').innerHTML='<div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;"><span>Spend vs Contract</span><span style="font-weight:700;">'+pct+'%</span></div><div class="progress-bar-bg"><div class="progress-bar-fill '+(over?'red':'green')+'" style="width:'+Math.min(100,pct)+'%"></div></div>';
  } else {
    document.getElementById('finBudgetBar').innerHTML='<p class="na" style="font-size:13px;">Contract value not set — budget comparison unavailable.</p>';
  }
  const toneIcon={success:'&#9989;',warn:'&#9888;&#65039;',danger:'&#128680;',no_budget:'&#128197;'};
  document.getElementById('finHealth').innerHTML='<div class="health-card tone-'+h.tone+'"><div class="hc-icon">'+(toneIcon[h.status]||'&#128200;')+'</div><div><div class="hc-label">'+esc(h.label)+'</div><div class="hc-detail">'+esc(h.detail)+'</div></div></div>';
  const months=d.months||[];
  const totCost={};months.forEach(m=>{totCost[m]=(Number(d.monthly.purchases[m]||0)+Number(d.monthly.advances[m]||0)+Number(d.monthly.labor[m]||0)+Number(d.monthly.expenses[m]||0));});
  document.getElementById('finCostChart').innerHTML=hBars([
    {label:'Material Purchases',value:c.purchases},
    {label:'Contractor Paid',value:c.contractor_paid},
    {label:'Labor Paid',value:c.labor_paid},
    {label:'Other Expenses',value:c.expenses}
  ],c.total_cost||1,'blue');
  document.getElementById('finFlowChart').innerHTML=months.length?axisChart(months,[
    {label:'Cash In',data:d.monthly.in,color:'#10B981'},
    {label:'Cash Out',data:totCost,color:'#EF4444'}
  ]):'<p style="color:#9CA3AF;font-size:13px;font-style:italic;">No data.</p>';
}

/* ---------------- init ---------------- */
document.addEventListener('DOMContentLoaded',function(){SmartDate.initAll();loadReport();});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>