<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/trades.php';
requireLogin();
$pageTitle='Workers';$activeNav='workers';
$activeContractors = $pdo->query("SELECT id,name,trade FROM app_contractors WHERE is_active=1 ORDER BY name")->fetchAll();
$allProjects = $pdo->query("SELECT id,name FROM app_projects WHERE is_deleted=0 ORDER BY name")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="filter-bar">
  <div class="search-input-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" class="form-input" id="wSearch" placeholder="Search workers..." oninput="filterWorkers()"></div>
  <select id="wContFilter" class="form-select filter-cat" style="max-width:240px;" onchange="filterWorkers()">
    <option value="">All Contractors / Teams</option>
    <option value="none">-- Independent (No Contractor) --</option>
    <?php foreach($activeContractors as $c): ?>
      <option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?><?= !empty($c['trade'])?' ('.htmlspecialchars($c['trade']).')':'' ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-primary btn-sm" onclick="openModal('addWorkerModal')">+ Add Worker</button>
</div>
<div class="table-wrapper card">
  <table class="data-table">
    <thead><tr><th>Name</th><th>Trade</th><th>Works Under (Contractor)</th><th>Phone</th><th>Daily Rate</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
    <tbody id="workerBody"></tbody>
  </table>
</div>

<!-- ADD -->
<div class="modal-overlay" id="addWorkerModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>+ Add Worker</h3><div class="modal-close" onclick="closeModal('addWorkerModal')">&times;</div></div>
  <div class="modal-body">
    <div class="two-col"><div class="form-group"><label class="form-label">Name <span class="required">*</span></label><input type="text" id="wName" class="form-input"></div><div class="form-group"><label class="form-label">Trade <span class="required">*</span></label><select id="wTrade" class="form-select"><?= tradeOptions() ?></select></div></div>
    <div class="two-col"><div class="form-group"><label class="form-label">Works Under (Contractor)</label><select id="wContractor" class="form-select" onchange="onContractorSelected('wContractor','wTrade')"><option value="">-- None (Independent) --</option><?php foreach($activeContractors as $c): ?><option value="<?=$c['id']?>" data-trade="<?=htmlspecialchars($c['trade'])?>"><?=htmlspecialchars($c['name'])?><?= !empty($c['trade'])?' ('.htmlspecialchars($c['trade']).')':'' ?></option><?php endforeach; ?></select></div><div class="form-group"><label class="form-label">Phone</label><input type="tel" id="wPhone" class="form-input"></div></div>
    <div class="two-col"><div class="form-group"><label class="form-label">Daily Rate (Tk)</label><input type="number" id="wRate" class="form-input" placeholder="0"></div><div class="form-group"><label class="form-label">Address</label><input type="text" id="wAddress" class="form-input"></div></div>
    <div class="form-group"><label class="form-label">NID</label><input type="text" id="wNID" class="form-input"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addWorkerModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="saveWorker()">Save</button></div>
</div></div>

