<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'Payments';
$activeNav = 'payments';
$projects = $pdo->query("SELECT id,name FROM app_projects WHERE is_deleted=0 ORDER BY name")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="tabs" id="paymentTabs">
  <button class="tab-btn active" onclick="switchTab(this,'contractorTab')">Contractor</button>
  <button class="tab-btn" onclick="switchTab(this,'clientTab')">Client Payments</button>
</div>

<!-- CLIENT PAYMENTS -->
<div id="clientTab" class="tab-content" style="display:none;">
  <div class="filter-bar">
    <select id="cpProject" class="form-select filter-select" onchange="loadClientPayments()">
      <option value="">-- All Projects --</option>
      <?php foreach($projects as $p): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option><?php endforeach; ?>
    </select>
    <button class="btn btn-primary btn-sm" onclick="openModal('addClientPayModal')">+ Add Payment</button>
  </div>
  <div class="card">
    <div class="card-header"><h3>Client Payments</h3><span id="cpTotal" class="badge badge-success">Tk. 0</span></div>
    <div class="table-wrapper">
      <table class="data-table">
        <thead><tr><th>Project</th><th>Amount</th><th>Method</th><th>Date</th><th>Notes</th><th></th></tr></thead>
        <tbody id="cpTable"><tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">Select a project to load payments</td></tr></tbody>
        <tfoot><tr><td colspan="1"><strong>Total</strong></td><td id="cpTableTotal" class="td-amount">Tk. 0</td><td colspan="4"></td></tr></tfoot>
      </table>
    </div>
  </div>
</div>

<!-- CONTRACTOR ADVANCES -->
<div id="contractorTab" class="tab-content active">
  <div class="filter-bar">
    <select id="caProject" class="form-select filter-select" onchange="onCaProjectChange()">
      <option value="">-- Select Project --</option>
      <?php foreach($projects as $p): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option><?php endforeach; ?>
    </select>
    <select id="caContractor" class="form-select filter-select" onchange="loadContractorAdvances()">
      <option value="">-- All Contractors --</option>
    </select>
    <button class="btn btn-primary btn-sm" onclick="openModal('addAdvanceModal')">+ Add Advance</button>
  </div>
  <div class="card">
    <div class="card-header"><h3>Contractor Advances</h3><span id="caTotal" class="badge badge-warning">Tk. 0</span></div>
    <div class="table-wrapper">
      <table class="data-table">
        <thead><tr><th>Contractor</th><th>Amount</th><th>Method</th><th>Who Paid</th><th>Date</th><th></th></tr></thead>
        <tbody id="caTable"><tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">Select a project</td></tr></tbody>
        <tfoot><tr><td><strong>Total</strong></td><td id="caTableTotal" class="td-amount">Tk. 0</td><td colspan="4"></td></tr></tfoot>
      </table>
    </div>
  </div>
</div>

<!-- ADD CLIENT PAYMENT MODAL -->
<div class="modal-overlay" id="addClientPayModal">
  <div class="modal" data-form-nav>
    <div class="modal-header"><h3>&#128176; Add Client Payment</h3><div class="modal-close" onclick="closeModal('addClientPayModal')">&times;</div></div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Project <span class="required">*</span></label>
        <select id="cpModalProject" class="form-select"><option value="">-- Select --</option><?php foreach($projects as $p): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option><?php endforeach; ?></select></div>
      <div class="two-col">
        <div class="form-group"><label class="form-label">Amount (Tk) <span class="required">*</span></label><input type="number" id="cpAmount" class="form-input" placeholder="0"></div>
        <div class="form-group"><label class="form-label">Date</label><input type="text" id="cpDate" class="form-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="cpDateHidden"><input type="hidden" id="cpDateHidden" value="<?=date('Y-m-d')?>"></div>
      </div>
      <div class="form-group"><label class="form-label">Method</label>
        <select id="cpMethod" class="form-select"><option>Cash</option><option>Bank Transfer</option><option>Cheque</option><option>Mobile Banking</option></select></div>
      <div class="form-group"><label class="form-label">Notes</label><input type="text" id="cpNotes" class="form-input"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addClientPayModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="saveClientPayment()">Save</button></div>
  </div>
</div>

