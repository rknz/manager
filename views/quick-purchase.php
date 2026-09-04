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

<div class="page-grid">
  <!-- ENTRY FORM -->
  <div class="card">
    <div class="card-header">
      <h3>&#9889; New Purchase Entry</h3>
      <span class="badge badge-info">Enter = Next field | Ctrl+Enter = Save</span>
    </div>
<div class="card-body" data-form-nav>
      <div class="page-grid" style="grid-template-columns: 1fr; gap:16px;">
        <!-- Project & Date -->
        <div class="two-col">
          <div class="form-group">
            <label class="form-label">Project <span class="required">*</span></label>
            <select id="qpProject" class="form-select">
              <option value="">-- Select Project --</option>
              <?php foreach($projects as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Purchase Date</label>
            <input type="text" id="qpDate" class="form-input smart-date" placeholder="e.g. <?= date('j/n/y') ?>" data-date-target="qpDateHidden">
            <input type="hidden" id="qpDateHidden" value="<?= date('Y-m-d') ?>">
          </div>
        </div>

        <!-- Category & Item -->
        <div class="two-col">
          <div class="form-group">
            <label class="form-label">Category</label>
            <select id="qpCategory" class="form-select">
              <option value="">-- Category --</option>
              <?php if(!empty($categories)): foreach($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; else: ?>
              <option>Board</option><option>Paint</option><option>Hardware</option>
              <option>Glass</option><option>Electric</option><option>Labour</option><option>Other</option>
              <?php endif; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Item Name <span class="required">*</span></label>
            <input type="text" id="qpItem" class="form-input" placeholder="e.g. Plex Board" list="itemSuggestions" autocomplete="off">
            <datalist id="itemSuggestions"></datalist>
          </div>
        </div>

        <!-- Conditional Board Fields -->
        <div id="boardFields" class="three-col" style="display:none;">
          <div class="form-group">
            <label class="form-label">Board Type</label>
            <input type="text" id="qpBoardType" class="form-input" placeholder="e.g. Plex" list="boardTypeSuggestions">
            <datalist id="boardTypeSuggestions"><option>Plex</option><option>MDF</option><option>Ply</option><option>Particle</option><option>Melamine</option></datalist>
          </div>
          <div class="form-group">
            <label class="form-label">Thickness (mm)</label>
            <input type="text" id="qpThickness" class="form-input" placeholder="e.g. 18" onblur="if(this.value && !this.value.includes('mm')) this.value += 'mm'">
          </div>
          <div class="form-group">
            <label class="form-label">Board Size</label>
            <input type="text" id="qpBoardSize" class="form-input" placeholder="e.g. 8x4">
          </div>
        </div>

        <!-- Qty & Rate -->
        <div class="three-col">
          <div class="form-group">
            <label class="form-label">Quantity <span class="required">*</span></label>
            <input type="number" id="qpQty" class="form-input" placeholder="0" step="1" min="1" inputmode="numeric" oninput="integerOnly(this); calcTotal()">
          </div>
          <div class="form-group">
            <label class="form-label">Unit</label>
            <input type="text" id="qpUnit" class="form-input" placeholder="pcs" list="unitList">
            <datalist id="unitList"><option>pcs</option><option>sft</option><option>rft</option><option>kg</option><option>ltr</option><option>set</option><option>bag</option></datalist>
          </div>
          <div class="form-group">
            <label class="form-label">Rate (Tk) <span class="required">*</span></label>
            <input type="number" id="qpRate" class="form-input" placeholder="0" step="1" min="1" inputmode="numeric" oninput="integerOnly(this); calcTotal()">
          </div>
        </div>

        <!-- Supplier (No Notes as requested) -->
        <div class="form-group">
          <label class="form-label">Supplier</label>
          <input type="text" id="qpSupplier" class="form-input" placeholder="Supplier name (optional)" list="supplierSuggestions">
          <datalist id="supplierSuggestions"></datalist>
        </div>

        <!-- Total display -->
        <div style="background:var(--primary-light);border-radius:var(--radius-md);padding:16px;display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
          <span style="font-size:14px;font-weight:600;color:var(--primary);">Total Amount</span>
          <span id="qpTotalDisplay" style="font-family:'Poppins','Noto Sans Bengali','Hind Siliguri','Nirmala UI','Vrinda','Shonar Bangla',sans-serif;font-size:24px;font-weight:800;color:var(--primary);">Tk. 0</span>
        </div>

<!-- Add More then Save -->
        <button class="btn btn-outline" style="padding:13px;font-size:16px;font-weight:700;width:100%;" onclick="savePurchase(false)">&#10133; ADD MORE</button>
        <button class="btn btn-primary" style="padding:14px;font-size:16px;font-weight:700;width:100%;" onclick="savePurchase(true)">&#128190; SAVE</button>
      </div>
    </div>
  </div>

  <div class="card" style="height:fit-content;">
    <div class="card-header">
      <h3>&#128203; Today's Entries</h3>
      <span id="todayTotalBadge" class="badge badge-primary">Tk. 0</span>
    </div>
    <div style="max-height:600px;overflow-y:auto;">
      <div id="todayPurchaseList" style="padding:0;">
        <div class="loading-state"><div class="spinner"></div></div>
      </div>
    </div>
  </div>
</div>

<script>
var TODAY = '<?= date('Y-m-d') ?>';

// Category change ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ show/hide board fields
document.getElementById('qpCategory').addEventListener('change', function() {
  document.getElementById('boardFields').style.display = this.value === 'Board' ? 'grid' : 'none';
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
    if (!pid)  { showToast('Please select a project', 'warning'); return; }
    if (!item) { showToast('Item name is required', 'warning'); return; }
    if (qty <= 0 || rate <= 0) { showToast('Quantity and rate must be greater than 0', 'warning'); return; }
    const fd = new FormData();
    fd.append('project_id',     pid);
    fd.append('item_name',      item);
    fd.append('supply_category',document.getElementById('qpCategory').value);
    fd.append('board_type',     document.getElementById('qpBoardType') ? document.getElementById('qpBoardType').value : '');
    fd.append('board_thickness',document.getElementById('qpThickness') ? document.getElementById('qpThickness').value : '');
    fd.append('board_size',     document.getElementById('qpBoardSize') ? document.getElementById('qpBoardSize').value : '');
    fd.append('quantity',       qty);
    fd.append('unit',           document.getElementById('qpUnit').value || 'pcs');
    fd.append('rate',           rate);
    fd.append('supplier',       document.getElementById('qpSupplier').value);
    fd.append('purchase_date',  document.getElementById('qpDateHidden').value || TODAY);
    try {
      const r = await fetch(BASE_PATH + '/api/purchases.php?action=create', {method:'POST', body:fd});
      const d = await r.json();
      if (d.success) {
        showToast('Purchase saved! Total: Tk. ' + parseFloat(qty*rate).toLocaleString(), 'success');
        if (goHome) {
           setTimeout(() => window.location.href = BASE_PATH + '/dashboard', 600);
        } else {
           clearForm();
           loadTodayPurchases();
        }
      } else { showToast(d.message || 'Error saving', 'error'); }
    } catch(e) { showToast('Connection error', 'error'); }
  }

function clearForm() {
  ['qpItem','qpQty','qpRate','qpSupplier','qpBoardType','qpThickness','qpBoardSize'].forEach(id => { document.getElementById(id).value = ''; });
  document.getElementById('qpCategory').value = '';
  document.getElementById('boardFields').style.display = 'none';
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
