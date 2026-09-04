<?php
// views/projects.php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle = 'Projects';
$activeNav = 'projects';
include __DIR__ . '/../includes/header.php';
?>

<!-- Action and Filter bar -->
<div class="projects-action-bar">
  <div class="filter-bar" style="margin-bottom:12px;">
    <div class="search-input-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" class="form-input" id="projectSearch" placeholder="Search projects or clients..." oninput="filterProjects()">
    </div>
    <div class="filter-tabs">
      <button class="filter-tab active" data-status="all" onclick="setStatus(this,'all')">All</button>
      <button class="filter-tab" data-status="Ongoing" onclick="setStatus(this,'Ongoing')">&#9654; Ongoing</button>
      <button class="filter-tab" data-status="Completed" onclick="setStatus(this,'Completed')">&#10003; Completed</button>
      <button class="filter-tab" data-status="On Hold" onclick="setStatus(this,'On Hold')">&#9208; On Hold</button>
    </div>
  </div>
  
  <div class="projects-btn-row">
    <button class="btn btn-primary" onclick="openModal('addProjectModal')">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      + New Project
    </button>
    <button class="btn btn-outline" style="color:var(--danger); border-color:var(--danger);" onclick="openDeleteModal()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
      Delete Project
    </button>
  </div>
</div>

<!-- Projects Grid -->
<div class="projects-grid" id="projectsGrid">
  <?php for($i=0;$i<6;$i++) echo '<div class="project-card"><div class="skeleton" style="height:160px;"></div><div style="padding:16px;"><div class="skeleton" style="height:14px;margin-bottom:8px;"></div><div class="skeleton" style="height:10px;width:70%;margin-bottom:12px;"></div><div class="skeleton" style="height:10px;"></div></div></div>'; ?>
</div>
<div id="projectsEmpty" class="empty-state" style="display:none;">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
  <h3>No projects found</h3>
  <p>Try a different filter or create a new project</p>
  <button class="btn btn-primary mt-4" onclick="openModal('addProjectModal')">Create Project</button>
</div>

<!-- ADD PROJECT MODAL -->
<div class="modal-overlay" id="addProjectModal">
  <div class="modal modal-lg" data-form-nav>
    <div class="modal-header">
      <h3>&#128193; New Project</h3>
      <div class="modal-close" onclick="closeModal('addProjectModal')">&times;</div>
    </div>
    <div class="modal-body">
      <div class="two-col">
        <div class="form-group">
          <label class="form-label">Project Name <span class="required">*</span></label>
          <input type="text" id="addProjName" class="form-input" placeholder="e.g. Gulshan 2 Apartment">
        </div>
        <div class="form-group">
          <label class="form-label">Project Type</label>
          <select id="addProjType" class="form-select">
            <option>Residential</option><option>Commercial</option><option>Office</option><option>Renovation</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Project Address</label>
        <input type="text" id="addProjAddress" class="form-input" placeholder="Full address">
      </div>
      <div class="two-col">
        <div class="form-group">
          <label class="form-label">Client Name <span class="required">*</span></label>
          <input type="text" id="addProjClient" class="form-input" placeholder="Client full name">
        </div>
        <div class="form-group">
          <label class="form-label">Client Phone</label>
          <input type="tel" id="addProjPhone" class="form-input" placeholder="01XXXXXXXXX">
        </div>
      </div>
      <div class="two-col">
        <div class="form-group">
          <label class="form-label">Client Email</label>
          <input type="email" id="addProjEmail" class="form-input" placeholder="Optional">
        </div>
        <div class="form-group">
          <label class="form-label">Estimated Budget (Tk)</label>
          <input type="number" id="addProjBudget" class="form-input" placeholder="0">
        </div>
      </div>
      <div class="two-col">
        <div class="form-group">
          <label class="form-label">Start Date</label>
          <input type="text" id="addProjStart" class="form-input smart-date" placeholder="e.g. 1/1/26">
          <input type="hidden" id="addProjStartHidden">
        </div>
        <div class="form-group">
          <label class="form-label">Expected End Date</label>
          <input type="text" id="addProjEnd" class="form-input smart-date" placeholder="e.g. 30/6/26">
          <input type="hidden" id="addProjEndHidden">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea id="addProjNotes" class="form-textarea" placeholder="Optional notes about this project"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('addProjectModal')">Cancel</button>
      <button class="btn btn-primary" data-save-btn onclick="saveProject()">Create Project</button>
    </div>
  </div>
</div>

