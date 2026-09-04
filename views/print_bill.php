<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'Final Bill Print';
$activeNav = 'print-bill';

// Fetch only ongoing projects (or all non-deleted projects)
$projects = $pdo->query("SELECT p.*, (SELECT image_path FROM app_project_images pi WHERE pi.project_id=p.id AND pi.is_primary=1 LIMIT 1) as primary_image FROM app_projects p WHERE p.is_deleted=0 AND (p.status='Ongoing' OR p.status='ongoing') ORDER BY p.name ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="print-bill-page">

  <!-- TOP HEADER CARD -->
  <div class="fb-header-card">
    <div class="fb-header-left">
      <div class="fb-header-icon">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      </div>
      <div>
        <h2 class="fb-header-title">Final Bill Print</h2>
        <p class="fb-header-sub">Generate and print final bill for completed projects.</p>
      </div>
    </div>
    <div class="fb-header-right">
      <svg class="fb-printer-svg" viewBox="0 0 64 64" fill="none">
        <rect x="14" y="8" width="36" height="20" rx="3" fill="#E2E8F0" stroke="#94A3B8" stroke-width="2.5"/>
        <line x1="20" y1="14" x2="44" y2="14" stroke="#94A3B8" stroke-width="2" stroke-linecap="round"/>
        <line x1="20" y1="19" x2="36" y2="19" stroke="#94A3B8" stroke-width="2" stroke-linecap="round"/>
        <rect x="8" y="24" width="48" height="26" rx="6" fill="#1E293B" stroke="#0F172A" stroke-width="2.5"/>
        <circle cx="48" cy="31" r="2.5" fill="#22C55E"/>
        <rect x="16" y="38" width="32" height="18" rx="3" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
        <line x1="22" y1="44" x2="42" y2="44" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round"/>
        <line x1="22" y1="48" x2="34" y2="48" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
    </div>
  </div>

  <!-- NOTICE BANNER -->
  <div class="fb-notice-banner">
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <span>Only ongoing projects are listed below.</span>
  </div>

  <!-- SECTION HEADER -->
  <div class="fb-section-header">
    <div class="fb-section-title-wrap">
      <div class="fb-section-icon">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      </div>
      <h3 class="fb-section-title">Ongoing Projects</h3>
      <span class="fb-badge-count"><?= count($projects) ?></span>
    </div>
    <button class="fb-refresh-btn" onclick="location.reload()" title="Refresh List">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
    </button>
  </div>

  <!-- PROJECT LIST -->
  <?php if(empty($projects)): ?>
    <div class="card" style="padding:40px; text-align:center; color:var(--text-muted); border-radius:18px;">
      <p>No ongoing projects found.</p>
    </div>
  <?php else: ?>
    <?php foreach($projects as $p): 
      $img = !empty($p['primary_image']) ? $p['primary_image'] : (!empty($p['project_image']) ? $p['project_image'] : '');
      $loc = !empty($p['address']) ? $p['address'] : (!empty($p['client_name']) ? $p['client_name'] : 'Dhaka');
    ?>
      <div class="fb-project-card">
        <div class="fb-project-top">
          <?php if($img): ?>
            <img src="<?= $basePath ?>/<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="fb-project-img" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'84\' height=\'84\' fill=\'%23e2e8f0\'><rect width=\'100%\' height=\'100%'/></svg>'">
          <?php else: ?>
            <div class="fb-project-img" style="display:flex;align-items:center;justify-content:center;color:var(--text-light);font-size:28px;">
              &#127968;
            </div>
          <?php endif; ?>

          <div class="fb-project-info">
            <h4 class="fb-project-name"><?= htmlspecialchars($p['name']) ?></h4>
            <div class="fb-project-loc">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <span><?= htmlspecialchars($loc) ?></span>
            </div>
            <span class="fb-status-pill"><?= htmlspecialchars($p['status']) ?></span>
          </div>

          <a href="<?= $basePath ?>/project-detail?id=<?= $p['id'] ?>" class="fb-dots-btn" title="View Project Details">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
          </a>
        </div>

        <div class="fb-details-box">
          <div class="fb-detail-item">
            <span class="fb-detail-label">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              CLIENT
            </span>
            <span class="fb-detail-val"><?= htmlspecialchars($p['client_name'] ?: '-') ?></span>
          </div>
          <div class="fb-detail-item">
            <span class="fb-detail-label">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
              STATUS
            </span>
            <span class="fb-detail-val"><?= htmlspecialchars($p['status']) ?></span>
          </div>
        </div>

        <button class="fb-print-btn" onclick="openFinalBillForProject(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['name'])) ?>')">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          Print Final Bill
        </button>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- BOTTOM NOTE -->
  <div class="fb-note-card">
    <div class="fb-note-icon">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>
    <div>
      <h5 class="fb-note-title">Note</h5>
      <p class="fb-note-sub">Make sure all project dues are cleared before generating the final bill.</p>
    </div>
  </div>

</div>

<!-- GENERATE FINAL BILL MODAL -->
<div class="modal-overlay" id="generateFinalBillModal">
  <div class="modal modal-lg" data-form-nav>
    <div class="modal-header">
      <h3 id="fbModalTitle">&#128247; Generate Final Bill</h3>
      <div class="modal-close" onclick="closeModal('generateFinalBillModal')">&times;</div>
    </div>
    <div class="modal-body">
      <form id="finalBillForm" action="<?= $basePath ?>/print_custom_bill" method="POST" target="_blank">
        <input type="hidden" name="project_id" id="fbProjectId" value="">
        <input type="hidden" name="items_json" id="fbItemsJson" value="[]">
        
        <div class="two-col">
          <div class="form-group">
            <label class="form-label">Bill Type</label>
            <select id="fbType" name="bill_type" class="form-select" onchange="loadFbTargets()">
              <option value="contractor">Contractor Bill</option>
              <option value="labor">Labor / Worker Bill</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Select Person <span class="required">*</span></label>
            <select id="fbTarget" name="target_id" class="form-select" onchange="fetchFinalBillData(this.value)" required>
              <option value="">-- Select --</option>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Bill Date</label>
          <input type="text" id="fbDate" class="form-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="fbDateH">
          <input type="hidden" id="fbDateH" name="bill_date" value="<?=date('Y-m-d')?>">
        </div>
        
        <div style="margin:16px 0 8px 0; display:flex; justify-content:space-between; align-items:center;">
          <label class="form-label" style="margin:0; font-weight:700;">Bill Items</label>
          <button type="button" class="btn btn-secondary btn-sm" onclick="addFbRow()">+ Add Row</button>
        </div>

        <div id="fbItemsContainer" style="max-height:280px; overflow-y:auto; padding-right:4px;">
          <!-- Dynamic item rows will be placed here -->
        </div>

        <div style="display:flex; gap:10px; margin:12px 0 16px 0;">
          <button type="button" class="btn btn-outline btn-sm" onclick="groupSelectedRows()">&#128279; Group Selected Rows</button>
        </div>

        <div style="background:var(--warning-bg); padding:14px 18px; border-radius:var(--radius-md); display:flex; justify-content:space-between; align-items:center;">
          <span style="font-weight:700;">Grand Total</span>
          <span id="fbGrandTotal" style="font-family:'Poppins','Noto Sans Bengali',sans-serif; font-weight:800; font-size:20px; color:#B45309;">Tk. 0</span>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('generateFinalBillModal')">Cancel</button>
      <button class="btn btn-primary" onclick="generateAndPrintFb()">Generate & Print</button>
    </div>
  </div>
</div>

<script>
let currentProjectId = 0;

function esc(s) {
  return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function fmt(n) {
  return 'Tk. ' + parseFloat(n || 0).toLocaleString('en-BD', { maximumFractionDigits: 0 });
}

async function openFinalBillForProject(projectId, projectName) {
  currentProjectId = projectId;
  document.getElementById('fbProjectId').value = projectId;
  document.getElementById('fbModalTitle').innerHTML = '&#128247; Final Bill &mdash; ' + esc(projectName);
  document.getElementById('fbType').value = 'contractor';
  document.getElementById('fbItemsContainer').innerHTML = '';
  addFbRow();
  await loadFbTargets();
  openModal('generateFinalBillModal');
}

async function loadFbTargets() {
  const type = document.getElementById('fbType').value;
  const targetSel = document.getElementById('fbTarget');
  targetSel.innerHTML = '<option value="">-- Loading... --</option>';
  
  try {
    if (type === 'contractor') {
      const r = await fetch(BASE_PATH + '/api/billing.php?action=list_project_contractors&project_id=' + currentProjectId);
      const d = await r.json();
      let list = d.data || [];
      if (!list.length) {
        const r2 = await fetch(BASE_PATH + '/api/billing.php?action=get_all_contractors');
        const d2 = await r2.json();
        list = d2.data || [];
      }
      targetSel.innerHTML = '<option value="">-- Select Contractor --</option>' + list.map(c => `<option value="${c.contractor_id || c.id}">${esc(c.name)} (${esc(c.trade || 'Contractor')})</option>`).join('');
    } else {
      const r = await fetch(BASE_PATH + '/api/workers.php?action=list');
      const d = await r.json();
      const list = d.data || [];
      targetSel.innerHTML = '<option value="">-- Select Worker --</option>' + list.map(w => `<option value="${w.id}">${esc(w.name)} (${esc(w.trade || 'Worker')})</option>`).join('');
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
      const r = await fetch(BASE_PATH + '/api/billing.php?action=get_category_summary&project_id=' + currentProjectId + '&contractor_id=' + targetId);
      const d = await r.json();
      if (d.success) {
        if (d.data && d.data.length > 0) {
          d.data.forEach(item => {
            addFbRow(item.item_name || item.description, item.total_qty || item.qty || 1, item.rate || '');
            hasItems = true;
          });
        }
        if (d.attendance && d.attendance.length > 0) {
          d.attendance.forEach(att => {
            const role = att.person_type === 'contractor' ? 'Contractor Work' : 'Crew Labor';
            const desc = `${att.name} (${role}) — ${att.days} days @ Tk.${att.rate}`;
            addFbRow(desc, att.days, att.rate || '');
            hasItems = true;
          });
        }
      }
    } catch(e) {}
  } else {
    try {
      const r = await fetch(BASE_PATH + '/api/billing.php?action=get_worker_bill_data&project_id=' + currentProjectId + '&worker_id=' + targetId);
      const d = await r.json();
      if (d.success && d.items && d.items.length > 0) {
        d.items.forEach(item => {
          addFbRow(item.description, item.qty, item.rate);
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

function addFbRow(desc = '', qty = '', rate = '') {
  const container = document.getElementById('fbItemsContainer');
  const div = document.createElement('div');
  div.className = 'bill-item-row three-col';
  div.style.cssText = 'gap:8px; margin-bottom:8px; align-items:center;';
  div.innerHTML = `
    <div style="display:flex; align-items:center; gap:8px;">
      <input type="checkbox" class="row-selector" style="width:18px;height:18px;cursor:pointer;" title="Select to group">
      <input type="text" class="form-input bill-desc" placeholder="Description / Item name" value="${esc(desc)}" style="flex:1;">
    </div>
    <input type="number" class="form-input bill-qty" placeholder="Qty" value="${qty}" step="any" oninput="calcFbTotal()">
    <div style="display:flex; gap:6px; align-items:center;">
      <input type="number" class="form-input bill-rate" placeholder="Rate" value="${rate}" step="any" oninput="calcFbTotal()" style="flex:1;">
      <button type="button" class="btn btn-ghost btn-sm btn-icon" onclick="this.closest('.bill-item-row').remove();calcFbTotal();" style="color:var(--danger);">&#10006;</button>
    </div>
  `;
  container.appendChild(div);
  calcFbTotal();
}

function calcFbTotal() {
  let grand = 0;
  const rows = document.querySelectorAll('#fbItemsContainer .bill-item-row');
  rows.forEach(r => {
    const qty = parseFloat(r.querySelector('.bill-qty').value) || 0;
    const rate = parseFloat(r.querySelector('.bill-rate').value) || 0;
    grand += (qty * rate);
  });
  document.getElementById('fbGrandTotal').textContent = fmt(grand);
}

function groupSelectedRows() {
  const checkedRows = Array.from(document.querySelectorAll('#fbItemsContainer .row-selector:checked')).map(cb => cb.closest('.bill-item-row'));
  if (checkedRows.length < 2) {
    showToast('Please select at least 2 rows to group', 'warning');
    return;
  }
  let totalQty = 0;
  let descs = [];
  checkedRows.forEach(r => {
    const desc = r.querySelector('.bill-desc').value.trim();
    if (desc) descs.push(desc);
    totalQty += (parseFloat(r.querySelector('.bill-qty').value) || 0);
    r.remove();
  });
  addFbRow(descs.join(', '), totalQty || 1, '');
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
    const qty = parseFloat(r.querySelector('.bill-qty').value) || 0;
    const rate = parseFloat(r.querySelector('.bill-rate').value) || 0;
    if (desc || qty > 0) {
      items.push({
        description: desc,
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

document.addEventListener('DOMContentLoaded', function(){
  SmartDate.initAll();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