<!-- EDIT -->
<div class="modal-overlay" id="editWorkerModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#9998; Edit Worker</h3><div class="modal-close" onclick="closeModal('editWorkerModal')">&times;</div></div>
  <div class="modal-body">
    <input type="hidden" id="ewId">
    <div class="two-col"><div class="form-group"><label class="form-label">Name</label><input type="text" id="ewName" class="form-input"></div><div class="form-group"><label class="form-label">Trade</label><select id="ewTrade" class="form-select"></select></div></div>
    <div class="two-col"><div class="form-group"><label class="form-label">Works Under (Contractor)</label><select id="ewContractor" class="form-select" onchange="onContractorSelected('ewContractor','ewTrade')"><option value="">-- None (Independent) --</option><?php foreach($activeContractors as $c): ?><option value="<?=$c['id']?>" data-trade="<?=htmlspecialchars($c['trade'])?>"><?=htmlspecialchars($c['name'])?><?= !empty($c['trade'])?' ('.htmlspecialchars($c['trade']).')':'' ?></option><?php endforeach; ?></select></div><div class="form-group"><label class="form-label">Phone</label><input type="tel" id="ewPhone" class="form-input"></div></div>
    <div class="two-col"><div class="form-group"><label class="form-label">Daily Rate (Tk)</label><input type="number" id="ewRate" class="form-input"></div><div class="form-group"><label class="form-label">Status</label><select id="ewStatus" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div></div>
    <div class="two-col"><div class="form-group"><label class="form-label">Address</label><input type="text" id="ewAddress" class="form-input"></div><div class="form-group"><label class="form-label">NID</label><input type="text" id="ewNID" class="form-input"></div></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('editWorkerModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="updateWorker()">Update</button></div>
</div></div>

<!-- WORKER STATEMENT / REPORT MODAL -->
<div class="modal-overlay" id="workerStatementModal"><div class="modal modal-sm" data-form-nav>
  <div class="modal-header"><h3 id="wsModalTitle">&#128196; Worker Statement</h3><div class="modal-close" onclick="closeModal('workerStatementModal')">&times;</div></div>
  <div class="modal-body">
    <input type="hidden" id="wsWorkerId" value="">
    <div class="form-group">
      <label class="form-label">Project</label>
      <select id="wsProject" class="form-select">
        <option value="0">-- All Projects (Consolidated) --</option>
        <?php foreach($allProjects as $p): ?>
          <option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Report Type</label>
      <select id="wsType" class="form-select">
        <option value="statement">Full Statement (Attendance + Payments + Due)</option>
        <option value="attendance">Attendance Records Only</option>
        <option value="payments">Payments Received Only</option>
      </select>
    </div>
    <div class="two-col">
      <div class="form-group">
        <label class="form-label">From Date</label>
        <input type="text" id="wsFrom" class="form-input smart-date" placeholder="Start" data-date-target="wsFromH">
        <input type="hidden" id="wsFromH">
      </div>
      <div class="form-group">
        <label class="form-label">To Date</label>
        <input type="text" id="wsTo" class="form-input smart-date" placeholder="End" data-date-target="wsToH">
        <input type="hidden" id="wsToH">
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <button class="btn btn-secondary" onclick="closeModal('workerStatementModal')">Cancel</button>
    <button class="btn btn-primary" onclick="printWorkerStatement()">&#128438; Print / View Statement</button>
  </div>
</div></div>

<script>
var allWorkers=[];
var TRADE_OPTS = <?= json_encode(array_values($GLOBALS['TRADES'])) ?>;

function onContractorSelected(contSelId, tradeSelId) {
  const sel = document.getElementById(contSelId);
  if (!sel) return;
  const opt = sel.options[sel.selectedIndex];
  const tr = opt ? opt.getAttribute('data-trade') : '';
  if (tr) {
    const tradeSel = document.getElementById(tradeSelId);
    if (tradeSel) tradeSel.value = tr;
  }
}

async function loadWorkers(){
  const r=await fetch(BASE_PATH + '/api/workers.php?action=list');
  const d=await r.json();
  allWorkers=d.data||[];
  filterWorkers();
}

function fillTradeOpts(selId,val){
  const sel=document.getElementById(selId);
  sel.innerHTML='<option value="">-- Select Trade --</option>';
  const opts=TRADE_OPTS.slice();
  if(val&&opts.indexOf(val)===-1)opts.push(val);
  opts.forEach(t=>{
    const o=document.createElement('option');
    o.value=t;
    o.textContent=t;
    if(t===val)o.selected=true;
    sel.appendChild(o);
  });
}

function filterWorkers(){
  const q=(document.getElementById('wSearch').value||'').toLowerCase();
  const contFilter = document.getElementById('wContFilter').value;
  const rows=allWorkers.filter(w=>{
    const mq=!q||w.name.toLowerCase().includes(q)||(w.trade||'').toLowerCase().includes(q)||(w.contractor_name||'').toLowerCase().includes(q);
    let mc = true;
    if (contFilter === 'none') mc = !w.contractor_id || w.contractor_id == 0;
    else if (contFilter) mc = w.contractor_id == contFilter;
    return mq && mc;
  });
  const body=document.getElementById('workerBody');
  if(!rows.length){body.innerHTML='<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-muted);">No workers found</td></tr>';return;}
  body.innerHTML=rows.map(w=>`<tr>
    <td><strong>${esc(w.name)}</strong></td>
    <td><span class="badge badge-info">${esc(w.trade||'-')}</span></td>
    <td>${w.contractor_id?`<span style="color:var(--primary);font-weight:600;">&#128736; ${esc(w.contractor_name||'-')}</span>`:'<span class="badge badge-neutral">Independent</span>'}</td>
    <td>${esc(w.phone||'-')}</td>
    <td style="font-family:'Poppins','Noto Sans Bengali','Hind Siliguri','Nirmala UI','Vrinda','Shonar Bangla',sans-serif;font-weight:700;">Tk.${parseFloat(w.default_daily_rate||0).toLocaleString()}</td>
    <td><span class="badge ${w.is_active?'badge-success':'badge-neutral'}">${w.is_active?'Active':'Inactive'}</span></td>
    <td class="td-actions" style="text-align:right;">
      <button class="btn btn-secondary btn-sm" onclick="openWorkerStatementModal(${w.id}, '${esc(w.name)}')" title="Print Worker Account Statement">&#128196; Statement</button>
      <button class="btn btn-ghost btn-sm" onclick="openEdit(${w.id})" title="Edit">&#9998;</button>
      <button class="btn btn-ghost btn-sm btn-icon" onclick="delWorker(${w.id})" title="Delete">&#10006;</button>
    </td>
  </tr>`).join('');
}

function openWorkerStatementModal(id, name) {
  document.getElementById('wsWorkerId').value = id;
  document.getElementById('wsModalTitle').innerHTML = '&#128196; Statement &mdash; ' + esc(name);
  openModal('workerStatementModal');
}

function printWorkerStatement() {
  const wid = document.getElementById('wsWorkerId').value;
  const pid = document.getElementById('wsProject').value || '0';
  const type = document.getElementById('wsType').value || 'statement';
  const from = document.getElementById('wsFromH').value || '';
  const to = document.getElementById('wsToH').value || '';
  if (!wid) return;
  const url = BASE_PATH + '/print_worker_report?worker_id=' + wid + '&project_id=' + pid + '&type=' + type + (from ? '&from=' + from : '') + (to ? '&to=' + to : '');
  window.open(url, '_blank');
  closeModal('workerStatementModal');
}

async function saveWorker(){
  const name=document.getElementById('wName').value.trim(),trade=document.getElementById('wTrade').value.trim();
  if(!name||!trade){showToast('Name and trade required','warning');return;}
  const fd=new FormData();
  fd.append('name',name);
  fd.append('trade',trade);
  fd.append('phone',document.getElementById('wPhone').value);
  fd.append('default_daily_rate',document.getElementById('wRate').value||0);
  fd.append('address',document.getElementById('wAddress').value);
  fd.append('nid',document.getElementById('wNID').value);
  fd.append('contractor_id',document.getElementById('wContractor').value||0);
  const r=await fetch(BASE_PATH + '/api/workers.php?action=create',{method:'POST',body:fd});
  const d=await r.json();
  if(d.success){showToast('Worker added!','success');closeModal('addWorkerModal');loadWorkers();}else showToast(d.message||'Error','error');
}

function openEdit(id){
  const w=allWorkers.find(x=>x.id==id);
  if(!w)return;
  document.getElementById('ewId').value=w.id;
  document.getElementById('ewName').value=w.name;
  fillTradeOpts('ewTrade',w.trade||'');
  document.getElementById('ewContractor').value=w.contractor_id||'';
  document.getElementById('ewPhone').value=w.phone||'';
  document.getElementById('ewRate').value=w.default_daily_rate||0;
  document.getElementById('ewAddress').value=w.address||'';
  document.getElementById('ewStatus').value=w.is_active;
  openModal('editWorkerModal');
}

async function updateWorker(){
  const fd=new FormData();
  fd.append('id',document.getElementById('ewId').value);
  fd.append('name',document.getElementById('ewName').value);
  fd.append('trade',document.getElementById('ewTrade').value);
  fd.append('phone',document.getElementById('ewPhone').value);
  fd.append('default_daily_rate',document.getElementById('ewRate').value||0);
  fd.append('address',document.getElementById('ewAddress').value);
  fd.append('is_active',document.getElementById('ewStatus').value);
  fd.append('contractor_id',document.getElementById('ewContractor').value||0);
  const r=await fetch(BASE_PATH + '/api/workers.php?action=update',{method:'POST',body:fd});
  const d=await r.json();
  if(d.success){showToast('Updated!','success');closeModal('editWorkerModal');loadWorkers();}else showToast(d.message||'Error','error');
}

async function delWorker(id){
  confirmDelete('Deactivate this worker?',async function(){
    const fd=new FormData();
    fd.append('id',id);
    await fetch(BASE_PATH + '/api/workers.php?action=delete',{method:'POST',body:fd});
    showToast('Done','success');
    loadWorkers();
  });
}

function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
document.addEventListener('DOMContentLoaded',loadWorkers);
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
