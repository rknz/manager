<?php
// views/quick-purchase.php — Fast purchase entry
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'Quick Purchase';
$activeNav = 'quick-purchase';
// Load projects for dropdown
$projects = $pdo->query("SELECT id, name FROM app_projects WHERE is_deleted=0 AND status='Ongoing' ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id, name FROM app_categories ORDER BY sort_order, name")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="qp-container">
  <!-- ENTRY FORM -->
  <div class="qp-card">
    <div class="qp-header">
      <h3>
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#E11D48" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        New Purchase Entry
      </h3>
      <span class="qp-shortcut-badge">Enter = Next &bull; Ctrl+Enter = Save</span>
    </div>

    <div data-form-nav>
      <!-- Project & Date -->
      <div class="qp-grid-2">
        <div class="qp-form-group">
          <label class="qp-form-label">Project <span class="required">*</span></label>
          <select id="qpProject" class="qp-select">
            <option value="">-- Select Project --</option>
            <?php foreach($projects as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="qp-form-group">
          <label class="qp-form-label">Purchase Date</label>
          <input type="text" id="qpDate" class="qp-input smart-date" placeholder="e.g. <?= date('j/n/y') ?>" data-date-target="qpDateHidden">
          <input type="hidden" id="qpDateHidden" value="<?= date('Y-m-d') ?>">
        </div>
      </div>

      <!-- Category & Item -->
      <div class="qp-grid-2">
        <div class="qp-form-group">
          <label class="qp-form-label">Category</label>
          <select id="qpCategory" class="qp-select">
            <option value="">-- Category --</option>
            <?php if(!empty($categories)): foreach($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; else: ?>
            <option>Board & Wood</option><option>Paint</option><option>Hardware</option>
            <option>Glass</option><option>Electric</option><option>Labour</option><option>Other</option>
            <?php endif; ?>
          </select>
        </div>
        <div class="qp-form-group">
          <label class="qp-form-label" id="qpItemLabel">Item Name <span class="required">*</span></label>
          <input type="text" id="qpItem" class="qp-input" placeholder="e.g. Item Name" list="itemSuggestions" autocomplete="off">
          <datalist id="itemSuggestions"></datalist>
          <datalist id="boardSuggestions">
            <option>Melamine</option>
            <option>Partex</option>
            <option>PVC</option>
            <option>Gorjon</option>
            <option>MDF</option>
            <option>Ply</option>
            <option>Plex</option>
            <option>HPL</option>
          </datalist>
        </div>
      </div>

      <!-- Conditional Board Thickness Field -->
      <div id="boardFields" style="display:none; margin-bottom:12px;">
        <div class="qp-form-group">
          <label class="qp-form-label">Board Thickness (mm) <span class="required">*</span></label>
          <input type="text" id="qpThickness" class="qp-input" placeholder="e.g. 12mm" list="thicknessSuggestions" onblur="if(this.value && isFinite(this.value)) this.value += 'mm'">
          <datalist id="thicknessSuggestions">
            <option>6mm</option>
            <option>8mm</option>
            <option>9mm</option>
            <option>10mm</option>
            <option>12mm</option>
            <option>18mm</option>
            <option>25mm</option>
          </datalist>
        </div>
      </div>

      <!-- Qty & Rate -->
      <div class="qp-grid-3">
        <div class="qp-form-group">
          <label class="qp-form-label" id="qpQtyLabel">Quantity <span class="required">*</span></label>
          <input type="number" id="qpQty" class="qp-input" placeholder="0" step="1" min="1" inputmode="numeric" oninput="integerOnly(this); calcTotal()">
        </div>
        <div class="qp-form-group" id="qpUnitGroup">
          <label class="qp-form-label">Unit</label>
          <input type="text" id="qpUnit" class="qp-input" placeholder="pcs" list="unitList">
          <datalist id="unitList"><option>pcs</option><option>sft</option><option>rft</option><option>kg</option><option>ltr</option><option>set</option><option>bag</option></datalist>
        </div>
        <div class="qp-form-group">
          <label class="qp-form-label">Rate (Tk) <span class="required">*</span></label>
          <input type="number" id="qpRate" class="qp-input" placeholder="0" step="1" min="1" inputmode="numeric" oninput="integerOnly(this); calcTotal()">
        </div>
      </div>

      <!-- Supplier -->
      <div class="qp-form-group">
        <label class="qp-form-label">Supplier</label>
        <input type="text" id="qpSupplier" class="qp-input" placeholder="Supplier name (optional)" list="supplierSuggestions">
        <datalist id="supplierSuggestions"></datalist>
      </div>

      <!-- Total display -->
      <div class="qp-total-box">
        <span class="qp-total-label">Total Amount</span>
        <span id="qpTotalDisplay" class="qp-total-val">Tk. 0</span>
      </div>

      <!-- Action buttons -->
      <div class="qp-btn-row">
        <button type="button" class="btn btn-outline qp-action-btn" onclick="savePurchase(false)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add More
        </button>
        <button type="button" class="btn btn-primary qp-action-btn" onclick="savePurchase(true)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Save Purchase
        </button>
      </div>
    </div>
  </div>

  <!-- RIGHT: TODAY ENTRIES -->
  <div class="qp-entries-card">
    <div class="qp-entries-header">
      <h3 class="qp-entries-title">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Today's Entries
      </h3>
      <span id="todayTotalBadge" class="qp-entries-badge">Tk. 0</span>
    </div>
    <div style="max-height:560px;overflow-y:auto;">
      <div id="todayPurchaseList" style="padding:0;">
        <div class="loading-state"><div class="spinner"></div></div>
      </div>
    </div>
  </div>
</div>

<script>
var TODAY = '<?= date('Y-m-d') ?>';// Category change -> show/hide board fields
document.getElementById('qpCategory').addEventListener('change', function() {
  var isBoard = this.value === 'Board' || this.value === 'Board & Wood' || /board/i.test(this.value);
  var itemLabel = document.getElementById('qpItemLabel');
  var itemInput = document.getElementById('qpItem');
  var boardFields = document.getElementById('boardFields');
  var unitGroup = document.getElementById('qpUnitGroup');
  var unitInput = document.getElementById('qpUnit');
  var qtyLabel = document.getElementById('qpQtyLabel');

  if (isBoard) {
    if (itemLabel) itemLabel.innerHTML = 'Board Type <span class="required">*</span>';
    if (itemInput) {
      itemInput.placeholder = 'e.g. Melamine, Partex, PVC, Gorjon';
      itemInput.setAttribute('list', 'boardSuggestions');
    }
    if (boardFields) boardFields.style.display = 'block';
    if (unitGroup) unitGroup.style.display = 'none';
    if (unitInput) unitInput.value = 'pcs';
    if (qtyLabel) qtyLabel.innerHTML = 'Quantity (pcs) <span class="required">*</span>';
  } else {
    if (itemLabel) itemLabel.innerHTML = 'Item Name <span class="required">*</span>';
    if (itemInput) {
      itemInput.placeholder = 'e.g. Berger Paint, UPVC Pipe';
      itemInput.setAttribute('list', 'itemSuggestions');
    }
    if (boardFields) boardFields.style.display = 'none';
    if (unitGroup) unitGroup.style.display = 'block';
    if (qtyLabel) qtyLabel.innerHTML = 'Quantity <span class="required">*</span>';
  }
});

function integerOnly(el) {
  el.value = el.value.replace(/[^0-9]/g, '').replace(/^0+(?=\d)/, '');
}

function calcTotal() {
  const qty  = parseFloat(document.getElementById('qpQty').value) || 0;
  const rate = parseFloat(document.getElementById('qpRate').value) || 0;
  const total = qty * rate;
  document.getElementById('qpTotalDisplay').textContent = 'Tk. ' + total.toLocaleString('en-BD', {maximumFractionDigits:0});
}

async function loadItemSuggestions(term) {
  if (!term || term.length < 2) return;
  try {
    const r = await fetch(BASE_PATH + '/api/purchases.php?action=autocomplete&field=item_name&term=' + encodeURIComponent(term) + '&project_id=0');
    const d = await r.json();
    if (d.success) {
      const dl = document.getElementById('itemSuggestions');
      dl.innerHTML = d.data.map(v => '<option value="' + v + '">').join('');
    }
  } catch(e) {}
}
document.getElementById('qpItem').addEventListener('input', function() { loadItemSuggestions(this.value); });

async function loadSupplierSuggestions(term) {
  if (!term || term.length < 2) return;
  try {
    const r = await fetch(BASE_PATH + '/api/purchases.php?action=autocomplete&field=supplier&term=' + encodeURIComponent(term) + '&project_id=0');
    const d = await r.json();
    if (d.success) {
      document.getElementById('supplierSuggestions').innerHTML = d.data.map(v => '<option value="' + v + '">').join('');
    }
  } catch(e) {}
}
document.getElementById('qpSupplier').addEventListener('input', function() { loadSupplierSuggestions(this.value); });

async function savePurchase(goHome) {
  const pid  = document.getElementById('qpProject').value;
  const item = document.getElementById('qpItem').value.trim();
  const qty  = parseFloat(document.getElementById('qpQty').value) || 0;
  const rate = parseFloat(document.getElementById('qpRate').value) || 0;
  const catVal = document.getElementById('qpCategory').value;
  const isBoard = catVal === 'Board' || catVal === 'Board & Wood' || /board/i.test(catVal);
  const thickVal = document.getElementById('qpThickness') ? document.getElementById('qpThickness').value.trim() : '';

  if (!pid)  { showToast('Please select a project', 'warning'); return; }
  if (!item) { showToast(isBoard ? 'Board type is required' : 'Item name is required', 'warning'); return; }
  if (isBoard && !thickVal) {
    showToast('Board thickness (mm) is required', 'warning');
    document.getElementById('qpThickness').focus();
    return;
  }
  if (qty <= 0 || rate <= 0) { showToast('Quantity and rate must be greater than 0', 'warning'); return; }

  const fd = new FormData();
  fd.append('project_id',     pid);
  fd.append('item_name',      item);
  fd.append('supply_category',catVal);
  fd.append('board_type',     item);
  fd.append('board_thickness',thickVal);
  fd.append('board_size',     '');
  fd.append('quantity',       qty);
  fd.append('unit',           isBoard ? 'pcs' : (document.getElementById('qpUnit').value || 'pcs'));
  fd.append('rate',           rate);
  fd.append('supplier',       document.getElementById('qpSupplier').value);
  fd.append('purchase_date',  document.getElementById('qpDateHidden').value || TODAY);
  try {
    const r = await fetch(BASE_PATH + '/api/purchases.php?action=create', {method:'POST', body:fd});
    const d = await r.json();
    if (d.success) {
      showToast('Purchase saved! Total: Tk. ' + parseFloat(qty*rate).toLocaleString(), 'success');
      clearForm();
      loadTodayPurchases();
    } else { showToast(d.message || 'Error saving', 'error'); }
  } catch(e) { showToast('Connection error', 'error'); }
}

function clearForm() {
  ['qpItem','qpQty','qpRate','qpSupplier','qpThickness'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  document.getElementById('qpCategory').value = '';
  document.getElementById('boardFields').style.display = 'none';
  const unitGroup = document.getElementById('qpUnitGroup');
  if (unitGroup) unitGroup.style.display = 'block';
  const itemLabel = document.getElementById('qpItemLabel');
  if (itemLabel) itemLabel.innerHTML = 'Item Name <span class="required">*</span>';
  const itemInput = document.getElementById('qpItem');
  if (itemInput) {
    itemInput.placeholder = 'e.g. Item Name';
    itemInput.setAttribute('list', 'itemSuggestions');
  }
  const qtyLabel = document.getElementById('qpQtyLabel');
  if (qtyLabel) qtyLabel.innerHTML = 'Quantity <span class="required">*</span>';
  document.getElementById('qpTotalDisplay').textContent = 'Tk. 0';
  document.getElementById('qpItem').focus();
}

async function loadTodayPurchases() {
  const pid = document.getElementById('qpProject').value;
  const list = document.getElementById('todayPurchaseList');
  list.innerHTML = '<div class="loading-state"><div class="spinner"></div></div>';
  try {
    const url = pid
      ? BASE_PATH + '/api/purchases.php?action=list&project_id=' + pid + '&from=' + TODAY + '&to=' + TODAY
      : BASE_PATH + '/api/index.php?action=get_dashboard_stats'; // fallback
    if (!pid) { list.innerHTML = '<div class="empty-state" style="padding:24px;"><p>Select a project to see today\'s entries</p></div>'; return; }
    const r = await fetch(url);
    const d = await r.json();
    if (!d.success || !d.data.length) {
      list.innerHTML = '<div class="empty-state" style="padding:24px;"><p>No entries today</p></div>';
      document.getElementById('todayTotalBadge').textContent = 'Tk. 0';
      return;
    }
    let total = 0;
    list.innerHTML = d.data.map(p => {
      total += parseFloat(p.total || 0);
      return `<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-light);">
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(p.item_name)}</div>
          <div style="font-size:11px;color:var(--text-muted);">${p.quantity} ${p.unit} @ Tk.${p.rate}</div>
        </div>
        <div style="text-align:right;flex-shrink:0;">
          <div style="font-family:'Poppins','Noto Sans Bengali','Hind Siliguri','Nirmala UI','Vrinda','Shonar Bangla',sans-serif;font-weight:700;font-size:13px;color:var(--danger);">Tk.${parseFloat(p.total).toLocaleString('en-BD',{maximumFractionDigits:0})}</div>
          <button onclick="deletePurchase(${p.id})" style="font-size:11px;color:var(--text-muted);cursor:pointer;background:none;border:none;">&#10006;</button>
        </div>
      </div>`;
    }).join('');
    document.getElementById('todayTotalBadge').textContent = 'Tk. ' + total.toLocaleString('en-BD',{maximumFractionDigits:0});
  } catch(e) { list.innerHTML = '<div class="empty-state"><p>Error loading</p></div>'; }
}

async function deletePurchase(id) {
  confirmDelete('Delete this purchase?', async function() {
    const pid = document.getElementById('qpProject').value;
    await fetch(BASE_PATH + '/api/purchases.php?action=delete&project_id=' + pid, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id})});
    showToast('Deleted', 'success');
    loadTodayPurchases();
  });
}

document.getElementById('qpProject').addEventListener('change', loadTodayPurchases);

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

document.addEventListener('DOMContentLoaded', function() {
  SmartDate.initAll();
  SmartDate.setDateValue(document.getElementById('qpDate'), TODAY);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