<!-- EDIT PROJECT MODAL -->
<div class="modal-overlay" id="editProjectModal">
  <div class="modal modal-lg" data-form-nav>
    <div class="modal-header">
      <h3>&#9998; Edit Project</h3>
      <div class="modal-close" onclick="closeModal('editProjectModal')">&times;</div>
    </div>
    <div class="modal-body">
      <input type="hidden" id="editProjId">
      <div class="two-col">
        <div class="form-group">
          <label class="form-label">Project Name <span class="required">*</span></label>
          <input type="text" id="editProjName" class="form-input">
        </div>
        <div class="form-group">
          <label class="form-label">Project Type</label>
          <select id="editProjType" class="form-select">
            <option>Residential</option><option>Commercial</option><option>Office</option><option>Renovation</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Address</label>
        <input type="text" id="editProjAddress" class="form-input">
      </div>
      <div class="two-col">
        <div class="form-group">
          <label class="form-label">Client Name <span class="required">*</span></label>
          <input type="text" id="editProjClient" class="form-input">
        </div>
        <div class="form-group">
          <label class="form-label">Client Phone</label>
          <input type="tel" id="editProjPhone" class="form-input">
        </div>
      </div>
      <div class="two-col">
        <div class="form-group">
          <label class="form-label">Status</label>
          <select id="editProjStatus" class="form-select">
            <option>Ongoing</option><option>Completed</option><option>On Hold</option><option>Cancelled</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Estimated Budget (Tk)</label>
          <input type="number" id="editProjBudget" class="form-input">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea id="editProjNotes" class="form-textarea"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('editProjectModal')">Cancel</button>
      <button class="btn btn-primary" data-save-btn onclick="updateProject()">Save Changes</button>
    </div>
  </div>
</div>

<!-- DELETE PROJECT MODAL -->
<div class="modal-overlay" id="deleteProjectModal">
  <div class="modal" style="max-width:400px;">
    <div class="modal-header">
      <h3 style="color:var(--danger);">&#9888; Delete Project</h3>
      <div class="modal-close" onclick="closeModal('deleteProjectModal')">&times;</div>
    </div>
    <div class="modal-body">
      <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px;">Select a project to permanently delete. This action cannot be undone.</p>
      <div class="form-group">
        <label class="form-label">Select Project <span class="required">*</span></label>
        <select id="deleteProjSelect" class="form-select">
          <option value="">-- Choose a project --</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('deleteProjectModal')">Cancel</button>
      <button class="btn btn-primary" style="background:var(--danger);border-color:var(--danger);" onclick="confirmDeleteProject()">Delete</button>
    </div>
  </div>
</div>

<script>
var allProjects = [];
var currentStatus = 'all';

async function loadProjects() {
  try {
    const r = await fetch(BASE_PATH + '/api/projects.php?action=list', {cache: 'no-store'});
    const d = await r.json();
    if (d.success) { allProjects = d.data; renderProjects(); }
  } catch(e) {}
}

function setStatus(btn, status) {
  document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  currentStatus = status;
  renderProjects();
}

function filterProjects() { renderProjects(); }

function renderProjects() {
  const q = (document.getElementById('projectSearch').value || '').toLowerCase();
  let projects = allProjects.filter(p => {
    const matchStatus = currentStatus === 'all' || p.status === currentStatus;
    const matchQ = !q || p.name.toLowerCase().includes(q) || (p.client_name||'').toLowerCase().includes(q);
    return matchStatus && matchQ;
  });
  const grid  = document.getElementById('projectsGrid');
  const empty = document.getElementById('projectsEmpty');
  if (!projects.length) { grid.innerHTML = ''; empty.style.display = 'flex'; return; }
  empty.style.display = 'none';
  const sm = {Ongoing:'ongoing',Completed:'completed','On Hold':'on-hold',Cancelled:'cancelled'};
  grid.innerHTML = projects.map((p,i) => {
    const pct = p.progress || 0;
    const barColor = pct >= 80 ? 'green' : pct >= 40 ? 'blue' : 'orange';
    const img = p.project_image
      ? `<img src="${BASE_PATH}/${p.project_image}" class="project-card-img" alt="${esc(p.name)}" onerror="this.outerHTML='<div class=\\'project-card-img-placeholder\\'>&#127968;</div>'">`
      : `<div class="project-card-img-placeholder" style="font-size:48px;">&#127968;</div>`;
    return `
    <a href="${BASE_PATH}/project-detail?id=${p.id}" class="project-card animate-fade-in stagger-${Math.min(i%5+1,5)}" style="text-decoration:none;">
      <div style="position:relative;">
        ${img}
        <span class="project-status-badge badge-${sm[p.status]||'neutral'}">${p.status}</span>
      </div>
      <div class="project-card-body">
        <div class="project-card-name">${esc(p.name)}</div>
        <div class="project-card-location">&#128100; ${esc(p.client_name||'')}</div>
        <div class="progress-bar-bg"><div class="progress-bar-fill ${barColor}" style="width:${pct}%"></div></div>
        <div class="flex items-center justify-between" style="margin-top:4px;">
          <span class="progress-pct">${pct}% Complete</span>
        </div>
      </div>
    </a>`;
  }).join('');
}

