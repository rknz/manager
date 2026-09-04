<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$pageTitle='Settings';$activeNav='settings';
$settings_rows=$pdo->query("SELECT setting_key,setting_value FROM app_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$users=$pdo->query("SELECT id,username,role,is_active,created_at FROM app_users ORDER BY id")->fetchAll();
include __DIR__ . '/../includes/header.php';
?>
<div class="tabs">
  <button class="tab-btn active" onclick="switchTab(this,'tabGeneral')">General</button>
  <button class="tab-btn" onclick="switchTab(this,'tabUsers')">Users</button>
  <button class="tab-btn" onclick="switchTab(this,'tabCategories')">Categories</button>
  <button class="tab-btn" onclick="switchTab(this,'tabPassword')">Change Password</button>
</div>

<!-- GENERAL -->
<div id="tabGeneral" class="tab-content active">
  <div class="card" style="max-width:560px;">
    <div class="card-header"><h3>&#127970; Company Settings</h3></div>
    <div class="card-body" data-form-nav>
      <div class="form-group"><label class="form-label">Company Name</label><input type="text" id="sCompany" class="form-input" value="<?=htmlspecialchars($settings_rows['company_name']??'Lily Interiors')?>"></div>
      <div class="form-group"><label class="form-label">Company Phone</label><input type="text" id="sPhone" class="form-input" value="<?=htmlspecialchars($settings_rows['company_phone']??'')?>"></div>
      <div class="form-group"><label class="form-label">Company Address</label><textarea id="sAddress" class="form-textarea" rows="2"><?=htmlspecialchars($settings_rows['company_address']??'')?></textarea></div>
      <div class="form-group"><label class="form-label">Currency Symbol</label><input type="text" id="sCurrency" class="form-input" value="<?=htmlspecialchars($settings_rows['currency_symbol']??'Tk.')?>" style="max-width:100px;"></div>
      <div class="form-group"><label class="form-label">Session Timeout (seconds)</label><input type="number" id="sTimeout" class="form-input" value="<?=htmlspecialchars($settings_rows['session_timeout']??'7200')?>" style="max-width:160px;"></div>
      <button class="btn btn-primary" onclick="saveSettings()">Save Settings</button>
    </div>
  </div>
</div>

<!-- USERS -->
<div id="tabUsers" class="tab-content" style="display:none;">
  <div class="filter-bar"><button class="btn btn-primary btn-sm" onclick="openModal('addUserModal')">+ Add User</button></div>
  <div class="table-wrapper card">
    <table class="data-table">
      <thead><tr><th>Username</th><th>Role</th><th>Status</th><th>Created</th><th></th></tr></thead>
      <tbody>
        <?php foreach($users as $u): ?>
        <tr>
          <td><strong><?=htmlspecialchars($u['username'])?></strong></td>
          <td><span class="badge <?=$u['role']==='admin'?'badge-primary':'badge-neutral'?>"><?=ucfirst($u['role'])?></span></td>
          <td><span class="badge <?=$u['is_active']?'badge-success':'badge-neutral'?>"><?=$u['is_active']?'Active':'Inactive'?></span></td>
          <td><?=date('d M Y',strtotime($u['created_at']))?></td>
          <td><?php if($u['id']!=$_SESSION['user_id']): ?><button class="btn btn-ghost btn-sm" onclick="toggleUser(<?=$u['id']?>,<?=$u['is_active']?>)"><?=$u['is_active']?'Deactivate':'Activate'?></button><?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- CATEGORIES -->
<div id="tabCategories" class="tab-content" style="display:none;">
  <div class="filter-bar"><button class="btn btn-primary btn-sm" onclick="openModal('addCatModal')">+ Add Category</button></div>
  <div class="table-wrapper card">
    <table class="data-table">
      <thead><tr><th>Name</th><th>Type</th><th>Order</th><th></th></tr></thead>
      <tbody id="catBody"></tbody>
    </table>
  </div>
</div>

<!-- PASSWORD -->
<div id="tabPassword" class="tab-content" style="display:none;">
  <div class="card" style="max-width:440px;">
    <div class="card-header"><h3>&#128274; Change Password</h3></div>
    <div class="card-body" data-form-nav>
      <div class="form-group"><label class="form-label">Current Password</label><input type="password" id="pwCurrent" class="form-input"></div>
      <div class="form-group"><label class="form-label">New Password</label><input type="password" id="pwNew" class="form-input"></div>
      <div class="form-group"><label class="form-label">Confirm New Password</label><input type="password" id="pwConfirm" class="form-input"></div>
      <button class="btn btn-primary" onclick="changePassword()">Update Password</button>
    </div>
  </div>
</div>

<!-- ADD USER MODAL -->
<div class="modal-overlay" id="addUserModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>+ Add User</h3><div class="modal-close" onclick="closeModal('addUserModal')">&times;</div></div>
  <div class="modal-body">
    <div class="form-group"><label class="form-label">Username <span class="required">*</span></label><input type="text" id="nuName" class="form-input"></div>
    <div class="form-group"><label class="form-label">Password <span class="required">*</span></label><input type="password" id="nuPass" class="form-input"></div>
    <div class="form-group"><label class="form-label">Role</label><select id="nuRole" class="form-select"><option value="user">User</option><option value="admin">Admin</option></select></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="addUser()">Create User</button></div>
</div></div>

<!-- ADD CATEGORY MODAL -->
<div class="modal-overlay" id="addCatModal"><div class="modal" data-form-nav>
  <div class="modal-header"><h3>+ Add Category</h3><div class="modal-close" onclick="closeModal('addCatModal')">&times;</div></div>
  <div class="modal-body">
    <div class="form-group"><label class="form-label">Name <span class="required">*</span></label><input type="text" id="catName" class="form-input" placeholder="e.g. Board"></div>
    <div class="form-group"><label class="form-label">Type</label><select id="catType" class="form-select"><option value="purchase">Purchase</option><option value="work">Work</option></select></div>
    <div class="form-group"><label class="form-label">Sort Order</label><input type="number" id="catOrder" class="form-input" value="0" style="max-width:100px;"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="closeModal('addCatModal')">Cancel</button><button class="btn btn-primary" data-save-btn onclick="addCategory()">Add</button></div>
</div></div>

<script>
function switchTab(btn,id){document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));btn.classList.add('active');document.querySelectorAll('.tab-content').forEach(t=>t.style.display='none');document.getElementById(id).style.display='block';if(id==='tabCategories')loadCategories();}
async function saveSettings(){
  const fd=new FormData();
  [['company_name','sCompany'],['company_phone','sPhone'],['company_address','sAddress'],['currency_symbol','sCurrency'],['session_timeout','sTimeout']].forEach(([k,id])=>fd.append(k,document.getElementById(id).value));
  const r=await fetch(BASE_PATH + '/api/index.php?action=save_settings',{method:'POST',body:fd});const d=await r.json();
  if(d.success)showToast('Settings saved!','success');else showToast(d.message||'Error','error');
}
async function addUser(){
  const name=document.getElementById('nuName').value.trim(),pass=document.getElementById('nuPass').value;
  if(!name||!pass){showToast('Username and password required','warning');return;}
  const fd=new FormData();fd.append('username',name);fd.append('password',pass);fd.append('role',document.getElementById('nuRole').value);
  const r=await fetch(BASE_PATH + '/api/index.php?action=create_user',{method:'POST',body:fd});const d=await r.json();
  if(d.success){showToast('User created!','success');closeModal('addUserModal');location.reload();}else showToast(d.message||'Error','error');
}
async function toggleUser(id,isActive){
  const fd=new FormData();fd.append('id',id);fd.append('is_active',isActive?0:1);
  const r=await fetch(BASE_PATH + '/api/index.php?action=toggle_user',{method:'POST',body:fd});const d=await r.json();
  if(d.success){showToast('Updated!','success');location.reload();}else showToast(d.message||'Error','error');
}
async function changePassword(){
  const cur=document.getElementById('pwCurrent').value,nw=document.getElementById('pwNew').value,conf=document.getElementById('pwConfirm').value;
  if(!cur||!nw||!conf){showToast('All fields required','warning');return;}
  if(nw!==conf){showToast('Passwords do not match','warning');return;}
  if(nw.length<6){showToast('Min 6 characters required','warning');return;}
  const fd=new FormData();fd.append('current_password',cur);fd.append('new_password',nw);
  const r=await fetch(BASE_PATH + '/api/index.php?action=change_password',{method:'POST',body:fd});const d=await r.json();
  if(d.success){showToast('Password changed!','success');document.getElementById('pwCurrent').value='';document.getElementById('pwNew').value='';document.getElementById('pwConfirm').value='';}else showToast(d.message||'Error','error');
}
async function loadCategories(){
  const r=await fetch(BASE_PATH + '/api/purchases.php?action=list_categories');const d=await r.json();
  const body=document.getElementById('catBody');if(!d.success){return;}
  body.innerHTML=(d.data||[]).map(c=>`<tr><td><strong>${esc(c.name)}</strong></td><td><span class="badge badge-info">${esc(c.type||'purchase')}</span></td><td>${c.sort_order||0}</td><td><button class="btn btn-ghost btn-sm btn-icon" onclick="delCat(${c.id})">&#10006;</button></td></tr>`).join('');
}
async function addCategory(){
  const name=document.getElementById('catName').value.trim();if(!name){showToast('Name required','warning');return;}
  const fd=new FormData();fd.append('name',name);fd.append('type',document.getElementById('catType').value);fd.append('sort_order',document.getElementById('catOrder').value||0);
  const r=await fetch(BASE_PATH + '/api/index.php?action=create_category',{method:'POST',body:fd});const d=await r.json();
  if(d.success){showToast('Category added!','success');closeModal('addCatModal');loadCategories();}else showToast(d.message||'Error','error');
}
async function delCat(id){confirmDelete('Delete this category?',async function(){await fetch(BASE_PATH + '/api/index.php?action=delete_category',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id})});showToast('Deleted','success');loadCategories();});}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