<!-- ADD ADVANCE MODAL -->
<div class="modal-overlay" id="addAdvanceModal">
  <div class="modal" data-form-nav>
    <div class="modal-header"><h3>&#128736; Add Contractor Advance</h3><div class="modal-close" onclick="closeModal('addAdvanceModal')">&times;</div></div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Project <span class="required">*</span></label>
        <select id="caModalProject" class="form-select" onchange="loadModalContractors()"><option value="">-- Select --</option><?php foreach($projects as $p): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label class="form-label">Contractor <span class="required">*</span></label><select id="caModalContractor" class="form-select"><option value="">-- Select project first --</option></select></div>
      <div class="two-col">
        <div class="form-group"><label class="form-label">Amount (Tk) <span class="required">*</span></label><input type="number" id="caAmount" class="form-input" placeholder="0"></div>
        <div class="form-group"><label class="form-label">Date</label><input type="text" id="caDate" class="form-input smart-date" placeholder="<?=date('j/n/y')?>" data-date-target="caDateHidden"><input type="hidden" id="caDateHidden" value="<?=date('Y-m-d')?>"></div>
      </div>
      <div class="two-col">
        <div class="form-group"><label class="form-label">Who Paid</label><input type="text" id="caWhoPaid" class="form-input" value="<?=htmlspecialchars($_SESSION['username']??'')?>"></div>
        <div class="form-group"><label class="form-label">Who Received</label><input type="text" id="caWhoReceived" class="form-input"></div>
      </div>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addAdvanceModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="saveAdvance()">Save</button></div>
  </div>
</div>

<script>
var TODAY = '<?=date('Y-m-d')?>';
function switchTab(btn, tabId) {
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.tab-content').forEach(t=>t.style.display='none');
  document.getElementById(tabId).style.display='block';
}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
function fmt(n){return 'Tk. '+parseFloat(n||0).toLocaleString('en-BD',{maximumFractionDigits:0});}
function fmtDate(d){if(!d)return'-';var dt=new Date(d);return isNaN(dt)?d:dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});}