async function saveProject() {
  const name   = document.getElementById('addProjName').value.trim();
  const client = document.getElementById('addProjClient').value.trim();
  if (!name || !client) { showToast('Project name and client name are required', 'warning'); return; }
  const fd = new FormData();
  fd.append('name',             name);
  fd.append('client_name',      client);
  fd.append('client_phone',     document.getElementById('addProjPhone').value);
  fd.append('client_email',     document.getElementById('addProjEmail').value);
  fd.append('address',          document.getElementById('addProjAddress').value);
  fd.append('project_type',     document.getElementById('addProjType').value);
  fd.append('estimated_budget', document.getElementById('addProjBudget').value || 0);
  fd.append('start_date',       document.getElementById('addProjStartHidden').value || '<?= date('Y-m-d') ?>');
  fd.append('end_date',         document.getElementById('addProjEndHidden').value || '');
  fd.append('notes',            document.getElementById('addProjNotes').value);
  try {
    const r = await fetch(BASE_PATH + '/api/projects.php?action=create', {method:'POST', body:fd});
    const d = await r.json();
    if (d.success) {
      showToast('Project created!', 'success');
      closeModal('addProjectModal');
      loadProjects();
    } else { showToast(d.message || 'Error', 'error'); }
  } catch(e) { showToast('Connection error', 'error'); }
}

function openEdit(id) {
  const p = allProjects.find(x => x.id == id);
  if (!p) return;
  document.getElementById('editProjId').value     = p.id;
  document.getElementById('editProjName').value   = p.name;
  document.getElementById('editProjClient').value = p.client_name;
  document.getElementById('editProjPhone').value  = p.client_phone || '';
  document.getElementById('editProjAddress').value= p.address || '';
  document.getElementById('editProjBudget').value = p.estimated_budget || '';
  document.getElementById('editProjNotes').value  = p.notes || '';
  document.getElementById('editProjType').value   = p.project_type || 'Residential';
  document.getElementById('editProjStatus').value = p.status || 'Ongoing';
  openModal('editProjectModal');
}

async function updateProject() {
  const id   = document.getElementById('editProjId').value;
  const name = document.getElementById('editProjName').value.trim();
  if (!name) { showToast('Project name required', 'warning'); return; }
  const fd = new FormData();
  fd.append('id',               id);
  fd.append('name',             name);
  fd.append('client_name',      document.getElementById('editProjClient').value);
  fd.append('client_phone',     document.getElementById('editProjPhone').value);
  fd.append('address',          document.getElementById('editProjAddress').value);
  fd.append('project_type',     document.getElementById('editProjType').value);
  fd.append('status',           document.getElementById('editProjStatus').value);
  fd.append('estimated_budget', document.getElementById('editProjBudget').value || 0);
  fd.append('notes',            document.getElementById('editProjNotes').value);
  try {
    const r = await fetch(BASE_PATH + '/api/projects.php?action=update', {method:'POST', body:fd});
    const d = await r.json();
    if (d.success) { showToast('Project updated!', 'success'); closeModal('editProjectModal'); loadProjects(); }
    else { showToast(d.message || 'Error', 'error'); }
  } catch(e) { showToast('Connection error', 'error'); }
}

function fmt(n)     { return 'Tk. '+parseFloat(n||0).toLocaleString('en-BD',{maximumFractionDigits:0}); }
function esc(s)     { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function fmtDate(d) { if(!d) return ''; var dt=new Date(d); return isNaN(dt)?d:dt.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}); }

// Auto-open add modal if #new in URL
if (window.location.hash === '#new') { openModal('addProjectModal'); }

function openDeleteModal() {
  const sel = document.getElementById('deleteProjSelect');
  sel.innerHTML = '<option value="">-- Choose a project --</option>' + 
    allProjects.map(p => `<option value="${p.id}">${esc(p.name)} (${esc(p.client_name||'')})</option>`).join('');
  openModal('deleteProjectModal');
}

function confirmDeleteProject() {
  const projId = document.getElementById('deleteProjSelect').value;
  if (!projId) { showToast('Please select a project to delete', 'warning'); return; }
  
  PasswordConfirm.require('Delete selected project?', async function() {
    try {
      const fd = new FormData();
      fd.append('id', projId);
      const r = await fetch(BASE_PATH + '/api/projects.php?action=delete', {method:'POST', body:fd});
      const d = await r.json();
      if (d.success) {
        showToast('Project deleted successfully', 'success');
        closeModal('deleteProjectModal');
        loadProjects();
      } else { showToast(d.message || 'Error deleting project', 'error'); }
    } catch(e) { showToast('Connection error', 'error'); }
  });
}

function initProjectsPage() {
  if (typeof SmartDate !== 'undefined' && SmartDate.initAll) {
    try { SmartDate.initAll(); } catch(e){}
  }
  loadProjects();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initProjectsPage);
} else {
  initProjectsPage();
}
window.addEventListener('load', initProjectsPage);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
