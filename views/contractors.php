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
<div class="contractors-grid" id="contGrid"></div>
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
  const q=(document.getElementById('contSearch').value||'').trim().toLowerCase();
  const rows=allConts.filter(c=>{
    const m=filterActive===''?true:c.is_active==filterActive;
    const mq=!q||(c.name||'').toLowerCase().includes(q)||(c.trade||'').toLowerCase().includes(q)||(c.phone||'').toLowerCase().includes(q)||(c.address||'').toLowerCase().includes(q);
    return m&&mq;
  });
  const grid=document.getElementById('contGrid');
  if(!grid) return;

  if(!rows.length){
    grid.innerHTML='<div class="card" style="grid-column:1/-1;text-align:center;padding:48px 20px;color:var(--text-muted);border-radius:14px;border:1px solid var(--border);">No contractors found</div>';
    return;
  }

  grid.innerHTML=rows.map(c=>{
    const initial = esc(c.name || 'C').charAt(0).toUpperCase();
    return `
    <div class="contractor-card">
      <div class="cc-header">
        <div class="cc-avatar">${initial}</div>
        <div class="cc-title-box">
          <div class="cc-name">${esc(c.name)}</div>
          ${c.trade ? `<span class="badge badge-info cc-trade">${esc(c.trade)}</span>` : ''}
        </div>
        <span class="badge ${c.is_active ? 'badge-success' : 'badge-neutral'} cc-status">${c.is_active ? 'Active' : 'Inactive'}</span>
      </div>

      <div class="cc-details">
        <div class="cc-detail-row">
          <span class="cc-label">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg> Phone:
          </span>
          ${c.phone ? `<a href="tel:${esc(c.phone)}" class="cc-val cc-phone">${esc(c.phone)}</a>` : `<span class="cc-val cc-empty">—</span>`}
        </div>

        <div class="cc-detail-row">
          <span class="cc-label">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Address:
          </span>
          <span class="cc-val">${esc(c.address || '—')}</span>
        </div>

        ${c.notes ? `
        <div class="cc-detail-row">
          <span class="cc-label">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg> Notes:
          </span>
          <span class="cc-val">${esc(c.notes)}</span>
        </div>` : ''}
      </div>

      <div class="cc-actions">
        ${c.phone ? `
          <a href="tel:${esc(c.phone)}" class="btn btn-outline btn-sm cc-btn-call">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:4px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg> Call
          </a>` : ''}
        <button class="btn btn-secondary btn-sm cc-btn-edit" onclick="openEdit(${c.id})">&#9998; Edit</button>
        <button class="btn btn-ghost btn-sm cc-btn-delete" onclick="delCont(${c.id})" style="color:var(--danger);" title="Deactivate">&#10006;</button>
      </div>
    </div>
    `;
  }).join('');
}
async function saveCont(){const name=document.getElementById('cName').value.trim(),trade=document.getElementById('cTrade').value.trim();if(!name||!trade){showToast('Name and trade required','warning');return;}
  const fd=new FormData();fd.append('name',name);fd.append('trade',trade);fd.append('phone',document.getElementById('cPhone').value);fd.append('nid',document.getElementById('cNID').value);fd.append('address',document.getElementById('cAddress').value);fd.append('notes',document.getElementById('cNotes').value);
  const r=await fetch(BASE_PATH + '/api/contractors.php?action=create',{method:'POST',body:fd});const d=await r.json();
  if(d.success){showToast('Contractor added!','success');closeModal('addContModal');loadContractors();}else showToast(d.message||'Error','error');}
function openEdit(id){const c=allConts.find(x=>x.id==id);if(!c)return;document.getElementById('ecId').value=c.id;document.getElementById('ecName').value=c.name;fillTradeOpts('ecTrade',c.trade||'');document.getElementById('ecPhone').value=c.phone||'';document.getElementById('ecAddress').value=c.address||'';document.getElementById('ecNotes').value=c.notes||'';document.getElementById('ecStatus').value=c.is_active;openModal('editContModal');}
async function updateCont(){const fd=new FormData();fd.append('id',document.getElementById('ecId').value);fd.append('name',document.getElementById('ecName').value);fd.append('trade',document.getElementById('ecTrade').value);fd.append('phone',document.getElementById('ecPhone').value);fd.append('address',document.getElementById('ecAddress').value);fd.append('notes',document.getElementById('ecNotes').value);fd.append('is_active',document.getElementById('ecStatus').value);
  const r=await fetch(BASE_PATH + '/api/contractors.php?action=update',{method:'POST',body:fd});const d=await r.json();if(d.success){showToast('Updated!','success');closeModal('editContModal');loadContractors();}else showToast(d.message||'Error','error');}
async function delCont(id){confirmDelete('Deactivate this contractor?',async function(adminPass){const fd=new FormData();fd.append('id',id);if(adminPass)fd.append('admin_password',adminPass);const r=await fetch(BASE_PATH + '/api/contractors.php?action=delete',{method:'POST',body:fd});const d=await r.json();if(d.success){showToast('Done','success');loadContractors();}else showToast(d.message||'Error','error');});}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
document.addEventListener('DOMContentLoaded',loadContractors);
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
