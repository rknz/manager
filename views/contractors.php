<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/trades.php';
requireLogin();
$pageTitle='Contractors';$activeNav='contractors';
include __DIR__ . '/../includes/header.php';
?>
<div class="filter-bar">
  <div class="search-input-wrap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><input type="text" class="form-input" id="contSearch" placeholder="Search contractors..." oninput="filterContractors()"></div>
  <div class="filter-tabs">
    <button class="filter-tab active" onclick="setFilter(this,1)">Active</button>
    <button class="filter-tab" onclick="setFilter(this,0)">Inactive</button>
    <button class="filter-tab" onclick="setFilter(this,'')">All</button>
  </div>
  <button class="btn btn-primary btn-sm" onclick="openModal('addContModal')">+ Add Contractor</button>
</div>
<div class="table-wrapper card">
  <table class="data-table" id="contTable">
    <thead><tr><th>Name</th><th>Trade</th><th>Phone</th><th>Address</th><th>Status</th><th></th></tr></thead>
    <tbody id="contBody"></tbody>
  </table>
</div>
<div class="modal-overlay" id="addContModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>+ Add Contractor</h3><div class="modal-close" onclick="closeModal('addContModal')">&times;</div></div>
  <div class="modal-body">
    <div class="two-col"><div class="form-group"><label class="form-label">Name <span class="required">*</span></label><input type="text" id="cName" class="form-input"></div><div class="form-group"><label class="form-label">Trade <span class="required">*</span></label><select id="cTrade" class="form-select"><?= tradeOptions() ?></select></div></div>
    <div class="two-col"><div class="form-group"><label class="form-label">Phone</label><input type="tel" id="cPhone" class="form-input"></div><div class="form-group"><label class="form-label">NID</label><input type="text" id="cNID" class="form-input"></div></div>
    <div class="form-group"><label class="form-label">Address</label><input type="text" id="cAddress" class="form-input"></div>
    <div class="form-group"><label class="form-label">Notes</label><textarea id="cNotes" class="form-textarea" rows="2"></textarea></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addContModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="saveCont()">Save</button></div>
</div></div>
<div class="modal-overlay" id="editContModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>&#9998; Edit Contractor</h3><div class="modal-close" onclick="closeModal('editContModal')">&times;</div></div>
  <div class="modal-body">
    <input type="hidden" id="ecId">
    <div class="two-col"><div class="form-group"><label class="form-label">Name</label><input type="text" id="ecName" class="form-input"></div><div class="form-group"><label class="form-label">Trade</label><select id="ecTrade" class="form-select"></select></div></div>
    <div class="two-col"><div class="form-group"><label class="form-label">Phone</label><input type="tel" id="ecPhone" class="form-input"></div><div class="form-group"><label class="form-label">Status</label><select id="ecStatus" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div></div>
    <div class="form-group"><label class="form-label">Address</label><input type="text" id="ecAddress" class="form-input"></div>
    <div class="form-group"><label class="form-label">Notes</label><textarea id="ecNotes" class="form-textarea" rows="2"></textarea></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('editContModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="updateCont()">Update</button></div>
</div></div>
<script>
var allConts=[],filterActive=1;
var TRADE_OPTS = <?= json_encode(array_values($GLOBALS['TRADES'])) ?>;
function fillTradeOpts(selId,val){const sel=document.getElementById(selId);sel.innerHTML='<option value="">-- Select Trade --</option>';const opts=TRADE_OPTS.slice();if(val&&opts.indexOf(val)===-1)opts.push(val);opts.forEach(t=>{const o=document.createElement('option');o.value=t;o.textContent=t;if(t===val)o.selected=true;sel.appendChild(o);});}
async function loadContractors(){const r=await fetch(BASE_PATH + '/api/contractors.php?action=list');const d=await r.json();allConts=d.data||[];filterContractors();}
function setFilter(btn,val){document.querySelectorAll('.filter-tab').forEach(b=>b.classList.remove('active'));btn.classList.add('active');filterActive=val;filterContractors();}
function filterContractors(){
  const q=(document.getElementById('contSearch').value||'').toLowerCase();
  const rows=allConts.filter(c=>{const m=filterActive===''?true:c.is_active==filterActive;const mq=!q||c.name.toLowerCase().includes(q)||(c.trade||'').toLowerCase().includes(q);return m&&mq;});
  const body=document.getElementById('contBody');
  if(!rows.length){body.innerHTML='<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">No contractors found</td></tr>';return;}
  body.innerHTML=rows.map(c=>`<tr><td><strong>${esc(c.name)}</strong></td><td>${esc(c.trade||'-')}</td><td>${esc(c.phone||'-')}</td><td>${esc(c.address||'-')}</td><td><span class="badge ${c.is_active?'badge-success':'badge-neutral'}">${c.is_active?'Active':'Inactive'}</span></td><td class="td-actions"><button class="btn btn-ghost btn-sm" onclick="openEdit(${c.id})">&#9998;</button><button class="btn btn-ghost btn-sm btn-icon" onclick="delCont(${c.id})">&#10006;</button></td></tr>`).join('');
}
async function saveCont(){const name=document.getElementById('cName').value.trim(),trade=document.getElementById('cTrade').value.trim();if(!name||!trade){showToast('Name and trade required','warning');return;}
  const fd=new FormData();fd.append('name',name);fd.append('trade',trade);fd.append('phone',document.getElementById('cPhone').value);fd.append('nid',document.getElementById('cNID').value);fd.append('address',document.getElementById('cAddress').value);fd.append('notes',document.getElementById('cNotes').value);
  const r=await fetch(BASE_PATH + '/api/contractors.php?action=create',{method:'POST',body:fd});const d=await r.json();
  if(d.success){showToast('Contractor added!','success');closeModal('addContModal');loadContractors();}else showToast(d.message||'Error','error');}
function openEdit(id){const c=allConts.find(x=>x.id==id);if(!c)return;document.getElementById('ecId').value=c.id;document.getElementById('ecName').value=c.name;fillTradeOpts('ecTrade',c.trade||'');document.getElementById('ecPhone').value=c.phone||'';document.getElementById('ecAddress').value=c.address||'';document.getElementById('ecNotes').value=c.notes||'';document.getElementById('ecStatus').value=c.is_active;openModal('editContModal');}
async function updateCont(){const fd=new FormData();fd.append('id',document.getElementById('ecId').value);fd.append('name',document.getElementById('ecName').value);fd.append('trade',document.getElementById('ecTrade').value);fd.append('phone',document.getElementById('ecPhone').value);fd.append('address',document.getElementById('ecAddress').value);fd.append('notes',document.getElementById('ecNotes').value);fd.append('is_active',document.getElementById('ecStatus').value);
  const r=await fetch(BASE_PATH + '/api/contractors.php?action=update',{method:'POST',body:fd});const d=await r.json();if(d.success){showToast('Updated!','success');closeModal('editContModal');loadContractors();}else showToast(d.message||'Error','error');}
async function delCont(id){confirmDelete('Deactivate this contractor?',async function(){const fd=new FormData();fd.append('id',id);await fetch(BASE_PATH + '/api/contractors.php?action=delete',{method:'POST',body:fd});showToast('Done','success');loadContractors();});}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
document.addEventListener('DOMContentLoaded',loadContractors);
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