async function loadClientPayments() {
  const pid = document.getElementById('cpProject').value;
  if (!pid) { document.getElementById('cpTable').innerHTML='<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">Select a project</td></tr>'; return; }
  const r = await fetch(BASE_PATH + '/api/client_payments.php?action=list&project_id='+pid);
  const d = await r.json();
  const body = document.getElementById('cpTable');
  const projName = document.getElementById('cpProject').options[document.getElementById('cpProject').selectedIndex].text;
  if (!d.success||!d.data.length) { body.innerHTML='<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">No payments recorded</td></tr>'; document.getElementById('cpTotal').textContent='Tk. 0'; document.getElementById('cpTableTotal').textContent='Tk. 0'; return; }
  document.getElementById('cpTotal').textContent = fmt(d.total);
  document.getElementById('cpTableTotal').textContent = fmt(d.total);
  body.innerHTML = d.data.map(p=>`<tr><td>${esc(projName)}</td><td class="td-amount text-success">${fmt(p.amount)}</td><td>${esc(p.payment_method||'-')}</td><td>${fmtDate(p.payment_date)}</td><td>${esc(p.notes||'-')}</td><td><button class="btn btn-ghost btn-sm btn-icon" onclick="delClientPay(${p.id})" title="Delete">&#10006;</button></td></tr>`).join('');
}
async function saveClientPayment() {
  const pid = document.getElementById('cpModalProject').value;
  const amt = parseFloat(document.getElementById('cpAmount').value)||0;
  if (!pid||amt<=0) { showToast('Project and amount required','warning'); return; }
  const fd=new FormData();
  fd.append('project_id',pid); fd.append('amount',amt);
  fd.append('payment_date',document.getElementById('cpDateHidden').value||TODAY);
  fd.append('payment_method',document.getElementById('cpMethod').value);
  fd.append('notes',document.getElementById('cpNotes').value);
  const r=await fetch(BASE_PATH + '/api/client_payments.php?action=create',{method:'POST',body:fd});
  const d=await r.json();
  if(d.success){showToast('Payment saved!','success');closeModal('addClientPayModal');loadClientPayments();}
  else showToast(d.message||'Error','error');
}
async function delClientPay(id) {
  const pid=document.getElementById('cpProject').value;
  confirmDelete('Delete this payment?',async function(){
    await fetch(BASE_PATH + '/api/client_payments.php?action=delete&project_id='+pid,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
    showToast('Deleted','success');loadClientPayments();
  });
}
async function onCaProjectChange() {
  const pid = document.getElementById('caProject').value;
  const sel = document.getElementById('caContractor');
  sel.innerHTML = '<option value="">-- All Contractors --</option>';
  if (pid) {
    const r = await fetch(BASE_PATH + '/api/billing.php?action=list_project_contractors&project_id=' + pid);
    const d = await r.json();
    if (d.success && d.data) {
      sel.innerHTML = '<option value="">-- All Contractors --</option>' + d.data.map(c => `<option value="${c.contractor_id}">${esc(c.name)}</option>`).join('');
    }
  }
  loadContractorAdvances();
}
async function loadContractorAdvances() {
  const pid=document.getElementById('caProject').value;
  const cid=document.getElementById('caContractor').value;
  if(!pid){document.getElementById('caTable').innerHTML='<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">Select a project</td></tr>';document.getElementById('caTotal').textContent='Tk. 0';document.getElementById('caTableTotal').textContent='Tk. 0';return;}
  const url=BASE_PATH + '/api/billing.php?action=list_advances_range&project_id='+pid+'&from=1900-01-01&to=2099-12-31'+(cid?'&contractor_id='+cid:'');
  const r2=await fetch(url);
  const d2=await r2.json();
  const body=document.getElementById('caTable');
  if(!d2.success||!d2.data.length){body.innerHTML='<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">No advances recorded</td></tr>';document.getElementById('caTotal').textContent='Tk. 0';document.getElementById('caTableTotal').textContent='Tk. 0';return;}
  document.getElementById('caTotal').textContent=fmt(d2.total);
  document.getElementById('caTableTotal').textContent=fmt(d2.total);
  body.innerHTML=d2.data.map(a=>`<tr><td><strong>${esc(a.contractor_name||'-')}</strong></td><td class="td-amount">${fmt(a.amount)}</td><td>${esc(a.payment_method||'-')}</td><td>${esc(a.who_paid||'-')}</td><td>${fmtDate(a.payment_date)}</td><td><button class="btn btn-ghost btn-sm btn-icon" onclick="delAdvance(${a.id})" title="Delete">&#10006;</button></td></tr>`).join('');
}
async function loadModalContractors() {
  const pid=document.getElementById('caModalProject').value;
  if(!pid)return;
  const r=await fetch(BASE_PATH + '/api/billing.php?action=list_project_contractors&project_id='+pid);
  const d=await r.json();
  if(d.success){document.getElementById('caModalContractor').innerHTML='<option value="">-- Select --</option>'+d.data.map(c=>`<option value="${c.contractor_id}">${esc(c.name)}</option>`).join('');}
}
async function saveAdvance() {
  const pid=document.getElementById('caModalProject').value;
  const cid=document.getElementById('caModalContractor').value;
  const amt=parseFloat(document.getElementById('caAmount').value)||0;
  if(!pid||!cid||amt<=0){showToast('Project, contractor and amount required','warning');return;}
  const fd=new FormData();
  fd.append('project_id',pid);fd.append('contractor_id',cid);fd.append('amount',amt);
  fd.append('payment_date',document.getElementById('caDateHidden').value||TODAY);
  fd.append('who_paid',document.getElementById('caWhoPaid').value);
  fd.append('who_received',document.getElementById('caWhoReceived').value);
  const r=await fetch(BASE_PATH + '/api/billing.php?action=add_advance',{method:'POST',body:fd});
  const d=await r.json();
  if(d.success){showToast('Advance saved!','success');closeModal('addAdvanceModal');loadContractorAdvances();}
  else showToast(d.message||'Error','error');
}
async function delAdvance(id) {
  const pid=document.getElementById('caProject').value;
  confirmDelete('Delete this advance?',async function(){
    await fetch(BASE_PATH + '/api/billing.php?action=delete_advance&project_id='+pid,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});
    showToast('Deleted','success');loadContractorAdvances();
  });
}
document.addEventListener('DOMContentLoaded',function(){SmartDate.initAll();SmartDate.setDateValue(document.getElementById('cpDate'),TODAY);SmartDate.setDateValue(document.getElementById('caDate'),TODAY);});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
